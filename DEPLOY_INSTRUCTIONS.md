# Інструкції для деплою на Vercel

## Проблема

Ваш PHP проект не працює на Vercel, тому що Vercel не підтримує PHP безпосередньо. Коли ви заходите на сайт, він просто завантажує PHP файл як статичний файл.

## Рішення

Я переписав ваш проект на Node.js/JavaScript, щоб він працював на Vercel.

## Що було зроблено

1. **Створено Node.js версію API**:

   - `api/weather.js` - для поточної погоди
   - `api/weather/forecast.js` - для прогнозу погоди

2. **Налаштовано Vercel конфігурацію**:

   - `vercel.json` - маршрутизація та налаштування
   - `package.json` - залежності Node.js

3. **Додано тестування**:
   - `test-api.html` - веб-інтерфейс для тестування
   - `test-local.js` - скрипт для локального тестування

## Як деплоїти

### Варіант 1: Через Vercel CLI

```bash
# Встановіть Vercel CLI
npm install -g vercel

# Встановіть залежності
npm install

# Деплойте
vercel --prod
```

### Варіант 2: Через веб-інтерфейс Vercel

1. Завантажте всі файли в репозиторій GitHub
2. Підключіть репозиторій до Vercel
3. Vercel автоматично виявить конфігурацію

## Файли для деплою

**Обов'язкові файли:**

- `weather.html` - головна сторінка
- `package.json` - залежності
- `vercel.json` - конфігурація
- `api/weather.js` - API поточної погоди
- `api/weather/forecast.js` - API прогнозу

**Додаткові файли:**

- `test-api.html` - тестова сторінка
- `test-local.js` - локальне тестування
- `VERCEL_DEPLOYMENT.md` - детальна документація

## Тестування

Після деплою перевірте:

1. Головна сторінка: `https://your-domain.vercel.app/`
2. Тестова сторінка: `https://your-domain.vercel.app/test`
3. API поточної погоди: `https://your-domain.vercel.app/weather?city=Kyiv`
4. API прогнозу: `https://your-domain.vercel.app/weather/forecast?city=Kyiv&days=5`

## Відмінності від PHP версії

1. **Мова**: JavaScript замість PHP
2. **Залежності**: axios замість Guzzle
3. **Структура**: API endpoints в папці `api/`
4. **Конфігурація**: vercel.json замість .htaccess
5. **Логування**: console.log замість Monolog

## Переваги

- ✅ Працює на Vercel
- ✅ Швидкий деплой
- ✅ Автоматичне масштабування
- ✅ CDN по всьому світу
- ✅ Безкоштовний хостинг
- ✅ HTTPS за замовчуванням

## Підтримка

Якщо виникнуть проблеми:

1. Перевірте логи в Vercel Dashboard
2. Використайте тестову сторінку `/test`
3. Перевірте конфігурацію в `vercel.json`
