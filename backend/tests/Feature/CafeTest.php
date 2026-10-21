<?php

namespace Tests\Feature;

use App\Models\Cafe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CafeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_tampil_semua_hanya_kafe_milik_sendiri(): void
    {
        Cafe::factory()->count(2)->for($this->user)->create();
        Cafe::factory()->create(); // milik user lain

        $this->getJson('/api/cafes')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'area', 'status', 'price_level', 'rating', 'work_friendly', 'facilities', 'color']]]);
    }

    public function test_tampil_semua_bisa_difilter(): void
    {
        Cafe::factory()->visited()->for($this->user)->create(['work_friendly' => true]);
        Cafe::factory()->for($this->user)->create(['work_friendly' => false]);

        $this->getJson('/api/cafes?status=visited')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/cafes?work_friendly=1')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_tambah_kafe_mengembalikan_201(): void
    {
        $this->postJson('/api/cafes', [
            'name' => 'Ruang Seduh',
            'area' => 'Seturan',
            'status' => 'visited',
            'price_level' => 2,
            'rating' => 5,
            'work_friendly' => true,
            'facilities' => ['wifi', 'colokan'],
            'color' => 'pink',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Ruang Seduh')
            ->assertJsonPath('data.work_friendly', true)
            ->assertJsonPath('data.facilities', ['wifi', 'colokan']);

        $this->assertDatabaseHas('cafes', ['user_id' => $this->user->id, 'name' => 'Ruang Seduh']);
    }

    public function test_tambah_kafe_tidak_valid_mengembalikan_422(): void
    {
        $this->postJson('/api/cafes', [
            'name' => '',
            'rating' => 9,
            'facilities' => ['kolam renang'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'area', 'rating', 'facilities.0']);
    }

    public function test_tampil_satu_kafe_mengembalikan_200(): void
    {
        $cafe = Cafe::factory()->for($this->user)->create(['name' => 'Teras Pagi']);

        $this->getJson("/api/cafes/{$cafe->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Teras Pagi');
    }

    public function test_kafe_milik_orang_lain_atau_tidak_ada_mengembalikan_404(): void
    {
        $lain = Cafe::factory()->create();

        $this->getJson("/api/cafes/{$lain->id}")->assertNotFound();
        $this->deleteJson("/api/cafes/{$lain->id}")->assertNotFound();
        $this->getJson('/api/cafes/99999')->assertNotFound();
    }

    public function test_ubah_kafe_mengembalikan_200(): void
    {
        $cafe = Cafe::factory()->for($this->user)->create(['status' => 'wishlist']);

        $this->patchJson("/api/cafes/{$cafe->id}", ['status' => 'visited', 'rating' => 4])
            ->assertOk()
            ->assertJsonPath('data.status', 'visited')
            ->assertJsonPath('data.rating', 4);
    }

    public function test_hapus_kafe_mengembalikan_200(): void
    {
        $cafe = Cafe::factory()->for($this->user)->create();

        $this->deleteJson("/api/cafes/{$cafe->id}")->assertOk();
        $this->assertDatabaseMissing('cafes', ['id' => $cafe->id]);
    }
}