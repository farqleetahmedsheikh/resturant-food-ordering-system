<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    public function test_health_endpoint_returns_safe_public_payload(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'application' => 'Arcade Kebab House',
            ])
            ->assertDontSee('PHP')
            ->assertDontSee('database')
            ->assertDontSee('APP_KEY');
    }
}
