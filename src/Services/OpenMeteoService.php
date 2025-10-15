<?php

namespace App\Services;

use App\Config\Config;
use App\Models\WeatherData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class OpenMeteoService
{
    private Client $httpClient;
    private Logger $logger;
    private Config $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->httpClient = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
        
        $this->logger = new Logger('openmeteo_api');
        $this->logger->pushHandler(new StreamHandler($this->config->get('log_file'), Logger::INFO));
    }

    /**
     * Get current weather by city name
     * @throws GuzzleException
     */
    public function getCurrentWeather(string $city, string $country = ''): WeatherData
    {
        try {
            // First, get coordinates for the city
            $coordinates = $this->getCityCoordinates($city, $country);
            
            // Then get weather data for those coordinates
            return $this->getWeatherByCoordinates($coordinates['lat'], $coordinates['lon'], $city, $country);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get current weather', [
                'city' => $city,
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get weather by coordinates
     * @throws GuzzleException
     */
    public function getWeatherByCoordinates(float $lat, float $lon, string $cityName = '', string $country = ''): WeatherData
    {
        $url = $this->config->get('openmeteo_base_url') . '/forecast';
        $params = [
            'latitude' => $lat,
            'longitude' => $lon,
            'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,pressure_msl,wind_speed_10m,weather_code',
            'timezone' => 'auto'
        ];

        try {
            $response = $this->httpClient->get($url, ['query' => $params]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed: ' . ($data['error'] ?? 'Unknown error'));
            }

            $this->logger->info('Open-Meteo weather data retrieved successfully', [
                'lat' => $lat,
                'lon' => $lon,
                'city' => $cityName
            ]);
            
            return WeatherData::fromOpenMeteoData($data, $cityName ?: 'Unknown City', $country ?: 'Unknown');
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch Open-Meteo weather data', [
                'lat' => $lat,
                'lon' => $lon,
                'city' => $cityName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get weather forecast for multiple days
     * @throws GuzzleException
     */
    public function getWeatherForecast(string $city, string $country = '', int $days = 5): array
    {
        try {
            // Get coordinates for the city
            $coordinates = $this->getCityCoordinates($city, $country);
            
            $url = $this->config->get('openmeteo_base_url') . '/forecast';
            $params = [
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lon'],
                'daily' => 'temperature_2m_max,temperature_2m_min,weather_code',
                'timezone' => 'auto',
                'forecast_days' => $days
            ];

            $response = $this->httpClient->get($url, ['query' => $params]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API request failed: ' . ($data['error'] ?? 'Unknown error'));
            }

            $this->logger->info('Open-Meteo forecast retrieved successfully', [
                'city' => $city,
                'country' => $country,
                'days' => $days
            ]);
            
            return $this->transformForecastData($data, $days, $coordinates['name'], $coordinates['country']);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch Open-Meteo forecast', [
                'city' => $city,
                'country' => $country,
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getCityCoordinates(string $city, string $country = ''): array
    {
        $query = $city;
        if (!empty($country)) {
            $query .= ', ' . $country;
        }

        $url = 'https://geocoding-api.open-meteo.com/v1/search';
        $params = [
            'name' => $query,
            'count' => 1,
            'language' => 'en',
            'format' => 'json'
        ];

        $response = $this->httpClient->get($url, ['query' => $params]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (empty($data['results'])) {
            throw new \Exception("City '{$city}' not found");
        }

        $result = $data['results'][0];
        return [
            'lat' => $result['latitude'],
            'lon' => $result['longitude'],
            'name' => $result['name'] ?? $city,
            'country' => $result['country'] ?? $country
        ];
    }


    private function transformForecastData(array $data, int $days, string $cityName, string $country): array
    {
        $daily = $data['daily'];
        $forecastData = [];

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

        for ($i = 0; $i < $days; $i++) {
            $date = $daily['time'][$i];
            $weatherCode = $daily['weather_code'][$i];
            
            $forecastData[$date] = [
                'date' => $date,
                'min_temp' => round($daily['temperature_2m_min'][$i], 1),
                'max_temp' => round($daily['temperature_2m_max'][$i], 1),
                'avg_humidity' => 70, // Default value since not available in daily forecast
                'avg_pressure' => 1013, // Default value since not available in daily forecast
                'main_condition' => $descriptions[$weatherCode] ?? 'unknown',
                'conditions' => [$descriptions[$weatherCode] ?? 'unknown'],
                'forecasts_count' => 1
            ];
        }

        return $forecastData;
    }

    private function getWeatherDescription(int $weatherCode): string
    {
        $descriptions = [
            0 => 'clear sky',
            1 => 'mainly clear',
            2 => 'partly cloudy',
            3 => 'overcast',
            45 => 'fog',
            48 => 'depositing rime fog',
            51 => 'light drizzle',
            53 => 'moderate drizzle',
            55 => 'dense drizzle',
            56 => 'light freezing drizzle',
            57 => 'dense freezing drizzle',
            61 => 'slight rain',
            63 => 'moderate rain',
            65 => 'heavy rain',
            66 => 'light freezing rain',
            67 => 'heavy freezing rain',
            71 => 'slight snow fall',
            73 => 'moderate snow fall',
            75 => 'heavy snow fall',
            77 => 'snow grains',
            80 => 'slight rain showers',
            81 => 'moderate rain showers',
            82 => 'violent rain showers',
            85 => 'slight snow showers',
            86 => 'heavy snow showers',
            95 => 'thunderstorm',
            96 => 'thunderstorm with slight hail',
            99 => 'thunderstorm with heavy hail'
        ];

        return $descriptions[$weatherCode] ?? 'unknown';
    }

    private function getWeatherIcon(int $weatherCode): string
    {
        $icons = [
            0 => '01d',
            1 => '02d',
            2 => '03d',
            3 => '04d',
            45 => '50d',
            48 => '50d',
            51 => '09d',
            53 => '09d',
            55 => '09d',
            56 => '13d',
            57 => '13d',
            61 => '10d',
            63 => '10d',
            65 => '10d',
            66 => '13d',
            67 => '13d',
            71 => '13d',
            73 => '13d',
            75 => '13d',
            77 => '13d',
            80 => '09d',
            81 => '09d',
            82 => '09d',
            85 => '13d',
            86 => '13d',
            95 => '11d',
            96 => '11d',
            99 => '11d'
        ];

        return $icons[$weatherCode] ?? '01d';
    }
}
