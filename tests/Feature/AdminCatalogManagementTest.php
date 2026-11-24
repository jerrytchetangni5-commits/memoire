<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_filiere(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.filieres.store'), [
            'code' => 'INFO',
            'nom' => 'Informatique',
        ])->assertRedirect(route('admin.filieres.index'));

        $filiere = Filiere::where('code', 'INFO')->firstOrFail();
        $this->assertDatabaseHas('filieres', ['id' => $filiere->id, 'nom' => 'Informatique']);

        $this->put(route('admin.filieres.update', $filiere), [
            'code' => 'INFO',
            'nom' => 'Informatique de gestion',
        ])->assertRedirect(route('admin.filieres.index'));

        $this->assertDatabaseHas('filieres', ['id' => $filiere->id, 'nom' => 'Informatique de gestion']);

        $this->delete(route('admin.filieres.destroy', $filiere))
            ->assertRedirect(route('admin.filieres.index'));

        $this->assertDatabaseMissing('filieres', ['id' => $filiere->id]);
    }

    public function test_admin_can_create_update_and_delete_an_academic_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.annees-academiques.store'), [
            'libelle' => '2025-2026',
        ])->assertRedirect(route('admin.annees-academiques.index'));

        $annee = AnneeAcademique::where('libelle', '2025-2026')->firstOrFail();

        $this->put(route('admin.annees-academiques.update', $annee), [
            'libelle' => '2026-2027',
        ])->assertRedirect(route('admin.annees-academiques.index'));

        $this->assertDatabaseHas('annees_academiques', [
            'id' => $annee->id,
            'libelle' => '2026-2027',
        ]);

        $this->delete(route('admin.annees-academiques.destroy', $annee))
            ->assertRedirect(route('admin.annees-academiques.index'));

        $this->assertDatabaseMissing('annees_academiques', ['id' => $annee->id]);
    }

    public function test_admin_cannot_delete_a_filiere_or_academic_year_in_use(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $filiere = Filiere::create(['code' => 'GEST', 'nom' => 'Gestion']);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $student = User::factory()->create([
            'role' => 'etudiant',
            'filiere_id' => $filiere->id,
        ]);
        Memoire::create([
            'titre' => 'Mémoire test',
            'resume' => 'Résumé de test',
            'user_id' => $student->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/test.pdf',
            'statut' => 'soumis',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.filieres.destroy', $filiere))
            ->assertRedirect(route('admin.filieres.index'))
            ->assertSessionHasErrors('delete');

        $this->delete(route('admin.annees-academiques.destroy', $annee))
            ->assertRedirect(route('admin.annees-academiques.index'))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('filieres', ['id' => $filiere->id]);
        $this->assertDatabaseHas('annees_academiques', ['id' => $annee->id]);
    }

    public function test_admin_catalog_rejects_duplicate_values_and_invalid_academic_year_format(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        AnneeAcademique::create(['libelle' => '2025-2026']);

        $this->actingAs($admin)
            ->post(route('admin.filieres.store'), ['code' => 'INFO', 'nom' => 'Autre'])
            ->assertSessionHasErrors('code');

        $this->post(route('admin.annees-academiques.store'), ['libelle' => '2025/2026'])
            ->assertSessionHasErrors('libelle');

        $this->post(route('admin.annees-academiques.store'), ['libelle' => '2025-2026'])
            ->assertSessionHasErrors('libelle');
    }
}
