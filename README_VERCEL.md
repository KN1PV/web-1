# Weather API - Vercel Version

Weather API для отримання даних погоди з різних джерел. Підтримує деплой на Vercel.

## 🚀 Швидкий старт

### Локальна розробка

```bash
# Встановлення залежностей
npm install

# Запуск локального сервера
npm run dev

# Тестування API
npm test
```

### Деплой на Vercel

```bash
# Встановлення Vercel CLI
npm install -g vercel

# Деплой
vercel --prod
```

## 📁 Структура проекту

```
├── weather.html          # Головна сторінка
├── test-api.html         # Тестова сторінка
├── package.json          # Node.js залежності
├── vercel.json          # Конфігурація Vercel
├── api/
│   ├── weather.js       # API поточної погоди
│   └── weather/
│       └── forecast.js  # API прогнозу погоди
└── test-local.js        # Локальне тестування
```

## 🌐 API Endpoints

- `GET /` - Головна сторінка
- `GET /test` - Тестова сторінка
- `GET /weather` - Поточна погода
- `GET /weather/forecast` - Прогноз погоди

## 📖 Документація

Детальна документація доступна в [VERCEL_DEPLOYMENT.md](VERCEL_DEPLOYMENT.md)

## 🔧 Проблема з PHP на Vercel

Vercel не підтримує PHP безпосередньо, тому проект було переписано на Node.js/JavaScript для сумісності з Vercel.

## 🌡️ Приклади використання

### Поточна погода

```bash
curl "https://your-domain.vercel.app/weather?city=Kyiv&country=UA"
```

### Прогноз погоди

```bash
curl "https://your-domain.vercel.app/weather/forecast?city=Kyiv&country=UA&days=5"
```

## 🎯 Особливості

- ✅ Працює на Vercel
- ✅ Швидкий деплой
- ✅ Автоматичне масштабування
- ✅ CDN по всьому світу
- ✅ Безкоштовний хостинг
- ✅ HTTPS за замовчуванням
- ✅ Підтримка української мови
- ✅ Адаптивний дизайн
- ✅ Тестова сторінка для перевірки API
