<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_welcome_page_offers_registration_only_when_it_is_enabled(): void
    {
        $this->skipUnlessFortifyHas(Features::registration());

        $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('canRegister', true));

        $this->disableFortifyFeature(Features::registration());

        $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('canRegister', false));
    }
}
