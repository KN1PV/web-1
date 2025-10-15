<?php

namespace App\Services;

use App\Models\WeatherData;

class WeatherTransformer
{
    public function transform(WeatherData $weatherData, array $filters = []): array
    {
        $data = $weatherData->toArray();

        // Apply filters
        if (isset($filters['fields'])) {
            $fields = explode(',', $filters['fields']);
            $data = array_intersect_key($data, array_flip($fields));
        }

        if (isset($filters['temperature_unit']) && $filters['temperature_unit'] === 'fahrenheit') {
            if (isset($data['temperature'])) {
                $data['temperature'] = round(($data['temperature'] * 9/5) + 32, 1);
            }
            if (isset($data['feels_like'])) {
                $data['feels_like'] = round(($data['feels_like'] * 9/5) + 32, 1);
            }
        }

        if (isset($filters['wind_unit']) && $filters['wind_unit'] === 'mph') {
            if (isset($data['wind_speed'])) {
                $data['wind_speed'] = round($data['wind_speed'] * 2.237, 1);
            }
        }

        if (isset($filters['pressure_unit']) && $filters['pressure_unit'] === 'inHg') {
            if (isset($data['pressure'])) {
                $data['pressure'] = round($data['pressure'] * 0.02953, 2);
            }
        }

        // Add computed fields
        if (isset($filters['include_computed'])) {
            $data['temperature_category'] = $this->getTemperatureCategory($data['temperature'] ?? 0);
            $data['humidity_category'] = $this->getHumidityCategory($data['humidity'] ?? 0);
            $data['wind_category'] = $this->getWindCategory($data['wind_speed'] ?? 0);
        }

        return $data;
    }

    private function getTemperatureCategory(float $temperature): string
    {
        if ($temperature < 0) return 'freezing';
        if ($temperature < 10) return 'cold';
        if ($temperature < 20) return 'cool';
        if ($temperature < 30) return 'warm';
        return 'hot';
    }

    private function getHumidityCategory(int $humidity): string
    {
        if ($humidity < 30) return 'dry';
        if ($humidity < 60) return 'comfortable';
        return 'humid';
    }

    private function getWindCategory(float $windSpeed): string
    {
        if ($windSpeed < 5) return 'calm';
        if ($windSpeed < 15) return 'light';
        if ($windSpeed < 25) return 'moderate';
        return 'strong';
    }
}

