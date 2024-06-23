/** Rochak Maharjan (Student ID:2408877) */

/** This is the default location to be searched for weather */
const CITY_PLACEHOLDER = 'jamnagar';

/**
 * Gets the icon name and displays the correct image on the screen.
 *
 * @param {string} iconName - Name of the icon.
 */
const setIcon = (iconName) => {
	const weatherImageElement = document.getElementById('weather--image');
	const currentImage = `https://openweathermap.org/img/wn/${iconName}@2x.png`;
	weatherImageElement.src = currentImage;
};

/**
 * Displays weather data on the screen.
 *
 * @param {JSON} jsonData - JSON data from the OpenWeatherMap API.
 * @param {string} city - The city to be displayed.
 */
const setDataToScreen = (jsonData, city = CITY_PLACEHOLDER) => {
	// Get DOM elements for different weather data
	const locationElement = document.getElementById('city');
	const humidityElement = document.getElementById('humidity-value');
	const temperatureElement = document.getElementById('temperature');
	const windSpeedElement = document.getElementById('wind-speed-value');
	const pressureElement = document.getElementById('pressure');

	// Extract relevant data from JSON and set to DOM elements
	const currentIcon = jsonData.weather[0].icon;
	setIcon(currentIcon);
	const currentTemperature = jsonData.main;
	locationElement.innerHTML =
		city[0].toUpperCase() + city.slice(1).toLowerCase();
	temperatureElement.innerHTML = (currentTemperature.temp - 273.15).toFixed(2);
	humidityElement.innerHTML = currentTemperature.humidity;
	windSpeedElement.innerHTML = jsonData.wind.speed.toFixed(2);
	pressureElement.innerHTML = currentTemperature.pressure;
};

/**
 * Fetches weather data from the OpenWeatherMap API.
 *
 * @param {string} city - Name of the city to fetch weather data for. Defaults to 'jamnagar'.
 * @returns {JSON} - JSON object containing weather data.
 */
const fetchData = async (city = CITY_PLACEHOLDER) => {
	try {
		// Check if city is provided
		if (!city) {
			alert('Please enter a city.');
			return;
		}

		// Fetch weather data from API
		const response = await fetch(
			`https://api.openweathermap.org/data/2.5/weather?q=${city}&APPID=39348318b634c47da2475d4664f4641f`
		);
		const jsonData = await response.json();

		// Handle case when city is not found
		if (jsonData.cod === '404') {
			alert(`City not found: ${city}`);
			return;
		}

		// Save fetched data to local storage only if it corresponds to the searched city
		const storedCity = city.toLowerCase();
		if (jsonData.name.toLowerCase() === storedCity) {
			localStorage.setItem(storedCity, JSON.stringify(jsonData));
		}

		return jsonData;
	} catch (error) {
		// Try to retrieve weather data from local storage if there's no internet connection
		const localData = localStorage.getItem(city.toLowerCase());
		if (localData) {
			return JSON.parse(localData);
		} else {
			// Return null if there's no local data found
			return null;
		}
	}
};

// Display initial weather data if available
if (typeof initialWeatherData !== 'undefined') {
	setDataToScreen(initialWeatherData);
}

// Event listener for city search form submission
document
	.getElementById('search-city-form')
	.addEventListener('submit', async (e) => {
		e.preventDefault();
		const currentCity = document.getElementById('city-value').value;
		const weatherData = await fetchData(currentCity);
		if (weatherData) {
			setDataToScreen(weatherData, currentCity);
		}
	});

// Function to fetch and display weather data
const main = async () => {
	const weatherData = await fetchData();
	if (weatherData) {
		setDataToScreen(weatherData);
	} else {
		// Handle case when no data is available
		alert('No weather data available.');
	}
};

// Initial execution of the main function
main();
