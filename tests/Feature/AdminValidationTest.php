<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use App\Notifications\MemoireRejeteNotification;
use App\Notifications\MemoireValideNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_validation_queue_lists_only_waiting_memoires_and_can_filter_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $student = $this->createStudent($filiere);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);

        $soumis = $this->createMemoire($student, $filiere, $annee, 'soumis', 'Dossier soumis');
        $this->createMemoire($student, $filiere, $annee, 'en_verification', 'Dossier en cours');
        $this->createMemoire($student, $filiere, $annee, 'valide', 'Dossier déjà validé');

        $this->actingAs($admin)
            ->get(route('admin.validations.index'))
            ->assertOk()
            ->assertViewHas('memoires', fn ($memoires): bool => $memoires->total() === 2)
            ->assertSeeText('Dossier soumis')
            ->assertDontSeeText('Dossier déjà validé');

        $this->get(route('admin.validations.index', ['statut' => 'soumis']))
            ->assertViewHas('memoires', fn ($memoires): bool => $memoires->total() === 1)
            ->assertSeeText($soumis->titre);
    }

    public function test_responsable_only_sees_and_opens_memoires_from_their_filiere(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $autreFiliere = Filiere::create(['code' => 'GEST', 'nom' => 'Gestion']);
        $responsable = User::factory()->create([
            'role' => 'responsable',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
        $student = $this->createStudent($filiere);
        $autreStudent = $this->createStudent($autreFiliere, '87654321');
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $visibleMemoire = $this->createMemoire($student, $filiere, $annee, 'soumis', 'Dossier de ma filière');
        $hiddenMemoire = $this->createMemoire($autreStudent, $autreFiliere, $annee, 'soumis', 'Dossier hors filière');

        $this->actingAs($responsable)
            ->get(route('admin.validations.index'))
            ->assertOk()
            ->assertViewHas('memoires', fn ($memoires): bool => $memoires->total() === 1)
            ->assertSeeText($visibleMemoire->titre)
            ->assertDontSeeText($hiddenMemoire->titre);

        $this->get(route('admin.validations.show', $hiddenMemoire))->assertForbidden();
    }

    public function test_admin_can_mark_and_validate_a_memoire_and_the_student_is_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $student = $this->createStudent($filiere);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $memoire = $this->createMemoire($student, $filiere, $annee, 'soumis');

        $this->actingAs($admin)
            ->patch(route('admin.validations.en-verification', $memoire))
            ->assertRedirect(route('admin.validations.show', $memoire));

        $this->assertDatabaseHas('memoires', ['id' => $memoire->id, 'statut' => 'en_verification']);

        $this->patch(route('admin.validations.valider', $memoire))
            ->assertRedirect(route('admin.validations.index'));

        $this->assertDatabaseHas('memoires', [
            'id' => $memoire->id,
            'statut' => 'valide',
            'valide_par' => $admin->id,
        ]);
        $this->assertNotNull($memoire->fresh()->date_validation);
        Notification::assertSentTo($student, MemoireValideNotification::class);
    }

    public function test_rejection_requires_a_reason_and_notifies_the_student(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'actif' => true]);
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $student = $this->createStudent($filiere);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $memoire = $this->createMemoire($student, $filiere, $annee, 'soumis');

        $this->actingAs($admin)
            ->from(route('admin.validations.show', $memoire))
            ->patch(route('admin.validations.rejeter', $memoire), ['motif_rejet' => ''])
            ->assertRedirect(route('admin.validations.show', $memoire))
            ->assertSessionHasErrors('motif_rejet');

        $this->patch(route('admin.validations.rejeter', $memoire), [
            'motif_rejet' => 'Le document déposé ne correspond pas à la version papier.',
        ])->assertRedirect(route('admin.validations.index'));

        $this->assertDatabaseHas('memoires', [
            'id' => $memoire->id,
            'statut' => 'rejete',
            'motif_rejet' => 'Le document déposé ne correspond pas à la version papier.',
            'valide_par' => $admin->id,
        ]);
        Notification::assertSentTo($student, MemoireRejeteNotification::class);
    }

    public function test_responsable_can_view_pending_pdf_and_api_validation_uses_the_same_rules(): void
    {
        Notification::fake();
        Storage::fake('local');
        Storage::disk('local')->put('memoires/test.pdf', '%PDF-1.4 test');

        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $responsable = User::factory()->create([
            'role' => 'responsable',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
        $student = $this->createStudent($filiere);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $memoire = $this->createMemoire($student, $filiere, $annee, 'soumis');

        $this->actingAs($responsable)
            ->get(route('admin.validations.fichier', $memoire))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        Sanctum::actingAs($responsable);
        $this->patchJson("/api/validation/memoires/{$memoire->id}/valider")
            ->assertOk()
            ->assertJsonPath('memoire.statut', 'valide');

        Notification::assertSentTo($student, MemoireValideNotification::class);
    }

    public function test_responsable_without_a_filiere_cannot_access_the_validation_queue(): void
    {
        $responsable = User::factory()->create([
            'role' => 'responsable',
            'actif' => true,
        ]);

        $this->actingAs($responsable)
            ->get(route('admin.validations.index'))
            ->assertForbidden();
    }

    private function createStudent(Filiere $filiere, string $matricule = '12345678'): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'matricule' => $matricule,
            'email' => $matricule.'@example.com',
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
    }

    private function createMemoire(
        User $student,
        Filiere $filiere,
        AnneeAcademique $annee,
        string $statut,
        ?string $titre = null,
    ): Memoire {
        return Memoire::create([
            'titre' => $titre ?? 'Mémoire '.$statut,
            'resume' => 'Résumé de test',
            'user_id' => $student->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/test.pdf',
            'statut' => $statut,
        ]);
    }
}
