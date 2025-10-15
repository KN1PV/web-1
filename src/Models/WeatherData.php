<?php

namespace App\Models;

class WeatherData
{
    public function __construct(
        public readonly string $city,
        public readonly string $country,
        public readonly float $temperature,
        public readonly float $feelsLike,
        public readonly int $humidity,
        public readonly float $pressure,
        public readonly float $windSpeed,
        public readonly string $description,
        public readonly string $icon,
        public readonly int $timestamp
    ) {}

    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'country' => $this->country,
            'temperature' => $this->temperature,
            'feels_like' => $this->feelsLike,
            'humidity' => $this->humidity,
            'pressure' => $this->pressure,
            'wind_speed' => $this->windSpeed,
            'description' => $this->description,
            'icon' => $this->icon,
            'timestamp' => $this->timestamp,
            'formatted_time' => date('Y-m-d H:i:s', $this->timestamp),
        ];
    }

    public static function fromOpenWeatherData(array $data): self
    {
        return new self(
            city: $data['name'],
            country: $data['sys']['country'],
            temperature: round($data['main']['temp'] - 273.15, 1), // Convert from Kelvin to Celsius
            feelsLike: round($data['main']['feels_like'] - 273.15, 1),
            humidity: $data['main']['humidity'],
            pressure: $data['main']['pressure'],
            windSpeed: $data['wind']['speed'] ?? 0,
            description: $data['weather'][0]['description'],
            icon: $data['weather'][0]['icon'],
            timestamp: $data['dt']
        );
    }

    public static function fromOpenMeteoData(array $data, string $cityName, string $country): self
    {
        $current = $data['current'];
        
        // Weather code descriptions
        $descriptions = [
            0 => 'clear sky', 1 => 'mainly clear', 2 => 'partly cloudy', 3 => 'overcast',
            45 => 'fog', 48 => 'depositing rime fog', 51 => 'light drizzle', 53 => 'moderate drizzle',
            55 => 'dense drizzle', 56 => 'light freezing drizzle', 57 => 'dense freezing drizzle',
            61 => 'slight rain', 63 => 'moderate rain', 65 => 'heavy rain',
            66 => 'light freezing rain', 67 => 'heavy freezing rain', 71 => 'slight snow fall',
            73 => 'moderate snow fall', 75 => 'heavy snow fall', 77 => 'snow grains',
            80 => 'slight rain showers', 81 => 'moderate rain showers', 82 => 'violent rain showers',
            85 => 'slight snow showers', 86 => 'heavy snow showers', 95 => 'thunderstorm',
            96 => 'thunderstorm with slight hail', 99 => 'thunderstorm with heavy hail'
        ];
        
        // Weather code icons
        $icons = [
            0 => '01d', 1 => '02d', 2 => '03d', 3 => '04d', 45 => '50d', 48 => '50d',
            51 => '09d', 53 => '09d', 55 => '09d', 56 => '13d', 57 => '13d',
            61 => '10d', 63 => '10d', 65 => '10d', 66 => '13d', 67 => '13d',
            71 => '13d', 73 => '13d', 75 => '13d', 77 => '13d', 80 => '09d',
            81 => '09d', 82 => '09d', 85 => '13d', 86 => '13d', 95 => '11d',
            96 => '11d', 99 => '11d'
        ];
        
        $weatherCode = $current['weather_code'];
        
        return new self(
            city: $cityName,
            country: $country,
            temperature: round($current['temperature_2m'], 1),
            feelsLike: round($current['apparent_temperature'], 1),
            humidity: $current['relative_humidity_2m'],
            pressure: round($current['pressure_msl'], 2),
            windSpeed: round($current['wind_speed_10m'], 1),
            description: $descriptions[$weatherCode] ?? 'unknown',
            icon: $icons[$weatherCode] ?? '01d',
            timestamp: time()
        );
    }
}
