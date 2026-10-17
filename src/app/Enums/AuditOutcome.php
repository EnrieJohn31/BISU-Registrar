<?php

namespace App\Enums;

enum AuditOutcome: string
{
    case Succeeded = 'succeeded';
    case Denied = 'denied';
}
