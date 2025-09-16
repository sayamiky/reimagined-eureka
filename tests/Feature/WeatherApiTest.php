<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_fetch_weather_data()
    {
        $response = $this->getJson('/api/weather?city=London');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'main' => [
                        'temp',
                    ],
                ],
            ]);
    }
}
