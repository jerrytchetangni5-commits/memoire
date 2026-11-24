<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use App\Notifications\MemoireValideNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResponsableValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_returns_similarity_and_applies_a_one_day_embargo(): void
    {
        Notification::fake();

        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $etudiant = $this->createUser('etudiant', $filiere, 'etudiant@example.com');
        $responsable = $this->createUser('responsable', $filiere, 'responsable@example.com');

        $memoireSimilaire = $this->createMemoire($etudiant, $filiere, $annee, 'Mémoire antérieur');
        $memoire = $this->createMemoire($etudiant, $filiere, $annee, 'Mémoire soumis', [
            'memoire_similaire_id' => $memoireSimilaire->id,
            'similarite_score' => 45.5,
        ]);

        Sanctum::actingAs($responsable);

        $this->getJson("/api/validation/memoires/{$memoire->id}")
            ->assertOk()
            ->assertJsonPath('memoire_similaire.titre', 'Mémoire antérieur')
            ->assertJsonPath('similarite_score', 45.5);

        $beforeValidation = now();

        $response = $this->patchJson("/api/validation/memoires/{$memoire->id}/valider")
            ->assertOk()
            ->assertJsonPath('memoire.statut', 'valide');

        $dateEmbargo = Carbon::parse($response->json('memoire.date_embargo'));
        $this->assertEqualsWithDelta(
            86400,
            $dateEmbargo->getTimestamp() - $beforeValidation->getTimestamp(),
            2
        );
        $this->assertDatabaseHas('memoires', [
            'id' => $memoire->id,
            'statut' => 'valide',
            'memoire_similaire_id' => $memoireSimilaire->id,
            'similarite_score' => 45.5,
        ]);

        Notification::assertSentTo($etudiant, MemoireValideNotification::class);
    }

    private function createUser(string $role, Filiere $filiere, string $email): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'filiere_id' => $filiere->id,
        ]);
    }

    private function createMemoire(
        User $etudiant,
        Filiere $filiere,
        AnneeAcademique $annee,
        string $titre,
        array $attributes = []
    ): Memoire {
        return Memoire::create($attributes + [
            'titre' => $titre,
            'resume' => 'Résumé de test',
            'user_id' => $etudiant->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/test.pdf',
            'statut' => 'soumis',
        ]);
    }
}
