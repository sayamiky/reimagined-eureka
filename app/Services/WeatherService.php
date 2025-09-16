<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweathermap.key') ?? env('OPENWEATHERMAP_KEY');
        $this->baseUrl = 'https://api.openweathermap.org/data/3.0/onecall';
        // https: //api.openweathermap.org/data/3.0/onecall?lat={lat}&lon={lon}&exclude={part}&appid={API key}
    }

    public function getCurrentWeatherForPerth()
    {
        $cacheKey = 'weather:perth:current';

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                    'lat' => -31.951399,
                    'lon' => 115.861678,
                    'execlude' => 'minutely,hourly,daily,alerts',
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                // $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                //     'q' => 'Perth,AU',
                //     'appid' => $this->apiKey,
                //     'units' => 'metric'
                // ]);

                if ($response->failed()) {
                    Log::error('OpenWeatherMap failed', ['status' => $response->status(), 'body' => $response->body()]);
                    throw new \Exception('Failed to fetch weather');
                }

                return $response->json();
            } catch (\Throwable $e) {
                
                if (Cache::has('weather:perth:current')) {
                    return Cache::get('weather:perth:current');
                }
                throw $e;
            }
        });
    }

    // Manual refresh used by scheduled job
    public function refreshWeatherForPerth()
    {
        $cacheKey = 'weather:perth:current';
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/weather", [
                    'lat' => -31.951399,
                    'lon' => 115.861678,
                    'execlude' => 'minutely,hourly,daily,alerts',
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch weather');
            }
            Cache::put($cacheKey, $response->json(), now()->addMinutes(15));
            return true;
        } catch (\Throwable $e) {
            Log::error('Weather refresh failed: ' . $e->getMessage());
            return false;
        }
    }
}
