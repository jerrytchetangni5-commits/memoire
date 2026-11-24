<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_only_the_current_api_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current');
        $otherToken = $user->createToken('other');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['message' => 'Déconnecté.']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);

        $this->app['auth']->forgetGuards();

        $this->withToken($currentToken->plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
