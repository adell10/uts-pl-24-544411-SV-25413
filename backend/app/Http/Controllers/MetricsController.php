<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

/** GET /metrics → metrik dalam format teks Prometheus */
class MetricsController extends Controller
{
    public function __invoke(CollectorRegistry $registry): Response
    {
        $body = (new RenderTextFormat())->render($registry->getMetricFamilySamples());

        return response($body, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
    }
}
