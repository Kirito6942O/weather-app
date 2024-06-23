<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta
			name="viewport"
			content="width=device-width, initial-scale=1.0"
		/>
		<title>Assignment Weather</title>
		<link
			rel="stylesheet"
			href="./style.css"
		/>
	
	</head>
	<body>
		<!-- Rochak Maharjan (Student ID:2408877) -->
    </form>
		<div class="head">
			<form
				class="search"
				id="search-city-form"
				method="post"
				action="index.php"
			>
				<input
					type="text"
					placeholder="Enter City"
					spellcheck="false"
					id="city-value"
					autocomplete="off"
					name="city"
				/>
				<button type="submit">
					<img
						src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Magnifying_glass_icon.svg/1200px-Magnifying_glass_icon.svg.png"
					/>
				</button>
			</form>
			<div class="weather">
				<img
					class="cloudy"
					id="weather--image"
				/>
				<h1 class="temperature"><span id="temperature">0</span>°C</h1>
				<h2
					class="city"
					id="city"
				></h2>
				<div class="side">
					<div class="main">
						<div class="humidityimg">
							<img
								src="https://cdn.iconscout.com/icon/free/png-512/free-humidity-4216073-3490829.png?f=webp&w=256"
							/>
							<div>
								<p><span id="humidity-value"></span>%</p>
								<p>Humidity</p>
							</div>
						</div>
					</div>
					
						<div class="wind">
							<img
								src="https://cdn-icons-png.flaticon.com/512/3741/3741046.png"
							/>
							<div>
								<p><span id="wind-speed-value"></span> Km/h</p>
								<p>Wind Speed</p>
							</div>
						</div>
						<div class="pressure">
							<img
								class="pressure-img"
								src="https://cdn-icons-png.flaticon.com/128/3656/3656766.png"
							/>
							<div class="pressuretext">
								<p><span id="pressure"></span> Pa</p>
								<p>Pressure</p>
							</div>
						</div>
					
				</div>
			</div>
		</div>
		
		<h5>Past Weather Data:</h5>
<?php
function get_conn()
{
    $host = 'sql105.infinityfree.com';
    $username = 'if0_35979858';
    $password = 'MhotBBxbzXW';
    $dbname = 'if0_35979858_WeatherData';

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function db_insert($conn, $weatherData)
{
	$timestamp = $weatherData['dt'];
   	$date = date('Y-m-d', $timestamp);
    $time = date('H:i', $timestamp);
    $temperatureKelvin = $weatherData['main']['temp'];
    $temperatureCelsius = $temperatureKelvin - 273.15;
    $humidity = $weatherData['main']['humidity'];
    $windSpeed = $weatherData['wind']['speed'];

    $insertDataQuery = $conn->prepare("INSERT INTO WeatherData (date, time, temperature, humidity, wind_speed) 
                                      VALUES (?, ?, ?, ?, ?)");
    $insertDataQuery->bind_param("sssss", $date, $time, $temperatureCelsius, $humidity, $windSpeed);
    $insertDataQuery->execute();
}

try {
    // Get database connection
    $conn = get_conn();

    // Fetch weather data from OpenWeatherMap API
    $city = isset($_POST['city']) ? $_POST['city'] : 'Jamnagar'; 
    $apiKey = '39348318b634c47da2475d4664f4641f';
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&APPID=$apiKey";
    $response = file_get_contents($apiUrl);

    if ($response === FALSE) {
        die('Error occurred while fetching weather data.');
    }

    // Decode the JSON response
    $weatherData = json_decode($response, true);

    if (!empty($weatherData['dt'])) {
        // Insert data into the 'weather_data' table
        db_insert($conn, $weatherData);
    }

    // Display latest 7 unique dates' weather data using prepared statement
    $selectDataQuery = "SELECT DISTINCT date FROM WeatherData ORDER BY date DESC LIMIT 7";
    $stmt = $conn->prepare($selectDataQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='past'>";

    while ($row = $result->fetch_assoc()) {
        $date = $row['date'];

        // Fetch data for the selected date using prepared statement
        $selectDataForDateQuery = "SELECT * FROM WeatherData WHERE date LIKE ? ORDER BY time";
        $stmtForDate = $conn->prepare($selectDataForDateQuery);
        $stmtForDate->bind_param("s", $date);
        $stmtForDate->execute();
        $resultForDate = $stmtForDate->get_result();
        $rowForDate = $resultForDate->fetch_assoc();
		$dateObj = new DateTime($date);
		$day = $dateObj->format('l');
        $time = $rowForDate['time'];
        $temperature = $rowForDate['temperature'];
        $humidity = $rowForDate['humidity'];
        $windSpeed = $rowForDate['wind_speed'];

        echo "<div class='day'>
                <h3 class='date'>$date ($day)</h3>
                <p class='temperature'>Temperature: $temperature °C</p>
               <div class='humidity'><img class='php-humidity'
                                src='https://cdn.iconscout.com/icon/free/png-512/free-humidity-4216073-3490829.png?f=webp&w=256'
                            /> <p>Humidity: $humidity%</p></div>
               <div class='wind-speed'><img class='php-wind'
                                src='https://cdn-icons-png.flaticon.com/512/3741/3741046.png'
                            /> <p>Wind Speed: $windSpeed Km/h</p></div>
              </div>";
    }

    // Include the initial weather data in the HTML
    echo "<script>const initialWeatherData = " . json_encode($weatherData) . ";</script>";

    echo "</div>";

    // Close the connections
    $stmtForDate->close();
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<div>
			<footer class="footer">
				&copy; Rochak Maharjan (Student ID:2408877)
			</footer>
		</div>
		<script src="./script.js"></script>
	</body>
</html>
