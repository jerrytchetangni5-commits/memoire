<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_and_open_the_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'MotDePasse123',
            'actif' => true,
        ]);

        $response = $this->post('/admin/connexion', [
            'email' => $admin->email,
            'password' => 'MotDePasse123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')
            ->assertOk()
            ->assertSeeText('Dashboard admin')
            ->assertSeeText('Bibliothèque des mémoires')
            ->assertSee('admin/deconnexion');
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/admin/deconnexion')
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_responsable_can_sign_in_to_the_admin_area(): void
    {
        $responsable = User::factory()->create([
            'role' => 'responsable',
            'password' => 'MotDePasse123',
        ]);

        $this->post('/admin/connexion', [
            'email' => $responsable->email,
            'password' => 'MotDePasse123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($responsable);
    }

    public function test_guest_is_redirected_to_admin_login_and_student_is_forbidden(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $student = User::factory()->create(['role' => 'etudiant']);
        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_students_inactive_accounts_and_wrong_password_cannot_sign_in(): void
    {
        $student = User::factory()->create(['role' => 'etudiant']);
        $inactiveAdmin = User::factory()->create([
            'role' => 'admin',
            'password' => 'MotDePasse123',
            'actif' => false,
        ]);

        foreach ([
            [$student->email, 'password'],
            [$inactiveAdmin->email, 'MotDePasse123'],
            [$inactiveAdmin->email, 'incorrect'],
        ] as [$email, $password]) {
            $this->post('/admin/connexion', compact('email', 'password'))
                ->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }
}
