<?php
session_start();
// Store user-selected units in session
$units = $_GET['units'] ?? ($_SESSION['units'] ?? 'metric');
$_SESSION['units'] = $units;
$unitLabel = $units == 'metric' ? '°C' : '°F';
$windUnit = $units == 'metric' ? 'm/s' : 'mph';

// Check if form is submitted
if (isset($_GET['city'])) {
    $city = urlencode(trim(preg_replace('/,.*/', '', $_GET['city'])));
    $apiKey = "f2a0dd0e25fb5b4ce4c6b00cc4f6aff4";
    $error = '';

    // Current weather
    $currentUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=$units";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $currentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    $currentData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    error_log("cURL Debug for current weather ($city): $verboseLog", 3, "weather_errors.log");

    if ($currentData && $httpCode == 200) {
        $weather = json_decode($currentData, true);
        if ($weather['cod'] == 200) {
            $cityName = $weather['name'];
            $temperature = $weather['main']['temp'];
            $description = $weather['weather'][0]['description'];
            $humidity = $weather['main']['humidity'];
            $windSpeed = $weather['wind']['speed'];
            $icon = $weather['weather'][0]['icon'];

            $windThreshold = $units == 'metric' ? 15 : 33.5;
            $alert = '';
            if ($windSpeed > $windThreshold) {
                $alert = "Warning: High winds detected! Take caution.";
            } elseif (stripos($description, 'storm') !== false) {
                $alert = "Warning: Storm conditions detected!";
            }
        } else {
            $error = "Invalid response from API: " . ($weather['message'] ?? 'Unknown error');
        }
    } else {
        $error = "Unable to fetch current weather data. HTTP Code: $httpCode.";
    }

    // 5-day forecast
    $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=$city&appid=$apiKey&units=$units";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $forecastUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    $forecastData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    error_log("cURL Debug for forecast ($city): $verboseLog", 3, "weather_errors.log");

    if ($forecastData && $httpCode == 200) {
        $forecast = json_decode($forecastData, true);
        if ($forecast['cod'] == "200") {
            $forecastList = [];
            $currentDate = '';
            foreach ($forecast['list'] as $item) {
                $date = date('Y-m-d', $item['dt']);
                if ($date !== $currentDate && date('H', $item['dt']) >= 12 && count($forecastList) < 5) {
                    $forecastList[] = $item;
                    $currentDate = $date;
                }
            }
        } else {
            $error .= " Invalid forecast response: " . ($forecast['message'] ?? 'Unknown error');
        }
    } else {
        $error .= " Unable to fetch forecast data. HTTP Code: $httpCode.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherWise - Weather</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <div class="logo">WeatherWise</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="weather.php" class="active">Weather</a></li>
            </ul>
            <button id="theme-toggle" aria-label="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
        </nav>
    </header>

    <main>
        <section class="weather-section">
            <h1>Weather Prediction</h1>
            <form method="GET" id="weather-form">
                <input type="text" name="city" placeholder="Enter city name (e.g., Chennai)" required aria-label="City name">
                <select name="units" aria-label="Temperature units">
                    <option value="metric" <?php echo $units == 'metric' ? 'selected' : ''; ?>>Celsius (°C)</option>
                    <option value="imperial" <?php echo $units == 'imperial' ? 'selected' : ''; ?>>Fahrenheit (°F)</option>
                </select>
                <button type="submit">Get Weather</button>
            </form>
            <p class="note">Note: Enter only the city name (e.g., Chennai, not Chennai, Tamil Nadu).</p>

            <div id="loading-spinner" class="loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Loading weather data...
            </div>

            <?php if (isset($cityName)): ?>
                <div class="weather-box fade-in">
                    <h2>Current Weather in <?php echo htmlspecialchars($cityName); ?></h2>
                    <img src="https://openweathermap.org/img/wn/<?php echo $icon; ?>@2x.png" alt="Weather icon for <?php echo $description; ?>">
                    <p>Temperature: <?php echo $temperature; ?><?php echo $unitLabel; ?></p>
                    <p>Description: <?php echo ucfirst($description); ?></p>
                    <p>Humidity: <?php echo $humidity; ?>%</p>
                    <p>Wind Speed: <?php echo $windSpeed; ?> <?php echo $windUnit; ?></p>
                    <?php if ($alert): ?>
                        <p class="alert"><?php echo htmlspecialchars($alert); ?></p>
                    <?php endif; ?>
                </div>
                <?php if (isset($forecastList)): ?>
                    <div class="forecast-box fade-in">
                        <h2>5-Day Forecast</h2>
                        <div class="forecast-container">
                            <?php foreach ($forecastList as $item): ?>
                                <details class="forecast-item">
                                    <summary><strong><?php echo date('D, M d', $item['dt']); ?></strong></summary>
                                    <img src="https://openweathermap.org/img/wn/<?php echo $item['weather'][0]['icon']; ?>@2x.png" alt="Forecast icon for <?php echo $item['weather'][0]['description']; ?>">
                                    <p>Temp: <?php echo $item['main']['temp']; ?><?php echo $unitLabel; ?></p>
                                    <p><?php echo ucfirst($item['weather'][0]['description']); ?></p>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </section>

        <section class="chatbot-section">
            <h2>Ask Our Weather Assistant</h2>
            <div id="chat-container">
                <div id="chat-messages"></div>
                <div class="chat-input">
                    <input type="text" id="chat-input" placeholder="Ask about weather or anything else..." aria-label="Chat input">
                    <button id="send-message" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 WeatherWise. Powered by xAI and OpenWeatherMap.</p>
    </footer>

    <script src="chatbot.js"></script>
    <script>
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            themeToggle.innerHTML = document.body.classList.contains('dark-mode') 
                ? '<i class="fas fa-sun"></i>' 
                : '<i class="fas fa-moon"></i>';
        });

        // Loading spinner for weather form
        const weatherForm = document.getElementById('weather-form');
        const loadingSpinner = document.getElementById('loading-spinner');
        weatherForm.addEventListener('submit', () => {
            loadingSpinner.style.display = 'block';
        });
    </script>
</body>
</html>