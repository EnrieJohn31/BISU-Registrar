<?php

namespace App\Enums;

enum VerificationState: string
{
    case Unverified = 'unverified';
    case Verified = 'verified';
    case NeedsReview = 'needs_review';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::Verified => 'Verified',
            self::NeedsReview => 'Needs review',
        };
    }
}
