<?php

namespace Tests\Feature;

use App\Models\Content;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingLocationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_locations_are_passed_to_the_new_landing_safely_and_reflect_cms_status(): void
    {
        $this->seed(CmsContentSeeder::class);
        Content::putItem('location', 'card_1', [
            'name' => 'Warehouse <script>alert(1)</script>',
            'area' => 'Jakarta',
            'address' => 'Alamat tes',
            'status' => 'coming',
        ], 1);

        $response = $this->get('/')->assertOk();
        $response->assertSee('window.__ANAGATA_LOCATIONS__', false)
            ->assertSee('\\u003Cscript\\u003E', false)
            ->assertSee('"status":"coming"', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
