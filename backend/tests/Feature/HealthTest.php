<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_sehat_mengembalikan_200(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_basis_data_mati_mengembalikan_503(): void
    {
        // Arahkan koneksi ke database yang tidak ada → seolah-olah DB mati
        $connection = config('database.default');
        config(["database.connections.{$connection}.database" => '/tidak/ada/database.sqlite']);
        DB::purge($connection);

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'fail')
            ->assertJsonPath('checks.cache.status', 'ok');
    }
}