<?php

namespace App\Actions\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientCategory;
use App\Enums\ClientRevisionAction;
use App\Enums\VerificationState;
use App\Models\ClientProfile;
use App\Models\User;
use App\Support\Clients\ClientProfileRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CorrectClientProfile
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Correct the name or category. Correcting a verified profile returns it to
     * "needs review": someone confirmed the old values, not the new ones.
     *
     * @throws ValidationException
     */
    public function handle(
        User $actor,
        ClientProfile $profile,
        int $expectedVersion,
        string $displayName,
        ClientCategory $category,
        string $reason,
    ): ClientProfile {
        return DB::transaction(function () use ($actor, $profile, $expectedVersion, $displayName, $category, $reason): ClientProfile {
            $locked = $this->recorder->lockForChange($profile, $expectedVersion);

            $changes = [];
            $auditChanges = [];
            $fields = [];

            if ($locked->display_name !== $displayName) {
                $changes['display_name'] = ['old' => $locked->display_name, 'new' => $displayName];
                $fields[] = 'display_name';
            }

            if ($locked->category !== $category) {
                $changes['category'] = ['old' => $locked->category->value, 'new' => $category->value];
                $auditChanges['category'] = $changes['category'];
                $fields[] = 'category';
            }

            if ($changes === []) {
                throw ValidationException::withMessages(['display_name' => __('Nothing was changed. Edit the name or category to save a correction.')]);
            }

            $locked->display_name = $displayName;
            $locked->category = $category;

            if ($locked->verification_state === VerificationState::Verified) {
                $changes['verification_state'] = ['old' => VerificationState::Verified->value, 'new' => VerificationState::NeedsReview->value];
                $auditChanges['verification_state'] = $changes['verification_state'];
                $locked->verification_state = VerificationState::NeedsReview;
                $locked->verified_by_id = null;
                $locked->verified_at = null;
            }

            $this->recorder->commit(
                $locked,
                ClientRevisionAction::Corrected,
                AuditAction::ClientCorrected,
                $actor,
                $reason,
                $changes,
                $auditChanges,
                ['fields' => $fields],
            );

            return $locked;
        });
    }
}
