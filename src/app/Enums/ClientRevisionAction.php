<?php

namespace App\Enums;

enum ClientRevisionAction: string
{
    case Created = 'created';
    case Corrected = 'corrected';
    case Verified = 'verified';
    case FlaggedForReview = 'flagged_for_review';
    case IdentifierAdded = 'identifier_added';
    case IdentifierRetired = 'identifier_retired';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Profile created',
            self::Corrected => 'Details corrected',
            self::Verified => 'Verified',
            self::FlaggedForReview => 'Flagged for review',
            self::IdentifierAdded => 'ID added',
            self::IdentifierRetired => 'ID retired',
        };
    }
}
