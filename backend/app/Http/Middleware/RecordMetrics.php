<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Mencatat tiap request HTTP ke metrik Prometheus:
 * - cafehopper_http_requests_total{method, route, status}       (counter)
 * - cafehopper_http_request_duration_seconds{method, route}     (histogram)
 *
 * Label "route" memakai POLA route (mis. api/cafes/{cafe}), bukan URL lengkap,
 * supaya jumlah deret waktu tetap kecil.
 */
class RecordMetrics
{
    public function __construct(private CollectorRegistry $registry)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);

        $response = $next($request);

        try {
            $route = $request->route()?->uri() ?? 'tidak_dikenal';

            // Permintaan Prometheus ke /metrics tidak ikut dihitung
            if ($route !== 'metrics') {
                $method = $request->method();

                $this->registry
                    ->getOrRegisterCounter('cafehopper', 'http_requests_total', 'Jumlah request HTTP yang dilayani backend', ['method', 'route', 'status'])
                    ->inc([$method, $route, (string) $response->getStatusCode()]);

                $this->registry
                    ->getOrRegisterHistogram('cafehopper', 'http_request_duration_seconds', 'Lama request HTTP dalam detik', ['method', 'route'], [0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5])
                    ->observe((hrtime(true) - $start) / 1e9, [$method, $route]);
            }
        } catch (Throwable) {
            // Gagal mencatat metrik tidak boleh membuat request pengguna gagal
        }

        return $response;
    }
}
