<?php

namespace Tests\Feature\Registrar;

use App\Actions\Clients\CreateClientProfile;
use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Enums\Role;
use App\Enums\VerificationState;
use App\Models\AuditEvent;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\ClientProfileRevision;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use LogicException;
use Tests\TestCase;

class ClientRecordsTest extends TestCase
{
    use RefreshDatabase;

    private function processor(): User
    {
        return User::factory()->withRole(Role::RegistrarProcessor)->create();
    }

    private function createProfile(User $actor, string $name = 'Juan Dela Cruz (Demo)', string $id = 'DEMO-S-0001', IdentifierType $type = IdentifierType::Student): ClientProfile
    {
        return app(CreateClientProfile::class)->handle($actor, $name, ClientCategory::Student, 'Test source', $type, $id, 'Test creation');
    }

    /**
     * @return array<string, mixed>
     */
    private function newClientPayload(array $overrides = []): array
    {
        return [
            'display_name' => 'Maria Santos (Demo)',
            'category' => 'student',
            'source_reference' => 'Registrar counter, walk-in',
            'identifier_type' => 'student',
            'identifier_value' => 'DEMO-S-0100',
            'reason' => 'New client at the counter',
            ...$overrides,
        ];
    }

    public function test_a_processor_can_create_a_client_with_a_first_id(): void
    {
        $processor = $this->processor();

        $response = $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload());

        $profile = ClientProfile::query()->sole();
        $response->assertRedirect(route('registrar.clients.show', $profile));

        $this->assertSame('Maria Santos (Demo)', $profile->display_name);
        $this->assertSame(ClientCategory::Student, $profile->category);
        $this->assertSame(VerificationState::Unverified, $profile->verification_state);
        $this->assertSame(1, $profile->version);
        $this->assertSame($processor->id, $profile->created_by_id);

        $identifier = $profile->identifiers()->sole();
        $this->assertSame('DEMO-S-0100', $identifier->display_value);
        $this->assertSame(1, $identifier->normalization_version);

