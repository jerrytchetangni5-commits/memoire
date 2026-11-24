<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_an_email_login_code(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'role' => 'etudiant',
            'matricule' => '12345678',
            'password' => null,
        ]);

        $response = $this->post('/connexion', [
            'matricule' => $user->matricule,
        ]);

        $response->assertRedirect(route('login.otp'));
        $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id]);
        Notification::assertSentTo($user, OtpNotification::class);
    }

    public function test_student_can_authenticate_with_a_valid_email_code(): void
    {
        $user = User::factory()->create([
            'role' => 'etudiant',
            'matricule' => '12345678',
            'password' => null,
        ]);
        OtpCode::create([
            'user_id' => $user->id,
            'code' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->withSession(['otp_matricule' => $user->matricule])
            ->post('/connexion/code', ['code' => '123456']);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_student_cannot_authenticate_with_an_invalid_email_code(): void
    {
        $user = User::factory()->create([
            'role' => 'etudiant',
            'matricule' => '12345678',
            'password' => null,
        ]);

        $this->withSession(['otp_matricule' => $user->matricule])
            ->from('/connexion/code')
            ->post('/connexion/code', ['code' => '000000'])
            ->assertRedirect('/connexion/code')
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/deconnexion');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
