<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;
use App\Http\Middleware\RecordMetrics;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // /health didaftarkan di luar grup "web" → tanpa session/cookie
        then: function () {
            Route::get('/health', HealthController::class)->name('health');
            // /metrics ditarik Prometheus tiap 15 detik (format teks Prometheus)
            Route::get('/metrics', MetricsController::class)->name('metrics');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di belakang Nginx/Docker, percayai header X-Forwarded-*
        $middleware->trustProxies(at: '*');

        // Request API tanpa token → jangan redirect ke halaman login web (balas 401 JSON)
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');

        // Catat setiap request ke metrik Prometheus (jumlah request + lama request)
        $middleware->append(RecordMetrics::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua error di /api/* selalu dibalas JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // Gagal menghubungi / query basis data → log terstruktur sendiri.
        // ->stop() mencegah log bawaan Laravel yang ikut menulis SQL + bindings
        // (bindings bisa berisi email saat login).
        $exceptions->report(function (QueryException $e) {
            $request = request();
            $guard = auth('sanctum');

            Log::error('Gagal menghubungi basis data', [
                'event' => 'db.query_failed',
                'actor' => $guard->hasUser() ? 'user:'.$guard->id() : 'unknown',
                'action' => 'memproses request API',
                'endpoint' => $request->method().' /'.$request->path(),
                'error_code' => $e->getCode(),
                'reason' => 'basis data tidak dapat dihubungi atau query gagal',
            ]);
        })->stop();

        // 503 – basis data tidak tersedia
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Layanan sedang bermasalah, coba lagi nanti.'], 503);
            }
        });

        // 401 – token tidak ada / tidak valid
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Belum login atau token tidak valid.'], 401);
            }
        });

        // 404 – data / endpoint tidak ada
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }
        });
    })->create();