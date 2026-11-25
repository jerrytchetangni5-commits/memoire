<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_and_updates_the_configured_admin_account(): void
    {
        config([
            'admin.email' => 'admin@example.test',
            'admin.password' => 'A-local-test-password',
        ]);

        $this->seed(AdminSeeder::class);

        $user = User::where('email', 'admin@example.test')->firstOrFail();
        $user->update(['role' => 'etudiant', 'actif' => false]);

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'role' => 'admin',
            'actif' => true,
        ]);
        $this->assertTrue(Hash::check('A-local-test-password', $user->fresh()->password));

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.test',
            'password' => 'A-local-test-password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_it_fails_explicitly_when_admin_password_is_not_configured(): void
    {
        config([
            'admin.email' => 'admin@example.test',
            'admin.password' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Set ADMIN_PASSWORD in .env');

        $this->seed(AdminSeeder::class);
    }
}
