const axios = require("axios");

// Weather API configuration
const OPEN_METEO_BASE_URL = "https://api.open-meteo.com/v1/forecast";
const GEOCODING_BASE_URL = "https://geocoding-api.open-meteo.com/v1/search";

// Helper function to get coordinates for a city
async function getCoordinates(city, country = "") {
  try {
    const searchQuery = country ? `${city}, ${country}` : city;
    const response = await axios.get(GEOCODING_BASE_URL, {
      params: {
        name: searchQuery,
        count: 1,
        language: "uk",
        format: "json",
      },
    });

    if (response.data.results && response.data.results.length > 0) {
      const result = response.data.results[0];
      return {
        latitude: result.latitude,
        longitude: result.longitude,
        name: result.name,
        country: result.country,
        country_code: result.country_code,
      };
    }
    throw new Error("Місто не знайдено");
  } catch (error) {
    throw new Error(`Помилка геокодування: ${error.message}`);
  }
}

// Helper function to get weather forecast
async function getWeatherForecast(
  latitude,
  longitude,
  days = 5,
  temperatureUnit = "celsius"
) {
  try {
    const response = await axios.get(OPEN_METEO_BASE_URL, {
      params: {
        latitude,
        longitude,
        daily:
          "temperature_2m_max,temperature_2m_min,weather_code,relative_humidity_2m,pressure_msl",
        temperature_unit: temperatureUnit,
        wind_speed_unit: "ms",
        timezone: "auto",
        forecast_days: days,
      },
    });

    const data = response.data;
    const daily = data.daily;

    // Weather code to description mapping
    const weatherDescriptions = {
      0: "Ясно",
      1: "Переважно ясно",
      2: "Частково хмарно",
      3: "Хмарно",
      4: "Хмарно",
      5: "Туман",
      6: "Туман",
      7: "Туман",
      8: "Туман",
      45: "Туман",
      48: "Туман",
      51: "Легкий дощ",
      53: "Помірний дощ",
      55: "Сильний дощ",
      56: "Легкий мокрий сніг",
      57: "Сильний мокрий сніг",
      61: "Легкий дощ",
      63: "Помірний дощ",
      65: "Сильний дощ",
      66: "Легкий мокрий сніг",
      67: "Сильний мокрий сніг",
      71: "Легкий сніг",
      73: "Помірний сніг",
      75: "Сильний сніг",
      77: "Сніжні зерна",
      80: "Легкі зливи",
      81: "Помірні зливи",
      82: "Сильні зливи",
      85: "Легкі снігові зливи",
      86: "Сильні снігові зливи",
      95: "Гроза",
      96: "Гроза з легким градом",
      99: "Гроза з сильним градом",
    };

    const forecast = {};
    for (let i = 0; i < daily.time.length; i++) {
      const date = daily.time[i];
      forecast[date] = {
        date: date,
        max_temp: Math.round(daily.temperature_2m_max[i]),
        min_temp: Math.round(daily.temperature_2m_min[i]),
        main_condition:
          weatherDescriptions[daily.weather_code[i]] || "Невідомо",
        avg_humidity: Math.round(daily.relative_humidity_2m[i]),
        avg_pressure: Math.round(daily.pressure_msl[i]),
      };
    }

    return forecast;
  } catch (error) {
    throw new Error(`Помилка отримання прогнозу: ${error.message}`);
  }
}

// Main handler function
module.exports = async (req, res) => {
  // Set CORS headers
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type");

  if (req.method === "OPTIONS") {
    res.status(200).end();
    return;
  }

  try {
    const { city, country, days = 5, temperature_unit = "celsius" } = req.query;

    if (!city) {
      return res.status(400).json({
        success: false,
        error: "Параметр city є обов'язковим",
      });
    }

    // Get coordinates
    const coordinates = await getCoordinates(city, country);

    // Get forecast
    const forecast = await getWeatherForecast(
      coordinates.latitude,
      coordinates.longitude,
      parseInt(days),
      temperature_unit
    );

    // Return data
    res.json({
      success: true,
      data: forecast,
    });
  } catch (error) {
    console.error("Error:", error);
    res.status(500).json({
      success: false,
      error: error.message,
    });
  }
};
