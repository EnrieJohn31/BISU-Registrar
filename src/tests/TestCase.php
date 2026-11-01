<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Turns a Fortify feature off for the rest of the test. Its routes stay
     * registered; only pages and checks that read the setting are affected.
     */
    protected function disableFortifyFeature(string $feature): void
    {
        config(['fortify.features' => array_values(array_diff((array) config('fortify.features'), [$feature]))]);
    }
}
