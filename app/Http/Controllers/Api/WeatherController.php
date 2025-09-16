<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected $weather;

    public function __construct(WeatherService $weather)
    {
        $this->weather = $weather;
    }

    public function current()
    {
        try {
            $data = $this->weather->getCurrentWeatherForPerth();
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unable to fetch weather data',
                'error' => $e->getMessage()
            ], 503);
        }
    }
}
