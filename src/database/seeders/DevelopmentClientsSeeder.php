<?php

namespace Database\Seeders;

use App\Actions\Clients\AddClientIdentifier;
use App\Actions\Clients\CreateClientProfile;
use App\Actions\Clients\FlagClientProfileForReview;
use App\Actions\Clients\RetireClientIdentifier;
use App\Actions\Clients\VerifyClientProfile;
use App\Enums\ClientCategory;
use App\Enums\IdentifierType;
use App\Models\ClientIdentifier;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Synthetic demo clients for local testing of Part 04. Every name ends in
 * "(Demo)" and every ID starts with "DEMO", so none can be mistaken for a real
 * person. Safe to run again: existing demo clients are skipped. Never runs in
 * production. Needs the staff accounts from DevelopmentAccountsSeeder.
 */
class DevelopmentClientsSeeder extends Seeder
{
    private const REASON = 'Synthetic demo record (DevelopmentClientsSeeder)';

    private const SOURCE = 'Demo data, not a real person';

    public function __construct(
        private readonly CreateClientProfile $create,
        private readonly VerifyClientProfile $verify,
        private readonly FlagClientProfileForReview $flag,
        private readonly AddClientIdentifier $addIdentifier,
        private readonly RetireClientIdentifier $retireIdentifier,
    ) {}

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('Skipped: demo clients are never created in production.');

            return;
        }

        $actor = User::query()->where('email', 'processor@bisu.test')->first();

        if ($actor === null) {
            $this->command->error('Run DevelopmentAccountsSeeder first: the demo clients are created by processor@bisu.test.');

            return;
        }

        $created = 0;

        foreach ($this->clients() as $client) {
            $existing = ClientIdentifier::query()
                ->where('type', $client['type']->value)
                ->where('canonical_value', strtoupper($client['id']))
                ->exists();

            if ($existing) {
                continue;
            }

            $profile = $this->create->handle(
                $actor,
                $client['name'],
                $client['category'],
                self::SOURCE,
                $client['type'],
                $client['id'],
                self::REASON,
            );
            $created++;

            if (isset($client['also'])) {
                [$type, $value] = $client['also'];
                $this->addIdentifier->handle($actor, $profile->refresh(), $profile->version, $type, $value, self::REASON);
            }

            if (isset($client['retire'])) {
                $this->addIdentifier->handle($actor, $profile->refresh(), $profile->version, $client['type'], $client['retire']['replacement'], self::REASON);
                $wrong = ClientIdentifier::query()->where('client_profile_id', $profile->id)->where('canonical_value', strtoupper($client['id']))->firstOrFail();
                $this->retireIdentifier->handle($actor, $profile->refresh(), $wrong, $profile->version, 'Typing mistake in the original ID');
            }

            $state = $client['state'] ?? 'unverified';

            if ($state === 'verified') {
                $this->verify->handle($actor, $profile->refresh(), $profile->version, 'Checked against the demo source list');
            } elseif ($state === 'needs_review') {
                $this->flag->handle($actor, $profile->refresh(), $profile->version, 'Another demo client has the same name');
            }
        }

        $total = ClientProfile::query()->count();
        $this->command->info("Demo clients ready: {$created} created, {$total} client profiles in total.");
        $this->command->line('Try searching for: DEMO-S-0001, DEMO-A, reyes, zoë, 00123456, villanueva.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function clients(): array
    {
        $student = IdentifierType::Student;
        $alumni = IdentifierType::Alumni;

        return [
            ['name' => 'Juan Dela Cruz (Demo)', 'id' => 'DEMO-S-0001', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'verified'],
            ['name' => 'Maria Santos (Demo)', 'id' => 'DEMO-S-0002', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'verified'],
            ['name' => 'Pedro Reyes (Demo)', 'id' => 'DEMO-S-0003', 'type' => $student, 'category' => ClientCategory::Student],
            ['name' => 'Ana Lim (Demo)', 'id' => 'DEMO-S-0004', 'type' => $student, 'category' => ClientCategory::Student],
            ['name' => 'Jose Garcia (Demo)', 'id' => 'DEMO-S-0005', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'verified'],
            ['name' => 'Luz Mendoza (Demo)', 'id' => 'DEMO-S-0006', 'type' => $student, 'category' => ClientCategory::Student],
            ['name' => 'Carlo Bautista (Demo)', 'id' => 'DEMO-S-0007', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'verified'],
            ['name' => 'Elena Torres (Demo)', 'id' => 'DEMO-A-0001', 'type' => $alumni, 'category' => ClientCategory::Alumni, 'state' => 'verified'],
            ['name' => 'Ramon Aquino (Demo)', 'id' => 'DEMO-A-0002', 'type' => $alumni, 'category' => ClientCategory::Alumni, 'state' => 'verified'],
            ['name' => 'Teresa Navarro (Demo)', 'id' => 'DEMO-A-0003', 'type' => $alumni, 'category' => ClientCategory::Alumni],
            ['name' => 'Gabriel Ramos (Demo)', 'id' => 'DEMO-A-0004', 'type' => $alumni, 'category' => ClientCategory::Alumni],
            // One person with both a Student ID and an Alumni ID (a student who graduated).
            ['name' => 'Rosa Villanueva (Demo)', 'id' => 'DEMO-S-0101', 'type' => $student, 'category' => ClientCategory::Both, 'also' => [$alumni, 'DEMO-A-0101'], 'state' => 'verified'],
            // Identical names are kept as separate people; one is flagged for review.
            ['name' => 'Alex Reyes (Demo)', 'id' => 'DEMO-S-0201', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'verified'],
            ['name' => 'Alex Reyes (Demo)', 'id' => 'DEMO-S-0202', 'type' => $student, 'category' => ClientCategory::Student, 'state' => 'needs_review'],
            // Unicode and a very long name, for layout checks.
            ['name' => 'Zoë Peña (Demo)', 'id' => 'DEMO-S-0301', 'type' => $student, 'category' => ClientCategory::Student],
            ['name' => 'Maria Concepcion Magdalena de los Santos-Villanueva y Fernandez (Demo)', 'id' => 'DEMO-S-0302', 'type' => $student, 'category' => ClientCategory::Student],
            // A numeric-looking ID: leading zeroes must survive.
            ['name' => 'Leading Zero Case (Demo)', 'id' => '00123456', 'type' => $student, 'category' => ClientCategory::Student],
            // An ID that was a typing mistake and was retired after the correct one was added.
            ['name' => 'Retired ID Case (Demo)', 'id' => 'DEMO-S-0401', 'type' => $student, 'category' => ClientCategory::Student, 'retire' => ['replacement' => 'DEMO-S-0402']],
            ['name' => 'Not Stated Category (Demo)', 'id' => 'DEMO-S-0501', 'type' => $student, 'category' => ClientCategory::Unknown],
            ['name' => 'Isabel Cortez (Demo)', 'id' => 'DEMO-A-0005', 'type' => $alumni, 'category' => ClientCategory::Alumni, 'state' => 'needs_review'],
        ];
    }
}
