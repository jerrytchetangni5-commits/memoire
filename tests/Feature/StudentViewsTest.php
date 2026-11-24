<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_pages_render_with_their_required_routes_and_data(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $annee = AnneeAcademique::create(['libelle' => '2025-2026']);
        $student = $this->createUser('etudiant', $filiere, 'student@example.com');

        $memoire = Memoire::create([
            'titre' => 'Mémoire accessible au catalogue',
            'resume' => 'Résumé de démonstration',
            'user_id' => $student->id,
            'filiere_id' => $filiere->id,
            'annee_academique_id' => $annee->id,
            'fichier' => 'memoires/demo.pdf',
            'statut' => 'valide',
        ]);

        $this->actingAs($student)
            ->get('/')
            ->assertRedirect(route('etudiant.catalogue.index'));

        $this->get('/catalogue')
            ->assertOk()
            ->assertSee('Catalogue des mémoires')
            ->assertSee($memoire->titre);

        $this->get("/catalogue/{$memoire->id}")
            ->assertOk()
            ->assertSee($memoire->resume);

        $this->get('/mes-depots/nouveau')
            ->assertOk()
            ->assertSee('Déposer un mémoire');

        $this->get('/mes-depots')->assertOk();
        $this->get('/favoris')->assertOk();
        $this->get('/historique')->assertOk();
    }

    public function test_non_student_cannot_open_student_pages(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $responsable = $this->createUser('responsable', $filiere, 'responsable@example.com');

        $this->actingAs($responsable)
            ->get('/mes-depots')
            ->assertForbidden();
    }

    public function test_student_is_greeted_by_first_name_on_the_first_page_after_otp_login(): void
    {
        $filiere = Filiere::create(['code' => 'INFO', 'nom' => 'Informatique']);
        $student = $this->createUser('etudiant', $filiere, 'student@example.com');
        OtpCode::create([
            'user_id' => $student->id,
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->withSession(['otp_matricule' => $student->matricule])
            ->post('/connexion/code', ['code' => '123456'])
            ->assertRedirect(route('home'));

        $this->get('/')
            ->assertRedirect(route('etudiant.catalogue.index'));

        $this->get('/catalogue')
            ->assertOk()
            ->assertSee('Salut, Jane !');

        $this->get('/mes-depots')
            ->assertOk()
            ->assertDontSee('Salut, Jane !');
    }

    private function createUser(string $role, Filiere $filiere, string $email): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'matricule' => $role === 'etudiant' ? '12345678' : null,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'filiere_id' => $filiere->id,
            'actif' => true,
        ]);
    }
}
