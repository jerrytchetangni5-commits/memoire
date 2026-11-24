<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use App\Notifications\MemoireRejeteNotification;
use App\Services\SimilariteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimilariteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_selects_the_best_memoire_from_earlier_years_only(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $user = $this->createStudent($filiere);
        $anneePrecedente = AnneeAcademique::create(['libelle' => '2024-2025']);
        $anneeCourante = AnneeAcademique::create(['libelle' => '2025-2026']);

        $memoirePrecedent = $this->createMemoire($user, $filiere, $anneePrecedente);
        $this->createMemoire($user, $filiere, $anneeCourante);

        $correspondance = app(SimilariteService::class)->meilleureCorrespondance(
            'Réseaux neuronaux adaptatifs',
            'Modélisation prédictive par apprentissage profond',
            $anneeCourante->id
        );

        $this->assertSame($memoirePrecedent->id, $correspondance['memoire_id']);
        $this->assertSame(100.0, $correspondance['score']);
    }

    public function test_submission_persists_similarity_and_rejects_a_close_match(): void
    {
        Notification::fake();
        Storage::fake('local');

        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $user = $this->createStudent($filiere);
        $anneePrecedente = AnneeAcademique::create(['libelle' => '2024-2025']);
        $anneeCourante = AnneeAcademique::create(['libelle' => '2025-2026']);
        $memoirePrecedent = $this->createMemoire($user, $filiere, $anneePrecedente);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/memoires', [
            'titre' => 'Réseaux neuronaux adaptatifs',
            'resume' => 'Modélisation prédictive par apprentissage profond',
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $anneeCourante->id,
            'fichier' => UploadedFile::fake()->create('memoire.pdf', 10, 'application/pdf'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('memoire.statut', 'rejete')
            ->assertJsonPath('memoire.similarite_score', 100)
            ->assertJsonPath('memoire.memoire_similaire_id', $memoirePrecedent->id);

        $this->assertDatabaseHas('memoires', [
            'id' => $response->json('memoire.id'),
            'statut' => 'rejete',
            'similarite_score' => 100,
            'memoire_similaire_id' => $memoirePrecedent->id,
        ]);
        Notification::assertSentTo($user, MemoireRejeteNotification::class);
    }

    private function createStudent(Filiere $filiere): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'matricule' => '12345678',
            'email' => 'jane@example.com',
            'password' => 'password',
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
    }

    private function createMemoire(
        User $user,
        Filiere $filiere,
        AnneeAcademique $annee
    ): Memoire {
        return Memoire::create([
            'titre' => 'Réseaux neuronaux adaptatifs',
            'resume' => 'Modélisation prédictive par apprentissage profond',
            'user_id' => $user->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/precedent.pdf',
        ]);
    }
}
