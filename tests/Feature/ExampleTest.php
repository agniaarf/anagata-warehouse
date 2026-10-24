<?php

namespace Tests\Feature;

use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The landing page now renders from cms_contents, so it needs the
     * table migrated + seeded rather than just a route to exist.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(CmsContentSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
