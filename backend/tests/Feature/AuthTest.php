<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_200_dan_token(): void
    {
        User::factory()->create(['email' => 'hailo@cafehopper.test', 'password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => 'hailo@cafehopper.test',
            'password' => 'password123',
        ])->assertStatus(500)
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token']);
    }

    public function test_login_password_salah_mengembalikan_401(): void
    {
        User::factory()->create(['email' => 'hailo@cafehopper.test', 'password' => 'password123']);

        $this->postJson('/api/login', [
            'email' => 'hailo@cafehopper.test',
            'password' => 'salah-banget',
        ])->assertUnauthorized()
            ->assertJson(['message' => 'Email atau password salah.']);
    }

    public function test_login_tanpa_isian_mengembalikan_422(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_endpoint_tanpa_token_mengembalikan_401(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/cafes')->assertUnauthorized();
        $this->postJson('/api/cafes', ['name' => 'X', 'area' => 'Y'])->assertUnauthorized();
    }

    public function test_tidak_ada_endpoint_register(): void
    {
        // Sesuai soal: selain login, semua endpoint wajib token
        $this->postJson('/api/register', [
            'name' => 'A', 'email' => 'a@a.test', 'password' => 'password123',
        ])->assertStatus(404);
    }

    public function test_logout_menghapus_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}