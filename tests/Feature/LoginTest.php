<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_the_login_form(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Masuk ke Akun Anda')
            ->assertSee('name="username"', false);
    }

    public function test_seeded_admin_can_log_in_to_the_admin_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post(route('login.authenticate'), [
            'username' => 'admin',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');
    }

    public function test_seeded_cashier_can_log_in_to_the_cashier_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post(route('login.authenticate'), [
            'username' => 'kasir',
            'password' => 'password123',
        ])->assertRedirect(route('kasir.index'));

        $this->get(route('kasir.index'))
            ->assertOk()
            ->assertSee('Dashboard Kasir');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post(route('login.authenticate'), [
            'username' => 'admin',
            'password' => 'salah',
        ])->assertSessionHasErrors('username');
    }
}
