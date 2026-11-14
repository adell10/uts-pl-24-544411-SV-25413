<?php

namespace Tests\Feature;

use Tests\TestCase;

class MetricsTest extends TestCase
{
    public function test_metrics_mencatat_request_per_route(): void
    {
        // Satu request biasa supaya ada yang tercatat
        $this->getJson('/api/cafes')->assertStatus(401);

        $response = $this->get('/metrics');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'cafehopper_http_requests_total{method="GET",route="api/cafes",status="401"} 1',
            $response->getContent()
        );
        $this->assertStringContainsString('cafehopper_http_request_duration_seconds_bucket', $response->getContent());
    }
}
