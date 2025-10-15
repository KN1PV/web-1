<?php

namespace App\Controllers;

use App\Services\WeatherApiService;
use App\Services\OpenMeteoService;
use App\Services\WeatherTransformer;
use App\Config\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class WeatherController
{
    private WeatherApiService $weatherService;
    private OpenMeteoService $openMeteoService;
    private WeatherTransformer $transformer;
    private Logger $logger;
    private Config $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->weatherService = new WeatherApiService();
        $this->openMeteoService = new OpenMeteoService();
        $this->transformer = new WeatherTransformer();
        
        $this->logger = new Logger('weather_controller');
        $this->logger->pushHandler(new StreamHandler($this->config->get('log_file'), Logger::INFO));
    }

    public function handleRequest(): void
    {
        try {
            // Clear any previous output
            if (ob_get_level()) {
                ob_clean();
            }
            
            $this->setHeaders();
            
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            if ($method === 'GET' && $path === '/weather') {
                $this->getWeather();
            } elseif ($method === 'GET' && $path === '/weather/coordinates') {
                $this->getWeatherByCoordinates();
            } elseif ($method === 'GET' && $path === '/weather/forecast') {
                $this->getWeatherForecast();
            } else {
                $this->sendErrorResponse('Endpoint not found', 404);
            }
        } catch (\Exception $e) {
            $this->logger->error('Controller error', ['error' => $e->getMessage()]);
            $this->sendErrorResponse('Internal server error: ' . $e->getMessage(), 500);
        } catch (\Error $e) {
            $this->logger->error('Fatal error', ['error' => $e->getMessage()]);
            $this->sendErrorResponse('Fatal error: ' . $e->getMessage(), 500);
        }
    }

    private function getWeather(): void
    {
        $city = $_GET['city'] ?? '';
        $country = $_GET['country'] ?? '';
        $filters = $this->extractFilters();

        if (empty($city)) {
            $this->sendErrorResponse('City parameter is required', 400);
            return;
        }

        try {
            // Try Open-Meteo first (more reliable)
            $weatherData = $this->openMeteoService->getCurrentWeather($city, $country);
            $transformedData = $this->transformer->transform($weatherData, $filters);
            
            $this->sendSuccessResponse($transformedData);
        } catch (\Exception $e) {
            $this->logger->error('Open-Meteo weather fetch error', [
                'city' => $city,
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to OpenWeatherMap
            try {
                $weatherData = $this->weatherService->getCurrentWeather($city, $country);
                $transformedData = $this->transformer->transform($weatherData, $filters);
                
                $this->sendSuccessResponse($transformedData);
            } catch (\Exception $fallbackError) {
                $this->logger->error('Weather fetch error (both APIs failed)', [
                    'city' => $city,
                    'country' => $country,
                    'openmeteo_error' => $e->getMessage(),
                    'openweather_error' => $fallbackError->getMessage()
                ]);
                $this->sendErrorResponse('Failed to fetch weather data: ' . $e->getMessage(), 500);
            }
        }
    }

    private function getWeatherByCoordinates(): void
    {
        $lat = $_GET['lat'] ?? '';
        $lon = $_GET['lon'] ?? '';
        $filters = $this->extractFilters();

        if (empty($lat) || empty($lon)) {
            $this->sendErrorResponse('Latitude and longitude parameters are required', 400);
            return;
        }

        if (!is_numeric($lat) || !is_numeric($lon)) {
            $this->sendErrorResponse('Latitude and longitude must be numeric', 400);
            return;
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            $this->sendErrorResponse('Invalid coordinates range', 400);
            return;
        }

        try {
            // Try Open-Meteo first
            $weatherData = $this->openMeteoService->getWeatherByCoordinates($lat, $lon);
            $transformedData = $this->transformer->transform($weatherData, $filters);
            
            $this->sendSuccessResponse($transformedData);
        } catch (\Exception $e) {
            $this->logger->error('Open-Meteo weather fetch by coordinates error', [
                'lat' => $lat,
                'lon' => $lon,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to OpenWeatherMap
            try {
                $weatherData = $this->weatherService->getWeatherByCoordinates($lat, $lon);
                $transformedData = $this->transformer->transform($weatherData, $filters);
                
                $this->sendSuccessResponse($transformedData);
            } catch (\Exception $fallbackError) {
                $this->logger->error('Weather fetch by coordinates error (both APIs failed)', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'openmeteo_error' => $e->getMessage(),
                    'openweather_error' => $fallbackError->getMessage()
                ]);
                $this->sendErrorResponse('Failed to fetch weather data: ' . $e->getMessage(), 500);
            }
        }
    }

    private function getWeatherForecast(): void
    {
        $city = $_GET['city'] ?? '';
        $country = $_GET['country'] ?? '';
        $days = (int)($_GET['days'] ?? 5);
        $filters = $this->extractFilters();

        if (empty($city)) {
            $this->sendErrorResponse('City parameter is required', 400);
            return;
        }

        if ($days < 1 || $days > 16) {
            $this->sendErrorResponse('Days parameter must be between 1 and 16', 400);
            return;
        }

        try {
            // Try Open-Meteo first
            $forecastData = $this->openMeteoService->getWeatherForecast($city, $country, $days);
            
            // Transform each day's data
            $transformedData = [];
            foreach ($forecastData as $date => $dayData) {
                $transformedData[$date] = [
                    'date' => $dayData['date'],
                    'min_temp' => $dayData['min_temp'],
                    'max_temp' => $dayData['max_temp'],
                    'avg_humidity' => $dayData['avg_humidity'],
                    'avg_pressure' => $dayData['avg_pressure'],
                    'main_condition' => $dayData['main_condition'],
                    'conditions' => $dayData['conditions'],
                    'forecasts_count' => $dayData['forecasts_count']
                ];

                // Apply filters if needed
                if (isset($filters['temperature_unit']) && $filters['temperature_unit'] === 'fahrenheit') {
                    $transformedData[$date]['min_temp'] = round(($transformedData[$date]['min_temp'] * 9/5) + 32, 1);
                    $transformedData[$date]['max_temp'] = round(($transformedData[$date]['max_temp'] * 9/5) + 32, 1);
                }
            }
            
            $this->sendSuccessResponse($transformedData);
        } catch (\Exception $e) {
            $this->logger->error('Open-Meteo weather forecast fetch error', [
                'city' => $city,
                'country' => $country,
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            
            // Fallback to OpenWeatherMap
            try {
                $forecastData = $this->weatherService->getWeatherForecast($city, $country, $days);
                
                // Transform each day's data
                $transformedData = [];
                foreach ($forecastData as $date => $dayData) {
                    $transformedData[$date] = [
                        'date' => $dayData['date'],
                        'min_temp' => $dayData['min_temp'],
                        'max_temp' => $dayData['max_temp'],
                        'avg_humidity' => $dayData['avg_humidity'],
                        'avg_pressure' => $dayData['avg_pressure'],
                        'main_condition' => $dayData['main_condition'],
                        'conditions' => $dayData['conditions'],
                        'forecasts_count' => count($dayData['forecasts'])
                    ];

                    // Apply filters if needed
                    if (isset($filters['temperature_unit']) && $filters['temperature_unit'] === 'fahrenheit') {
                        $transformedData[$date]['min_temp'] = round(($transformedData[$date]['min_temp'] * 9/5) + 32, 1);
                        $transformedData[$date]['max_temp'] = round(($transformedData[$date]['max_temp'] * 9/5) + 32, 1);
                    }
                }
                
                $this->sendSuccessResponse($transformedData);
            } catch (\Exception $fallbackError) {
                $this->logger->error('Weather forecast fetch error (both APIs failed)', [
                    'city' => $city,
                    'country' => $country,
                    'days' => $days,
                    'openmeteo_error' => $e->getMessage(),
                    'openweather_error' => $fallbackError->getMessage()
                ]);
                $this->sendErrorResponse('Failed to fetch weather forecast: ' . $e->getMessage(), 500);
            }
        }
    }

    private function extractFilters(): array
    {
        return [
            'fields' => $_GET['fields'] ?? null,
            'temperature_unit' => $_GET['temperature_unit'] ?? null,
            'wind_unit' => $_GET['wind_unit'] ?? null,
            'pressure_unit' => $_GET['pressure_unit'] ?? null,
            'include_computed' => isset($_GET['include_computed']) ? 'true' : null,
        ];
    }

    private function setHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    private function sendSuccessResponse(array $data): void
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => time(),
            'formatted_time' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function sendErrorResponse(string $message, int $code): void
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => time(),
            'formatted_time' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
