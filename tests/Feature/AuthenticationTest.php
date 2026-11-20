<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_for_guests(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke Akun Anda');
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_with_username(): void
    {
        $this->seed();

        $this->post('/login', ['login' => 'owner', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_email(): void
    {
        $this->seed();

        $this->post('/login', ['login' => 'kasir@vogile.com', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->seed();

        $this->from('/login')
            ->post('/login', ['login' => 'owner', 'password' => 'password-salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->seed();

        $user = \App\Models\User::where('username', 'owner')->firstOrFail();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
