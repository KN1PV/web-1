# Деплой на Vercel - Weather API

## Проблема

Vercel не підтримує PHP безпосередньо, тому PHP файли завантажуються як статичні файли замість виконання.

## Рішення

Перенесли логіку з PHP на Node.js/JavaScript для сумісності з Vercel.

## Структура проекту для Vercel

```
├── weather.html          # Статичний HTML файл
├── package.json          # Node.js залежності
├── vercel.json          # Конфігурація Vercel
├── .vercelignore        # Файли для ігнорування
├── api/
│   ├── weather.js       # API для поточної погоди
│   └── weather/
│       └── forecast.js  # API для прогнозу погоди
└── index.js             # Основний файл (не використовується)
```

## Як деплоїти

1. **Встановіть Vercel CLI** (якщо ще не встановлено):

   ```bash
   npm install -g vercel
   ```

2. **Встановіть залежності**:

   ```bash
   npm install
   ```

3. **Деплойте проект**:

   ```bash
   vercel --prod
   ```

   Або просто завантажте файли через веб-інтерфейс Vercel.

## API Endpoints

- `GET /` - Головна сторінка (weather.html)
- `GET /weather?city=Kyiv&country=UA&temperature_unit=celsius` - Поточна погода
- `GET /weather/forecast?city=Kyiv&country=UA&days=5&temperature_unit=celsius` - Прогноз погоди

## Параметри

### Weather API

- `city` (обов'язковий) - Назва міста
- `country` (опціональний) - Код країни
- `temperature_unit` - celsius або fahrenheit (за замовчуванням: celsius)

### Forecast API

- `city` (обов'язковий) - Назва міста
- `country` (опціональний) - Код країни
- `days` - Кількість днів прогнозу (за замовчуванням: 5)
- `temperature_unit` - celsius або fahrenheit (за замовчуванням: celsius)

## Приклад використання

```javascript
// Поточна погода
fetch("/weather?city=Kyiv&country=UA")
  .then((response) => response.json())
  .then((data) => console.log(data));

// Прогноз погоди
fetch("/weather/forecast?city=Kyiv&country=UA&days=7")
  .then((response) => response.json())
  .then((data) => console.log(data));
```

## Відмінності від PHP версії

1. **Мова**: JavaScript замість PHP
2. **Залежності**: axios замість Guzzle
3. **Структура**: API endpoints в папці `api/`
4. **Конфігурація**: vercel.json замість .htaccess
5. **Логування**: console.log замість Monolog (можна додати пізніше)

## Переваги

- ✅ Працює на Vercel
- ✅ Швидкий деплой
- ✅ Автоматичне масштабування
- ✅ CDN по всьому світу
- ✅ Безкоштовний хостинг
- ✅ HTTPS за замовчуванням
