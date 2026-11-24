<?php

namespace Tests\Feature\Auth;

use App\Models\EtudiantEneam;
use App\Models\Filiere;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_from_the_eneam_reference_list(): void
    {
        Notification::fake();

        $filiere = Filiere::create([
            'code' => 'INFO',
            'nom' => 'Informatique',
        ]);

        EtudiantEneam::create([
            'matricule' => '12345678',
            'email' => 'etudiant@example.com',
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'filiere_id' => $filiere->id,
        ]);

        $response = $this->postJson('/api/student/register', [
            'matricule' => '12345678',
            'email' => 'etudiant@example.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', [
            'matricule' => '12345678',
            'email' => 'etudiant@example.com',
            'role' => 'etudiant',
            'actif' => true,
        ]);
    }

    public function test_student_cannot_register_an_unknown_matricule(): void
    {
        $response = $this->postJson('/api/student/register', [
            'matricule' => '87654321',
            'email' => 'unknown@example.com',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
    }
}
