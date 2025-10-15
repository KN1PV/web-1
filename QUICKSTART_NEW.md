# Швидкий запуск Weather API

## 1. Встановлення залежностей

```bash
composer install
```

## 2. Налаштування конфігурації

Скопіюйте `config.env` в `.env` та додайте ваш API ключ OpenWeatherMap:

```bash
cp config.env .env
```

Відредагуйте `.env` файл:

```
OPENWEATHER_API_KEY=ваш_api_ключ_тут
```

**Примітка:** Якщо API ключ недійсний, додаток автоматично переходить в тестовий режим з фейковими даними.

## 3. Запуск сервера

```bash
php -S localhost:8000
```

## 4. Використання

### 🌐 Веб-інтерфейс (рекомендовано):

Відкрийте браузер та перейдіть на:

```
http://localhost:8000
```

Красивий інтерфейс дозволяє:

- Вибирати місто та країну
- Обирати кількість днів прогнозу (1-10)
- Перемикати одиниці температури (Цельсій/Фаренгейт)
- Переглядати поточну погоду та прогноз

### 🔗 API Endpoints:

#### Отримання погоди за містом:

```
http://localhost:8000/weather?city=Kyiv
```

#### Отримання погоди за координатами:

```
http://localhost:8000/weather/coordinates?lat=50.4501&lon=30.5234
```

#### Прогноз погоди на кілька днів:

```
http://localhost:8000/weather/forecast?city=Kyiv&days=5
```

#### Погода з фільтрами:

```
http://localhost:8000/weather?city=Kyiv&fields=city,temperature,humidity&temperature_unit=fahrenheit
```

## 5. Перегляд прикладів

```bash
php examples.php
```

## Структура відповіді

### Поточна погода:

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

### Прогноз погоди:

```json
{
  "success": true,
  "data": {
    "2025-10-14": {
      "date": "2025-10-14",
      "min_temp": 10,
      "max_temp": 19,
      "avg_humidity": 51,
      "avg_pressure": 1023.5,
      "main_condition": "clear sky",
      "conditions": ["clear sky", "few clouds"],
      "forecasts_count": 8
    }
  },
  "timestamp": 1703123456,
  "formatted_time": "2023-12-21 15:30:56"
}
```

### Помилка:

```json
{
  "success": false,
  "error": "City parameter is required",
  "timestamp": 1703123456,
  "formatted_time": "2023-12-21 15:30:56"
}
```

## Логи

Логи зберігаються в `logs/app.log`

