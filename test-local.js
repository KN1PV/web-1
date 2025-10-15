const axios = require("axios");

// Test the weather API locally
async function testWeatherAPI() {
  const baseUrl = "http://localhost:3000"; // Vercel dev server

  console.log("🌤️ Тестування Weather API...\n");

  try {
    // Test current weather
    console.log("1. Тестування поточної погоди...");
    const currentResponse = await axios.get(`${baseUrl}/weather`, {
      params: {
        city: "Kyiv",
        country: "UA",
        temperature_unit: "celsius",
      },
    });

    if (currentResponse.data.success) {
      console.log("✅ Поточна погода працює!");
      console.log(
        `   Місто: ${currentResponse.data.data.city}, ${currentResponse.data.data.country}`
      );
      console.log(`   Температура: ${currentResponse.data.data.temperature}°C`);
      console.log(`   Опис: ${currentResponse.data.data.description}\n`);
    } else {
      console.log("❌ Помилка поточної погоди:", currentResponse.data.error);
    }

    // Test forecast
    console.log("2. Тестування прогнозу погоди...");
    const forecastResponse = await axios.get(`${baseUrl}/weather/forecast`, {
      params: {
        city: "Kyiv",
        country: "UA",
        days: 5,
        temperature_unit: "celsius",
      },
    });

    if (forecastResponse.data.success) {
      console.log("✅ Прогноз погоди працює!");
      const forecast = forecastResponse.data.data;
      const days = Object.keys(forecast).length;
      console.log(`   Отримано прогноз на ${days} днів\n`);
    } else {
      console.log("❌ Помилка прогнозу погоди:", forecastResponse.data.error);
    }

    console.log("🎉 Всі тести завершено!");
  } catch (error) {
    console.error("❌ Помилка тестування:", error.message);
    console.log("\n💡 Переконайтеся, що Vercel dev server запущено:");
    console.log("   vercel dev");
  }
}

// Run tests
testWeatherAPI();
