# Weather API Transformer

PHP 8.2+ додаток для отримання та трансформації даних про погоду з OpenWeatherMap API.

## Встановлення

1. Встановіть залежності через Composer:

```bash
composer install
```

2. Налаштуйте конфігурацію:

```bash
cp config.env .env
```

3. Отримайте API ключ з [OpenWeatherMap](https://openweathermap.org/api) та додайте його в `.env` файл:

```
OPENWEATHER_API_KEY=your_api_key_here
```

**Примітка:** Якщо API ключ недійсний або відсутній, додаток автоматично переходить в тестовий режим та повертає фейкові дані для демонстрації функціоналу.

## Використання

### Отримання погоди за містом

```
GET /weather?city=Kyiv&country=UA
```

### Отримання погоди за координатами

```
GET /weather/coordinates?lat=50.4501&lon=30.5234
```

### Отримання прогнозу погоди на кілька днів

```
GET /weather/forecast?city=Kyiv&days=5
```

### Фільтри та параметри

- `fields` - вибір конкретних полів (через кому): `city,temperature,humidity`
- `temperature_unit` - одиниця температури: `celsius` (за замовчуванням) або `fahrenheit`
- `wind_unit` - одиниця швидкості вітру: `m/s` (за замовчуванням) або `mph`
- `pressure_unit` - одиниця тиску: `hPa` (за замовчуванням) або `inHg`
- `include_computed` - додати обчислені поля (категорії температури, вологості, вітру)
- `days` - кількість днів для прогнозу (1-16, тільки для `/weather/forecast`)

### Приклади запитів

```bash
# Базова інформація про погоду
curl "http://localhost/index.php/weather?city=Kyiv"

# Погода з фільтрами
curl "http://localhost/index.php/weather?city=Kyiv&fields=city,temperature,humidity&temperature_unit=fahrenheit"

# Погода за координатами з обчисленими полями
curl "http://localhost/index.php/weather/coordinates?lat=50.4501&lon=30.5234&include_computed=true"

# Погода з усіма одиницями в американській системі
curl "http://localhost/index.php/weather?city=New York&temperature_unit=fahrenheit&wind_unit=mph&pressure_unit=inHg"

# Прогноз погоди на 5 днів
curl "http://localhost/index.php/weather/forecast?city=Kyiv&days=5"

# Прогноз погоди в Фаренгейтах
curl "http://localhost/index.php/weather/forecast?city=Kyiv&days=3&temperature_unit=fahrenheit"
```

## Структура проекту

```
├── src/
│   ├── Config/
│   │   └── Config.php          # Конфігурація додатку
│   ├── Controllers/
│   │   └── WeatherController.php # Основний контролер API
│   ├── Models/
│   │   └── WeatherData.php     # Модель даних погоди
│   └── Services/
│       ├── WeatherApiService.php    # Сервіс для роботи з OpenWeatherMap API
│       └── WeatherTransformer.php   # Сервіс трансформації даних
├── logs/                       # Логи додатку
├── composer.json              # Залежності
├── config.env                 # Приклад конфігурації
├── weather.html               # Веб-інтерфейс
└── index.php                  # Точка входу
```

## Особливості

- **🌐 Веб-інтерфейс** - красивий та зручний інтерфейс для вибору міста та перегляду прогнозу
- **📅 Прогноз на кілька днів** - можливість отримати прогноз погоди на 1-16 днів
- **🌡️ Гнучкі одиниці вимірювання** - підтримка Цельсія та Фаренгейта
- **PHP 8.2+** з використанням сучасних можливостей мови
- **Composer** для управління залежностями
- **Dotenv** для конфігурації через .env файл
- **Guzzle HTTP** для HTTP запитів
- **Monolog** для логування
- **Валідація** вхідних параметрів
- **Обробка помилок** з детальним логуванням
- **Фільтрація** та трансформація даних
- **Тестовий режим** - автоматичний fallback на демонстраційні дані

## Логування

Логи зберігаються в директорії `logs/app.log` та включають:

- Успішні запити до API
- Помилки запитів
- Помилки валідації
- Системні помилки

## API Response Format

### Успішна відповідь

```json
{
  "success": true,
  "data": {
    "city": "Kyiv",
    "country": "UA",
    "temperature": 15.5,
    "feels_like": 14.2,
    "humidity": 65,
    "pressure": 1013.25,
    "wind_speed": 3.2,
    "description": "clear sky",
    "icon": "01d",
    "timestamp": 1703123456,
    "formatted_time": "2023-12-21 15:30:56"
  },
  "timestamp": 1703123456,
  "formatted_time": "2023-12-21 15:30:56"
}
```

### Помилка

```json
{
  "success": false,
  "error": "City parameter is required",
  "timestamp": 1703123456,
  "formatted_time": "2023-12-21 15:30:56"
}
```