        $revision = ClientProfileRevision::query()->sole();
        $this->assertSame(1, $revision->revision_number);
        $this->assertSame('New client at the counter', $revision->reason);
        $this->assertSame('Maria Santos (Demo)', $revision->changes['display_name']['new']);
    }

    public function test_identifiers_keep_leading_zeroes_and_compare_case_insensitively(): void
    {
        $processor = $this->processor();

        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['identifier_value' => '00123456']));

        $stored = ClientIdentifier::query()->sole();
        $this->assertSame('00123456', $stored->display_value);
        $this->assertSame('00123456', $stored->canonical_value);

        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload([
            'display_name' => 'Someone Else (Demo)',
            'identifier_value' => 'demo-x-77',
        ]));
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload([
            'display_name' => 'Third Person (Demo)',
            'identifier_value' => 'DEMO-X-77',
        ]))->assertSessionHasErrors('identifier_value');

        $this->assertSame(2, ClientProfile::query()->count());
        $this->assertSame('DEMO-X-77', ClientIdentifier::query()->where('display_value', 'demo-x-77')->sole()->canonical_value);
    }

    public function test_an_id_already_recorded_is_refused_but_a_shared_name_is_not(): void
    {
        $processor = $this->processor();
        $this->createProfile($processor, 'Alex Reyes (Demo)', 'DEMO-S-0201');

        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload([
            'display_name' => 'Alex Reyes (Demo)',
            'identifier_value' => 'DEMO-S-0201',
        ]))->assertSessionHasErrors('identifier_value');

        // The same name with a different ID is a different person: never merged.
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload([
            'display_name' => 'Alex Reyes (Demo)',
            'identifier_value' => 'DEMO-S-0202',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, ClientProfile::query()->where('display_name', 'Alex Reyes (Demo)')->count());
    }

    public function test_the_database_itself_rejects_a_duplicate_id(): void
    {
        ClientProfile::factory()->withIdentifier(IdentifierType::Student, 'DEMO-S-0300')->create();

        $this->expectException(UniqueConstraintViolationException::class);

        ClientIdentifier::factory()->create([
            'client_profile_id' => ClientProfile::factory()->create()->id,
            'type' => IdentifierType::Student,
            'display_value' => 'DEMO-S-0300',
            'canonical_value' => 'DEMO-S-0300',
        ]);
    }

    public function test_the_same_text_may_be_a_student_id_and_an_alumni_id(): void
    {
        $processor = $this->processor();

        $this->createProfile($processor, 'First (Demo)', 'DEMO-0500', IdentifierType::Student);
        $this->createProfile($processor, 'Second (Demo)', 'DEMO-0500', IdentifierType::Alumni);

        $this->assertSame(2, ClientIdentifier::query()->where('canonical_value', 'DEMO-0500')->count());
    }

    public function test_invalid_input_is_rejected(): void
    {
        $processor = $this->processor();

        foreach (['ab', 'has space', 'under_score', 'DEMO-', '-DEMO', 'ÉÉÉ-1', str_repeat('A', 33), ''] as $badId) {
            $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['identifier_value' => $badId]))
                ->assertSessionHasErrors('identifier_value');
        }

        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['display_name' => "Bad\x07Name"]))
            ->assertSessionHasErrors('display_name');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['display_name' => 'X']))
            ->assertSessionHasErrors('display_name');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['category' => 'graduate']))
            ->assertSessionHasErrors('category');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['identifier_type' => 'staff']))
            ->assertSessionHasErrors('identifier_type');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['source_reference' => '']))
            ->assertSessionHasErrors('source_reference');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['reason' => '']))
            ->assertSessionHasErrors('reason');

        $this->assertSame(0, ClientProfile::query()->count());
    }

    public function test_unicode_names_are_kept_and_extra_spaces_collapsed(): void
    {
        $this->actingAs($this->processor())->post(route('registrar.clients.store'), $this->newClientPayload([
            'display_name' => "  Zoë   Peña \t (Demo) ",
        ]));

        $this->assertSame('Zoë Peña (Demo)', ClientProfile::query()->sole()->display_name);
    }

    public function test_the_list_masks_ids_and_the_detail_page_shows_them(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);

        $this->actingAs($processor)->get(route('registrar.clients.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('registrar/clients/Index')
                ->has('profiles.data', 1)
                ->where('profiles.data.0.identifiers.0.masked', '•••••••0001')
                ->missing('profiles.data.0.identifiers.0.display_value'));

        $this->actingAs($processor)->get(route('registrar.clients.show', $profile))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('registrar/clients/Show')
                ->where('profile.identifiers.0.display_value', 'DEMO-S-0001')
                ->where('profile.version', 1)
                ->has('revisions', 1));
    }

    public function test_search_finds_names_and_the_start_of_ids(): void
    {
        $processor = $this->processor();
        $this->createProfile($processor, 'Juan Dela Cruz (Demo)', 'DEMO-S-0001');
        $this->createProfile($processor, 'Maria Santos (Demo)', 'DEMO-S-0002');
        $this->createProfile($processor, 'Elena Torres (Demo)', 'DEMO-A-0001', IdentifierType::Alumni);

        $count = fn (string $query): int => count($this->actingAs($processor)
            ->get(route('registrar.clients.index', ['search' => $query]))
            ->viewData('page')['props']['profiles']['data']);

        $this->assertSame(1, $count('dela cruz'));
        $this->assertSame(2, $count('DEMO-S'));
        $this->assertSame(2, $count('demo-s-000'));
        $this->assertSame(1, $count('DEMO-A-0001'));
        $this->assertSame(3, $count('d'), 'A single character is ignored, so everything is listed.');
        $this->assertSame(0, $count('no such person'));
        $this->assertSame(0, $count('%%'), 'Wildcard characters are matched literally.');
    }

    public function test_the_list_can_be_filtered_by_category_and_verification(): void
    {
        $processor = $this->processor();
        ClientProfile::factory()->withIdentifier()->category(ClientCategory::Alumni)->create();
        ClientProfile::factory()->withIdentifier()->verified()->create();
        ClientProfile::factory()->withIdentifier()->needsReview()->create();

        $filtered = fn (array $query): int => count($this->actingAs($processor)
            ->get(route('registrar.clients.index', $query))
            ->viewData('page')['props']['profiles']['data']);

        $this->assertSame(1, $filtered(['category' => 'alumni']));
        $this->assertSame(1, $filtered(['state' => 'verified']));
        $this->assertSame(1, $filtered(['state' => 'needs_review']));
        $this->assertSame(1, $filtered(['state' => 'unverified', 'category' => 'alumni']), 'Filters combine.');
        $this->assertSame(0, $filtered(['state' => 'verified', 'category' => 'alumni']), 'Filters combine.');
    }

    public function test_correcting_details_records_a_revision_and_bumps_the_version(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Juan Dela Cruz (Demo)');

        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), [
            'display_name' => 'Juan de la Cruz (Demo)',
            'category' => 'alumni',
            'reason' => 'Spelling on the source list',
            'version' => 1,
        ])->assertRedirect(route('registrar.clients.show', $profile));

        $profile->refresh();
        $this->assertSame('Juan de la Cruz (Demo)', $profile->display_name);
        $this->assertSame(ClientCategory::Alumni, $profile->category);
        $this->assertSame(2, $profile->version);

        $revision = $profile->revisions()->first();
        $this->assertSame(2, $revision->revision_number);
        $this->assertSame('Spelling on the source list', $revision->reason);
        // MySQL stores JSON objects with their keys re-ordered, so compare without regard to key order.
        $this->assertEquals(['old' => 'Juan Dela Cruz (Demo)', 'new' => 'Juan de la Cruz (Demo)'], $revision->changes['display_name']);
        $this->assertEquals(['old' => 'student', 'new' => 'alumni'], $revision->changes['category']);
    }

    public function test_a_correction_that_changes_nothing_is_refused(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);

        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), [
            'display_name' => $profile->display_name,
            'category' => 'student',
            'reason' => 'No real change',
            'version' => 1,
        ])->assertSessionHasErrors('display_name');

        $this->assertSame(1, $profile->fresh()->version);
    }

    public function test_a_stale_edit_is_rejected_and_nothing_is_overwritten(): void
    {
        $processor = $this->processor();
        $other = $this->processor();
        $profile = $this->createProfile($processor);

        // A colleague saves first.
        $this->actingAs($other)->patch(route('registrar.clients.update', $profile), [
            'display_name' => 'Colleague Change (Demo)',
            'category' => 'student',
            'reason' => 'Colleague edit',
            'version' => 1,
        ])->assertSessionHasNoErrors();

        // This screen still shows version 1.
        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), [
            'display_name' => 'Stale Change (Demo)',
            'category' => 'student',
            'reason' => 'Stale edit',
            'version' => 1,
        ])->assertSessionHasErrors('version');

        $this->assertSame('Colleague Change (Demo)', $profile->fresh()->display_name);
        $this->assertSame(2, $profile->fresh()->version);
        $this->assertSame(2, ClientProfileRevision::query()->where('client_profile_id', $profile->id)->count());
    }

    public function test_every_change_action_rejects_a_stale_version(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);
        $this->actingAs($processor)->post(route('registrar.clients.flag', $profile), ['reason' => 'First change', 'version' => 1]);

        $this->actingAs($processor)->post(route('registrar.clients.verify', $profile), ['reason' => 'Stale', 'version' => 1])->assertSessionHasErrors('version');
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), [
            'identifier_type' => 'alumni', 'identifier_value' => 'DEMO-A-0900', 'reason' => 'Stale', 'version' => 1,
        ])->assertSessionHasErrors('version');

        $identifier = $profile->identifiers()->sole();
        $this->actingAs($processor)->delete(route('registrar.clients.identifiers.destroy', [$profile, $identifier]), ['reason' => 'Stale', 'version' => 1])
            ->assertSessionHasErrors('version');

        $this->assertSame(VerificationState::NeedsReview, $profile->fresh()->verification_state);
        $this->assertSame(1, $profile->identifiers()->count());
    }

    public function test_verifying_records_who_and_when_and_correcting_returns_it_to_review(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);

        $this->actingAs($processor)->post(route('registrar.clients.verify', $profile), [
            'reason' => 'Checked against the master list', 'version' => 1,
        ])->assertRedirect(route('registrar.clients.show', $profile));

        $profile->refresh();
        $this->assertSame(VerificationState::Verified, $profile->verification_state);
        $this->assertSame($processor->id, $profile->verified_by_id);
        $this->assertNotNull($profile->verified_at);

        $this->actingAs($processor)->post(route('registrar.clients.verify', $profile), ['reason' => 'Again', 'version' => 2])
            ->assertSessionHasErrors('reason');

        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), [
            'display_name' => 'Juan Corrected (Demo)', 'category' => 'student', 'reason' => 'Name fix', 'version' => 2,
        ]);

        $profile->refresh();
        $this->assertSame(VerificationState::NeedsReview, $profile->verification_state);
        $this->assertNull($profile->verified_by_id);
        $this->assertNull($profile->verified_at);
        $this->assertEquals(['old' => 'verified', 'new' => 'needs_review'], $profile->revisions()->first()->changes['verification_state']);
    }

    public function test_flagging_marks_review_without_changing_the_person(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Alex Reyes (Demo)');

        $this->actingAs($processor)->post(route('registrar.clients.flag', $profile), [
            'reason' => 'Same name as another client', 'version' => 1,
        ])->assertRedirect();

        $profile->refresh();
        $this->assertSame(VerificationState::NeedsReview, $profile->verification_state);
        $this->assertSame('Alex Reyes (Demo)', $profile->display_name);
        $this->assertSame(1, ClientProfile::query()->count());

        $this->actingAs($processor)->post(route('registrar.clients.flag', $profile), ['reason' => 'Again', 'version' => 2])
            ->assertSessionHasErrors('reason');
    }

    public function test_an_id_can_be_added_to_the_same_person(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Rosa Villanueva (Demo)', 'DEMO-S-0101');

        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), [
            'identifier_type' => 'alumni', 'identifier_value' => 'DEMO-A-0101', 'reason' => 'Graduated', 'version' => 1,
        ])->assertRedirect(route('registrar.clients.show', $profile));

        $this->assertSame(1, ClientProfile::query()->count());
        $this->assertSame(2, $profile->currentIdentifiers()->count());
        $this->assertSame(2, $profile->fresh()->version);
    }

    public function test_an_id_recorded_on_another_person_cannot_be_added(): void
    {
        $processor = $this->processor();
        $this->createProfile($processor, 'Owner (Demo)', 'DEMO-S-0600');
        $other = $this->createProfile($processor, 'Someone Else (Demo)', 'DEMO-S-0601');

        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $other), [
            'identifier_type' => 'student', 'identifier_value' => 'demo-s-0600', 'reason' => 'Attempt', 'version' => 1,
        ])->assertSessionHasErrors('identifier_value');

        $this->assertSame(1, $other->identifiers()->count());
    }

    public function test_a_retired_id_is_kept_and_can_never_be_reused(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Retiring (Demo)', 'DEMO-S-0700');
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), [
            'identifier_type' => 'student', 'identifier_value' => 'DEMO-S-0701', 'reason' => 'Correct ID', 'version' => 1,
        ]);

        $wrong = $profile->identifiers()->where('display_value', 'DEMO-S-0700')->sole();

        $this->actingAs($processor)->delete(route('registrar.clients.identifiers.destroy', [$profile, $wrong]), [
            'reason' => 'Typing mistake', 'version' => 2,
        ])->assertRedirect(route('registrar.clients.show', $profile));

        $wrong->refresh();
        $this->assertNotNull($wrong->retired_at);
        $this->assertSame($processor->id, $wrong->retired_by_id);
        $this->assertSame('Typing mistake', $wrong->retire_reason);
        $this->assertSame(1, $profile->currentIdentifiers()->count());
        $this->assertSame(2, $profile->identifiers()->count(), 'The retired ID stays as history.');

        $stranger = $this->createProfile($processor, 'Stranger (Demo)', 'DEMO-S-0800');
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $stranger), [
            'identifier_type' => 'student', 'identifier_value' => 'DEMO-S-0700', 'reason' => 'Reuse attempt', 'version' => 1,
        ])->assertSessionHasErrors('identifier_value');
        $this->actingAs($processor)->post(route('registrar.clients.store'), $this->newClientPayload(['identifier_value' => 'demo-s-0700']))
            ->assertSessionHasErrors('identifier_value');
    }

    public function test_the_last_current_id_cannot_be_retired(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);
        $only = $profile->identifiers()->sole();

        $this->actingAs($processor)->delete(route('registrar.clients.identifiers.destroy', [$profile, $only]), [
            'reason' => 'Trying to remove it', 'version' => 1,
        ])->assertSessionHasErrors('reason');

        $this->assertTrue($only->fresh()->isCurrent());
        $this->assertSame(1, $profile->fresh()->version);
    }

    public function test_an_id_of_another_profile_cannot_be_retired_through_this_profile(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Mine (Demo)', 'DEMO-S-0900');
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), [
            'identifier_type' => 'student', 'identifier_value' => 'DEMO-S-0902', 'reason' => 'Second', 'version' => 1,
        ]);
        $other = $this->createProfile($processor, 'Theirs (Demo)', 'DEMO-S-0901');
        $theirs = $other->identifiers()->sole();

        $this->actingAs($processor)->delete(route('registrar.clients.identifiers.destroy', [$profile, $theirs]), [
            'reason' => 'Wrong profile', 'version' => $profile->fresh()->version,
        ])->assertSessionHasErrors('reason');

        $this->assertTrue($theirs->fresh()->isCurrent());
    }

    public function test_verification_needs_a_current_id(): void
    {
        $processor = $this->processor();
        $profile = ClientProfile::factory()->create(['created_by_id' => $processor->id]);

        $this->actingAs($processor)->post(route('registrar.clients.verify', $profile), ['reason' => 'No ID', 'version' => 1])
            ->assertSessionHasErrors('reason');

        $this->assertSame(VerificationState::Unverified, $profile->fresh()->verification_state);
    }

    public function test_every_successful_change_has_one_revision_and_one_audit_event(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor);

        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), ['display_name' => 'Changed (Demo)', 'category' => 'both', 'reason' => 'Fix', 'version' => 1]);
        $this->actingAs($processor)->post(route('registrar.clients.verify', $profile), ['reason' => 'Checked', 'version' => 2]);
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), ['identifier_type' => 'alumni', 'identifier_value' => 'DEMO-A-0001', 'reason' => 'Graduated', 'version' => 3]);
        $this->actingAs($processor)->post(route('registrar.clients.flag', $profile), ['reason' => 'Check again', 'version' => 4]);

        $profile->refresh();
        $revisions = $profile->revisions()->get();

        $this->assertSame(5, $profile->version);
        $this->assertSame([5, 4, 3, 2, 1], $revisions->pluck('revision_number')->all());

        $audit = AuditEvent::query()->where('subject_type', 'client_profile')->where('subject_id', (string) $profile->id)->orderBy('id')->get();
        $this->assertSame(
            ['client.created', 'client.corrected', 'client.verified', 'client.identifier_added', 'client.flagged_for_review'],
            $audit->pluck('action')->all(),
        );
        $this->assertSame([1, 2, 3, 4, 5], $audit->map(fn (AuditEvent $event) => $event->context['revision'])->all());
        $this->assertTrue($audit->every(fn (AuditEvent $event) => $event->office_id !== null && $event->actor_user_id === $processor->id));
    }

    public function test_the_audit_trail_holds_no_names_and_no_full_ids(): void
    {
        $processor = $this->processor();
        $profile = $this->createProfile($processor, 'Confidential Person (Demo)', 'DEMO-S-5555');
        $this->actingAs($processor)->patch(route('registrar.clients.update', $profile), ['display_name' => 'Renamed Person (Demo)', 'category' => 'alumni', 'reason' => 'Fix', 'version' => 1]);
        $this->actingAs($processor)->post(route('registrar.clients.identifiers.store', $profile), ['identifier_type' => 'alumni', 'identifier_value' => 'DEMO-A-5556', 'reason' => 'Graduated', 'version' => 2]);

        $text = AuditEvent::query()->where('action', 'like', 'client.%')->get()
            ->map(fn (AuditEvent $event): string => json_encode([$event->changes, $event->context]) ?: '')
            ->implode(' ');

        foreach (['Confidential Person', 'Renamed Person', 'DEMO-S-5555', 'DEMO-A-5556'] as $secret) {
            $this->assertStringNotContainsString($secret, $text, "Audit data must not contain {$secret}.");
        }

        $this->assertStringContainsString('5555', $text, 'Only the masked tail of an ID is recorded.');
    }

    public function test_revisions_cannot_be_changed_or_deleted(): void
    {
        $profile = $this->createProfile($this->processor());
        $revision = $profile->revisions()->sole();

        $this->expectException(LogicException::class);

        $revision->update(['reason' => 'Rewritten history']);
    }

    public function test_a_profile_cannot_be_deleted_while_it_has_history(): void
    {
        $profile = $this->createProfile($this->processor());

        $this->expectException(QueryException::class);

        $profile->delete();
    }

    public function test_the_audit_trail_viewer_labels_client_events(): void
    {
        $admin = User::factory()->withTwoFactor()->withRole(Role::SystemAdmin)->create();
        $this->createProfile($this->processor());

        $this->actingAs($admin)->get(route('admin.audit.index', ['action' => 'client.created']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('events.data', 1)
                ->where('events.data.0.action_label', 'Client profile created')
                ->where('events.data.0.subject_type', 'client_profile')
                ->where('events.data.0.office', 'REGISTRAR'));
    }
}
