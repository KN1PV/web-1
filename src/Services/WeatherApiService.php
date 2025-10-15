<?php

namespace App\Services;

use App\Config\Config;
use App\Models\WeatherData;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class WeatherApiService
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
        
        $this->logger = new Logger('weather_api');
        $this->logger->pushHandler(new StreamHandler($this->config->get('log_file'), Logger::INFO));
    }

    /**
     * @throws GuzzleException
     */
    public function getCurrentWeather(string $city, string $country = ''): WeatherData
    {
        $query = $city;
        if (!empty($country)) {
            $query .= ',' . $country;
        }

        $url = $this->config->get('openweather_base_url') . '/weather';
        $params = [
            'q' => $query,
            'appid' => $this->config->get('openweather_api_key'),
            'units' => 'metric'
        ];

        try {
            $response = $this->httpClient->get($url, ['query' => $params]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                // If API key is invalid, return mock data for demo purposes
                if ($response->getStatusCode() === 401) {
                    $this->logger->warning('API key invalid, returning mock data', ['city' => $city, 'country' => $country]);
                    return $this->getMockWeatherData($city, $country);
                }
                throw new \Exception('API request failed: ' . ($data['message'] ?? 'Unknown error'));
            }

            $this->logger->info('Weather data retrieved successfully', ['city' => $city, 'country' => $country]);
            
            return WeatherData::fromOpenWeatherData($data);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch weather data', [
                'city' => $city,
                'country' => $country,
                'error' => $e->getMessage()
            ]);
            
            // If it's an API key issue, return mock data
            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Invalid API key') !== false) {
                $this->logger->warning('API key invalid, returning mock data', ['city' => $city, 'country' => $country]);
                return $this->getMockWeatherData($city, $country);
            }
            
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getWeatherByCoordinates(float $lat, float $lon): WeatherData
    {
        $url = $this->config->get('openweather_base_url') . '/weather';
        $params = [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->config->get('openweather_api_key'),
            'units' => 'metric'
        ];

        try {
            $response = $this->httpClient->get($url, ['query' => $params]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                // If API key is invalid, return mock data for demo purposes
                if ($response->getStatusCode() === 401) {
                    $this->logger->warning('API key invalid, returning mock data', ['lat' => $lat, 'lon' => $lon]);
                    return $this->getMockWeatherDataByCoordinates($lat, $lon);
                }
                throw new \Exception('API request failed: ' . ($data['message'] ?? 'Unknown error'));
            }

            $this->logger->info('Weather data retrieved by coordinates', ['lat' => $lat, 'lon' => $lon]);
            
            return WeatherData::fromOpenWeatherData($data);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch weather data by coordinates', [
                'lat' => $lat,
                'lon' => $lon,
                'error' => $e->getMessage()
            ]);
            
            // If it's an API key issue, return mock data
            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Invalid API key') !== false) {
                $this->logger->warning('API key invalid, returning mock data', ['lat' => $lat, 'lon' => $lon]);
                return $this->getMockWeatherDataByCoordinates($lat, $lon);
            }
            
            throw $e;
        }
    }

    private function getMockWeatherData(string $city, string $country = ''): WeatherData
    {
        // Generate mock data for demo purposes
        $mockData = [
            'name' => $city,
            'sys' => [
                'country' => $country ?: 'UA'
            ],
            'main' => [
                'temp' => 288.15 + rand(-10, 20), // Random temperature between 10-30°C
                'feels_like' => 288.15 + rand(-10, 20),
                'humidity' => rand(30, 90),
                'pressure' => rand(1000, 1030)
            ],
            'wind' => [
                'speed' => rand(1, 15)
            ],
            'weather' => [
                [
                    'description' => 'clear sky',
                    'icon' => '01d'
                ]
            ],
            'dt' => time()
        ];

        return WeatherData::fromOpenWeatherData($mockData);
    }

    private function getMockWeatherDataByCoordinates(float $lat, float $lon): WeatherData
    {
        // Generate mock data for demo purposes based on coordinates
        $cityName = $this->getCityNameByCoordinates($lat, $lon);
        
        $mockData = [
            'name' => $cityName,
            'sys' => [
                'country' => 'UA'
            ],
            'main' => [
                'temp' => 288.15 + rand(-10, 20), // Random temperature between 10-30°C
                'feels_like' => 288.15 + rand(-10, 20),
                'humidity' => rand(30, 90),
                'pressure' => rand(1000, 1030)
            ],
            'wind' => [
                'speed' => rand(1, 15)
            ],
            'weather' => [
                [
                    'description' => 'clear sky',
                    'icon' => '01d'
                ]
            ],
            'dt' => time()
        ];

        return WeatherData::fromOpenWeatherData($mockData);
    }

    /**
     * Get weather forecast for multiple days
     * @throws GuzzleException
     */
    public function getWeatherForecast(string $city, string $country = '', int $days = 5): array
    {
        $query = $city;
        if (!empty($country)) {
            $query .= ',' . $country;
        }

        $url = $this->config->get('openweather_base_url') . '/forecast';
        $params = [
            'q' => $query,
            'appid' => $this->config->get('openweather_api_key'),
            'units' => 'metric',
            'cnt' => $days * 8 // 8 forecasts per day (every 3 hours)
        ];

        try {
            $response = $this->httpClient->get($url, ['query' => $params]);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                if ($response->getStatusCode() === 401) {
                    $this->logger->warning('API key invalid, returning mock forecast data', ['city' => $city, 'country' => $country]);
                    return $this->getMockForecastData($city, $country, $days);
                }
                throw new \Exception('API request failed: ' . ($data['message'] ?? 'Unknown error'));
            }

            $this->logger->info('Weather forecast retrieved successfully', ['city' => $city, 'country' => $country, 'days' => $days]);
            
            return $this->processForecastData($data['list'], $days);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch weather forecast', [
                'city' => $city,
                'country' => $country,
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            
            if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Invalid API key') !== false) {
                $this->logger->warning('API key invalid, returning mock forecast data', ['city' => $city, 'country' => $country]);
                return $this->getMockForecastData($city, $country, $days);
            }
            
            throw $e;
        }
    }

    private function processForecastData(array $forecastList, int $days): array
    {
        $processedData = [];
        $dailyData = [];

        foreach ($forecastList as $item) {
            $date = date('Y-m-d', $item['dt']);
            
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'forecasts' => [],
                    'min_temp' => $item['main']['temp'],
                    'max_temp' => $item['main']['temp'],
                    'avg_humidity' => 0,
                    'avg_pressure' => 0,
                    'conditions' => []
                ];
            }

            $dailyData[$date]['forecasts'][] = WeatherData::fromOpenWeatherData($item);
            $dailyData[$date]['min_temp'] = min($dailyData[$date]['min_temp'], $item['main']['temp']);
            $dailyData[$date]['max_temp'] = max($dailyData[$date]['max_temp'], $item['main']['temp']);
            $dailyData[$date]['avg_humidity'] += $item['main']['humidity'];
            $dailyData[$date]['avg_pressure'] += $item['main']['pressure'];
            $dailyData[$date]['conditions'][] = $item['weather'][0]['description'];
        }

        // Calculate averages and get unique conditions
        foreach ($dailyData as $date => &$dayData) {
            $forecastCount = count($dayData['forecasts']);
            $dayData['avg_humidity'] = round($dayData['avg_humidity'] / $forecastCount);
            $dayData['avg_pressure'] = round($dayData['avg_pressure'] / $forecastCount, 2);
            $dayData['conditions'] = array_unique($dayData['conditions']);
            $dayData['main_condition'] = $dayData['conditions'][0] ?? 'clear sky';
        }

        return array_slice($dailyData, 0, $days, true);
    }

    private function getMockForecastData(string $city, string $country = '', int $days = 5): array
    {
        $forecastData = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $baseTemp = 288.15 + rand(-5, 15); // Base temperature
            
            $forecastData[$date] = [
                'date' => $date,
                'forecasts' => [],
                'min_temp' => round($baseTemp - 273.15 - rand(2, 5), 1),
                'max_temp' => round($baseTemp - 273.15 + rand(2, 5), 1),
                'avg_humidity' => rand(40, 80),
                'avg_pressure' => rand(1000, 1030),
                'conditions' => ['clear sky', 'few clouds', 'scattered clouds'],
                'main_condition' => 'clear sky'
            ];

            // Generate hourly forecasts for the day
            for ($hour = 0; $hour < 24; $hour += 3) {
                $timestamp = strtotime($date . " {$hour}:00:00");
                $mockData = [
                    'name' => $city,
                    'sys' => ['country' => $country ?: 'UA'],
                    'main' => [
                        'temp' => $baseTemp + rand(-3, 3),
                        'feels_like' => $baseTemp + rand(-3, 3),
                        'humidity' => rand(30, 90),
                        'pressure' => rand(1000, 1030)
                    ],
                    'wind' => ['speed' => rand(1, 15)],
                    'weather' => [['description' => 'clear sky', 'icon' => '01d']],
                    'dt' => $timestamp
                ];
                
                $forecastData[$date]['forecasts'][] = WeatherData::fromOpenWeatherData($mockData);
            }
        }

        return $forecastData;
    }

    private function getCityNameByCoordinates(float $lat, float $lon): string
    {
        // Simple mapping for demo purposes
        if ($lat >= 50 && $lat <= 51 && $lon >= 30 && $lon <= 31) {
            return 'Kyiv';
        } elseif ($lat >= 49 && $lat <= 50 && $lon >= 23 && $lon <= 24) {
            return 'Lviv';
        } elseif ($lat >= 46 && $lat <= 47 && $lon >= 30 && $lon <= 31) {
            return 'Odessa';
        } else {
            return 'Unknown City';
        }
    }
}
