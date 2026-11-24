<?php

namespace Tests\Feature\Auth;

use App\Models\EtudiantEneam;
use App\Models\Filiere;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        Notification::fake();
        $filiere = Filiere::create([
            'code' => 'INFO',
            'nom' => 'Informatique',
        ]);
        EtudiantEneam::create([
            'matricule' => '12345678',
            'email' => 'test@example.com',
            'nom' => 'User',
            'prenom' => 'Test',
            'filiere_id' => $filiere->id,
        ]);

        $response = $this->post('/inscription', [
            'matricule' => '12345678',
            'email' => 'test@example.com',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'matricule' => '12345678',
            'email' => 'test@example.com',
            'role' => 'etudiant',
        ]);
    }
}
