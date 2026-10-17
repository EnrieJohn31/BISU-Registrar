<?php

namespace App\Support\Audit;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * One id per HTTP request or queued job, so all audit events caused by a single
 * action can be grouped. Laravel's Context carries it into queued jobs.
 */
final class CorrelationId
{
    public const CONTEXT_KEY = 'correlation_id';

    public static function start(): string
    {
        $id = (string) Str::uuid();

        Context::add(self::CONTEXT_KEY, $id);

        return $id;
    }

    public static function current(): string
    {
        $id = Context::get(self::CONTEXT_KEY);

        return is_string($id) && $id !== '' ? $id : self::start();
    }
}
