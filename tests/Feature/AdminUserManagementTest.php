<?php

namespace Tests\Feature;

use App\Models\Filiere;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_student_and_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $this->actingAs($admin);

        $this->post(route('admin.utilisateurs.store'), [
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'email' => 'jane@example.com',
            'role' => 'etudiant',
            'matricule' => '12345678',
            'filiere_id' => $filiere->id,
        ])->assertRedirect(route('admin.utilisateurs.index'));

        $student = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame('etudiant', $student->role);
        $this->assertTrue($student->actif);
        $this->assertNull($student->password);

        $this->post(route('admin.utilisateurs.store'), [
            'nom' => 'Smith',
            'prenom' => 'John',
            'email' => 'john@example.com',
            'role' => 'responsable',
            'filiere_id' => $filiere->id,
            'password' => 'Responsable123',
            'password_confirmation' => 'Responsable123',
        ])->assertRedirect(route('admin.utilisateurs.index'));

        $responsable = User::where('email', 'john@example.com')->firstOrFail();
        $this->assertSame('responsable', $responsable->role);
        $this->assertTrue(Hash::check('Responsable123', $responsable->password));
    }

    public function test_admin_can_activate_and_deactivate_another_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'etudiant',
            'actif' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.utilisateurs.statut', $student))
            ->assertRedirect()
            ->assertSessionHas('status', 'Le compte utilisateur a été activé.');

        $this->assertDatabaseHas('users', ['id' => $student->id, 'actif' => true]);

        $this->patch(route('admin.utilisateurs.statut', $student))
            ->assertRedirect()
            ->assertSessionHas('status', 'Le compte utilisateur a été désactivé.');

        $this->assertDatabaseHas('users', ['id' => $student->id, 'actif' => false]);
    }

    public function test_admin_cannot_deactivate_their_own_account_and_inactive_admins_are_blocked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.utilisateurs.statut', $admin))
            ->assertSessionHasErrors('user_action');

        $this->patch(route('admin.utilisateurs.statut', $otherAdmin))
            ->assertSessionHas('status', 'Le compte utilisateur a été désactivé.');

        $this->actingAs($otherAdmin->refresh())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_only_admins_can_access_user_management(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);

        $this->actingAs($responsable)
            ->get(route('admin.utilisateurs.index'))
            ->assertForbidden();
    }

    public function test_user_creation_validates_role_specific_fields_and_unique_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($admin)
            ->post(route('admin.utilisateurs.store'), [
                'nom' => 'Doe',
                'prenom' => 'Jane',
                'email' => $existing->email,
                'role' => 'etudiant',
                'matricule' => '12345678',
            ])
            ->assertSessionHasErrors(['email', 'filiere_id']);
    }
}
