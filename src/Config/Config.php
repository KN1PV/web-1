<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct()
    {
        $this->loadEnvironment();
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->config = [
            'openweather_api_key' => $_ENV['OPENWEATHER_API_KEY'] ?? '',
            'openweather_base_url' => $_ENV['OPENWEATHER_BASE_URL'] ?? 'https://api.openweathermap.org/data/2.5',
            'openmeteo_base_url' => $_ENV['OPENMETEO_BASE_URL'] ?? 'https://api.open-meteo.com/v1',
            'app_name' => $_ENV['APP_NAME'] ?? 'Weather API Transformer',
            'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'app_timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Kiev',
            'log_level' => $_ENV['LOG_LEVEL'] ?? 'info',
            'log_file' => $_ENV['LOG_FILE'] ?? 'logs/app.log',
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function getAll(): array
    {
        return $this->config;
    }
}
