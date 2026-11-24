<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentApiOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_stateful_api_otp_login_returns_json_and_authenticates_the_student(): void
    {
        Notification::fake();

        $student = $this->createStudent();

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login/otp', ['matricule' => $student->matricule])
            ->assertOk()
            ->assertJson(['message' => 'Un code de connexion vous a été envoyé.'])
            ->assertSessionHas('otp_matricule', $student->matricule);

        Notification::assertSentTo($student, OtpNotification::class);

        OtpCode::where('user_id', $student->id)->latest()->firstOrFail()->update([
            'code' => Hash::make('123456'),
        ]);

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login/verify', ['code' => '123456'])
            ->assertOk()
            ->assertJson(['message' => 'Connexion réussie.']);

        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $student->id);

        $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/logout')
            ->assertOk();

        Auth::forgetGuards();

        $this->withHeader('Origin', 'http://localhost')
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_stateless_api_otp_request_fails_explicitly_without_creating_a_code(): void
    {
        $student = $this->createStudent();

        $this->postJson('/api/login/otp', ['matricule' => $student->matricule])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('matricule');

        $this->assertDatabaseCount('otp_codes', 0);
    }

    private function createStudent(): User
    {
        return User::create([
            'nom' => 'Doe',
            'prenom' => 'Jane',
            'matricule' => '12345678',
            'email' => 'jane@example.com',
            'role' => 'etudiant',
            'actif' => true,
        ]);
    }
}
