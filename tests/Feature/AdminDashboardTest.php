<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_memoire_stats_and_filiere_chart_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $informatique = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $gestion = Filiere::create(['code' => 'GEST', 'nom' => 'Gestion']);
        $student = User::factory()->create([
            'role' => 'etudiant',
            'filiere_id' => $informatique->id,
        ]);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);

        $this->createMemoire($student, $informatique, $annee, 'soumis');
        $this->createMemoire($student, $informatique, $annee, 'valide');
        $this->createMemoire($student, $gestion, $annee, 'en_verification');
        $this->createMemoire($student, $gestion, $annee, 'rejete');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Déconnexion')
            ->assertSee(route('admin.memoires.create'))
            ->assertSee(route('admin.utilisateurs.index'))
            ->assertViewHas('stats', fn (array $stats): bool => $stats === [
                'total' => 4,
                'a_traiter' => 2,
                'valides' => 1,
                'rejetes' => 1,
                'utilisateurs_actifs' => 2,
            ])
            ->assertViewHas('chartData', fn (array $chartData): bool => $chartData === [
                'labels' => ['Gestion', 'Informatique'],
                'values' => [2, 2],
            ]);
    }

    public function test_responsable_navigation_does_not_show_admin_only_pages(): void
    {
        $responsable = User::factory()->create(['role' => 'responsable']);

        $this->actingAs($responsable)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Filières')
            ->assertSeeText('Validations')
            ->assertDontSee(route('admin.memoires.create'))
            ->assertDontSee(route('admin.utilisateurs.index'));
    }

    public function test_admin_dashboard_handles_an_empty_catalogue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats === [
                'total' => 0,
                'a_traiter' => 0,
                'valides' => 0,
                'rejetes' => 0,
                'utilisateurs_actifs' => 1,
            ])
            ->assertViewHas('chartData', fn (array $chartData): bool => $chartData === [
                'labels' => [],
                'values' => [],
            ])
            ->assertSeeText('Aucune filière n’est encore enregistrée.');
    }

    private function createMemoire(
        User $student,
        Filiere $filiere,
        AnneeAcademique $annee,
        string $statut,
    ): void {
        Memoire::create([
            'titre' => 'Mémoire '.$statut,
            'resume' => 'Résumé de test',
            'user_id' => $student->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/test.pdf',
            'statut' => $statut,
        ]);
    }
}
