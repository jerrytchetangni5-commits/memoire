<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MemoireAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_submit_a_memoire_for_another_filiere(): void
    {
        $studentFiliere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $otherFiliere = Filiere::create(['code' => 'GEST', 'nom' => 'Gestion']);
        $student = $this->createStudent($studentFiliere);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/memoires', [
            'titre' => 'Mémoire test',
            'resume' => 'Résumé de test',
            'filiere_id' => $otherFiliere->id,
            'annee_academique_id' => $this->createAnnee()->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('filiere_id');
        $this->assertDatabaseCount('memoires', 0);
    }

    public function test_student_cannot_favorite_a_memoire_that_is_not_published(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $student = $this->createStudent($filiere);
        $memoire = $this->createMemoire($student, $filiere, 'soumis');

        Sanctum::actingAs($student);

        $this->postJson('/api/favoris', ['memoire_id' => $memoire->id])
            ->assertNotFound();

        $this->assertDatabaseCount('favoris', 0);
    }

    private function createStudent(Filiere $filiere): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'password',
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
    }

    private function createAnnee(): AnneeAcademique
    {
        return AnneeAcademique::create(['libelle' => '2025-2026']);
    }

    private function createMemoire(User $student, Filiere $filiere, string $statut): Memoire
    {
        return Memoire::create([
            'titre' => 'Mémoire test',
            'resume' => 'Résumé de test',
            'user_id' => $student->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $this->createAnnee()->id,
            'fichier' => 'memoires/test.pdf',
            'statut' => $statut,
        ]);
    }
}
