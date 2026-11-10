<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_old_two_factor_data_does_not_block_normal_login(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => 'legacy-encrypted-secret',
            'two_factor_recovery_codes' => 'legacy-encrypted-codes',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(RouteServiceProvider::HOME);
        $this->assertAuthenticatedAs($user);
        $this->get('/bojong/security/two-factor')->assertNotFound();
    }
}
