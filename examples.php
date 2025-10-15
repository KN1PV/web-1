<?php

/**
 * Приклад використання Weather API
 * 
 * Перед запуском переконайтеся, що:
 * 1. Встановлені залежності: composer install
 * 2. Налаштований .env файл з API ключем OpenWeatherMap
 * 3. Запущений веб-сервер (наприклад, php -S localhost:8000)
 */

echo "=== Weather API Examples ===\n\n";

// Приклад 1: Отримання погоди за містом
echo "1. Погода в Києві:\n";
echo "GET /weather?city=Kyiv&country=UA\n";
echo "URL: http://localhost:8000/index.php/weather?city=Kyiv&country=UA\n\n";

// Приклад 2: Погода з фільтрами
echo "2. Погода з конкретними полями:\n";
echo "GET /weather?city=Kyiv&fields=city,temperature,humidity,description\n";
echo "URL: http://localhost:8000/index.php/weather?city=Kyiv&fields=city,temperature,humidity,description\n\n";

// Приклад 3: Погода в Фаренгейтах
echo "3. Погода в Фаренгейтах:\n";
echo "GET /weather?city=Kyiv&temperature_unit=fahrenheit\n";
echo "URL: http://localhost:8000/index.php/weather?city=Kyiv&temperature_unit=fahrenheit\n\n";

// Приклад 4: Погода за координатами
echo "4. Погода за координатами:\n";
echo "GET /weather/coordinates?lat=50.4501&lon=30.5234\n";
echo "URL: http://localhost:8000/index.php/weather/coordinates?lat=50.4501&lon=30.5234\n\n";

// Приклад 5: Погода з обчисленими полями
echo "5. Погода з категоріями:\n";
echo "GET /weather?city=Kyiv&include_computed=true\n";
echo "URL: http://localhost:8000/index.php/weather?city=Kyiv&include_computed=true\n\n";

// Приклад 6: Американські одиниці вимірювання
echo "6. Погода в американських одиницях:\n";
echo "GET /weather?city=New York&temperature_unit=fahrenheit&wind_unit=mph&pressure_unit=inHg\n";
echo "URL: http://localhost:8000/index.php/weather?city=New York&temperature_unit=fahrenheit&wind_unit=mph&pressure_unit=inHg\n\n";

echo "=== Доступні параметри ===\n";
echo "city - назва міста (обов'язковий для /weather)\n";
echo "country - код країни (опціональний)\n";
echo "lat - широта (обов'язковий для /weather/coordinates)\n";
echo "lon - довгота (обов'язковий для /weather/coordinates)\n";
echo "fields - поля для виводу через кому\n";
echo "temperature_unit - celsius (за замовчуванням) або fahrenheit\n";
echo "wind_unit - m/s (за замовчуванням) або mph\n";
echo "pressure_unit - hPa (за замовчуванням) або inHg\n";
echo "include_computed - додати категорії (true/false)\n\n";

echo "=== Запуск сервера ===\n";
echo "php -S localhost:8000\n\n";

echo "=== Тестування через curl ===\n";
echo "curl \"http://localhost:8000/index.php/weather?city=Kyiv\"\n";
echo "curl \"http://localhost:8000/index.php/weather/coordinates?lat=50.4501&lon=30.5234\"\n";

