<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * GET /health → benar-benar memeriksa basis data dan cache.
 * 200 = healthy, 503 = unhealthy (disebutkan bagian mana yang bermasalah).
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        // Bagian yang statusnya bukan "ok"
        $failing = array_keys(array_filter($checks, fn (array $check) => $check['status'] !== 'ok'));
        $healthy = $failing === [];

        if (! $healthy) {
            Log::error('Health check gagal', [
                'event' => 'health.unhealthy',
                'actor' => 'system:healthcheck',
                'action' => 'memeriksa kesehatan layanan',
                'endpoint' => 'GET /health',
                'failing' => $failing,
                'reason' => array_map(fn (string $part) => $checks[$part]['error'], array_combine($failing, $failing)),
            ]);
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'checked_at' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    /** Buka koneksi lalu jalankan query sungguhan ke basis data */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::select('select 1');

            return ['status' => 'ok', 'latency_ms' => $this->elapsed($start)];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'latency_ms' => $this->elapsed($start),
                'error' => 'Tidak dapat terhubung ke basis data',
                'code' => $e->getCode(),
            ];
        }
    }

    /** Tulis, baca, lalu hapus satu nilai di cache */
    private function checkCache(): array
    {
        $start = microtime(true);

        try {
            $key = 'health:'.Str::random(12);
            Cache::put($key, 'ok', 10);
            $readBack = Cache::get($key);
            Cache::forget($key);

            if ($readBack !== 'ok') {
                return ['status' => 'fail', 'latency_ms' => $this->elapsed($start), 'error' => 'Nilai cache tidak terbaca kembali'];
            }

            return ['status' => 'ok', 'latency_ms' => $this->elapsed($start)];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'latency_ms' => $this->elapsed($start),
                'error' => 'Cache tidak dapat diakses',
                'code' => $e->getCode(),
            ];
        }
    }

    private function elapsed(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}