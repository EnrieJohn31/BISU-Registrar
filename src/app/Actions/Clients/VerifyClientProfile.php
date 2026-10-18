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

final class VerifyClientProfile
{
    public function __construct(private readonly ClientProfileRecorder $recorder) {}

    /**
     * Mark the profile verified against a source the verifier names in the reason.
     * Verification is a controlled action: nothing imported or typed in a form
     * can set it, and it records who verified and when.
     *
     * @throws ValidationException
     */
    public function handle(User $actor, ClientProfile $profile, int $expectedVersion, string $reason): ClientProfile
    {
        return DB::transaction(function () use ($actor, $profile, $expectedVersion, $reason): ClientProfile {
            $locked = $this->recorder->lockForChange($profile, $expectedVersion);

            if ($locked->verification_state === VerificationState::Verified) {
                throw ValidationException::withMessages(['reason' => __('This profile is already verified.')]);
            }

            if (! $locked->currentIdentifiers()->exists()) {
                throw ValidationException::withMessages(['reason' => __('Add a current ID before verifying this profile.')]);
            }

            $change = ['old' => $locked->verification_state->value, 'new' => VerificationState::Verified->value];

            $locked->verification_state = VerificationState::Verified;
            $locked->verified_by_id = $actor->id;
            $locked->verified_at = now();

            $this->recorder->commit(
                $locked,
                ClientRevisionAction::Verified,
                AuditAction::ClientVerified,
                $actor,
                $reason,
                ['verification_state' => $change],
                ['verification_state' => $change],
            );

            return $locked;
        });
    }
}
