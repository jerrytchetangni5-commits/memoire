<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMemoireDepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_deposit_a_memoire_without_an_existing_student_account(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        [$filiere, $annee] = $this->createAcademicCatalog();

        $this->actingAs($admin)
            ->post(route('admin.memoires.store'), [
                'titre' => 'Ancien mémoire',
                'resume' => 'Résumé du mémoire d’archive.',
                'auteur_nom' => 'Kouassi',
                'auteur_prenom' => 'Aminata',
                'filiere_id' => $filiere->id,
                'annee_academique_id' => $annee->id,
                'fichier' => UploadedFile::fake()->create('memoire.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.memoires.create'))
            ->assertSessionHas('status');

        $memoire = Memoire::where('titre', 'Ancien mémoire')->firstOrFail();

        $this->assertNull($memoire->user_id);
        $this->assertSame('Kouassi', $memoire->auteur_nom);
        $this->assertSame('Aminata', $memoire->auteur_prenom);
        $this->assertSame('Aminata Kouassi', $memoire->auteur_complet);
        $this->assertSame('valide', $memoire->statut);
        $this->assertSame($admin->id, $memoire->valide_par);
        $this->assertNotNull($memoire->date_validation);
        Storage::disk('local')->assertExists($memoire->fichier);
    }

    public function test_admin_can_link_an_existing_student_and_uses_their_recorded_name(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        [$filiere, $annee] = $this->createAcademicCatalog();
        $student = User::factory()->create([
            'nom' => 'Mensah',
            'prenom' => 'Ama',
            'matricule' => '12345678',
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.memoires.store'), [
                'titre' => 'Mémoire lié',
                'resume' => 'Résumé',
                'user_id' => $student->id,
                'filiere_id' => $filiere->id,
                'annee_academique_id' => $annee->id,
                'fichier' => UploadedFile::fake()->create('memoire.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.memoires.create'));

        $memoire = Memoire::where('titre', 'Mémoire lié')->firstOrFail();
        $this->assertSame($student->id, $memoire->user_id);
        $this->assertSame('Mensah', $memoire->auteur_nom);
        $this->assertSame('Ama', $memoire->auteur_prenom);
    }

    public function test_selected_students_must_match_the_memoires_filiere(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        [$filiere, $annee] = $this->createAcademicCatalog();
        $autreFiliere = Filiere::create(['code' => 'GEST', 'nom' => 'Gestion']);
        $student = User::factory()->create([
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.memoires.create'))
            ->post(route('admin.memoires.store'), [
                'titre' => 'Filière incohérente',
                'resume' => 'Résumé',
                'user_id' => $student->id,
                'filiere_id' => $autreFiliere->id,
                'annee_academique_id' => $annee->id,
                'fichier' => UploadedFile::fake()->create('memoire.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.memoires.create'))
            ->assertSessionHasErrors('filiere_id');

        $this->assertDatabaseMissing('memoires', ['titre' => 'Filière incohérente']);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_catalogue_search_includes_manual_author_names_for_admin_deposits(): void
    {
        $student = User::factory()->create(['role' => 'etudiant', 'actif' => true]);
        [$filiere, $annee] = $this->createAcademicCatalog();
        Memoire::create([
            'titre' => 'Archives de 2018',
            'resume' => 'Résumé',
            'auteur_nom' => 'Kouassi',
            'auteur_prenom' => 'Aminata',
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/archive.pdf',
            'statut' => 'valide',
        ]);

        Sanctum::actingAs($student);

        $this->getJson('/api/catalogue?auteur=Kouassi')
            ->assertOk()
            ->assertJsonPath('data.0.auteur_nom', 'Kouassi')
            ->assertJsonPath('data.0.auteur_prenom', 'Aminata')
            ->assertJsonPath('data.0.etudiant', null);
    }

    private function createAcademicCatalog(): array
    {
        return [
            Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']),
            AnneeAcademique::create(['libelle' => '2025-2026']),
        ];
    }
}
