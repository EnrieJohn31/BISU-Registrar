<?php

namespace App\Actions\Clients;

use App\Enums\AuditAction;
use App\Enums\ClientRevisionAction;
use App\Enums\VerificationState;
use App\Models\ClientProfile;
use App\Models\User;
use App\Support\Clients\ClientProfileRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class FlagClientProfileForReview
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Flag a profile for manual review: a similar name, a name change, a possible
     * duplicate or a conflicting ID. Never merges or changes the person.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, ClientProfile $profile, int $expectedVersion, string $reason): ClientProfile
    {
        return DB::transaction(function () use ($actor, $profile, $expectedVersion, $reason): ClientProfile {
            $locked = $this->recorder->lockForChange($profile, $expectedVersion);

            if ($locked->verification_state === VerificationState::NeedsReview) {
                throw ValidationException::withMessages(['reason' => __('This profile is already flagged for review.')]);
            }

            $change = ['old' => $locked->verification_state->value, 'new' => VerificationState::NeedsReview->value];

            $locked->verification_state = VerificationState::NeedsReview;
            $locked->verified_by_id = null;
            $locked->verified_at = null;

            $this->recorder->commit(
                $locked,
                ClientRevisionAction::FlaggedForReview,
                AuditAction::ClientFlaggedForReview,
                $actor,
                $reason,
                ['verification_state' => $change],
                ['verification_state' => $change],
            );

            return $locked;
        });
    }
}
