<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherWise - Home</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav>
            <div class="logo">WeatherWise</div>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="weather.php">Weather</a></li>
            </ul>
            <button id="theme-toggle" aria-label="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
        </nav>
    </header>

    <main>
        <section class="hero fade-in">
            <h1>Welcome to WeatherWise</h1>
            <p>Get accurate weather forecasts and chat with our AI assistant for weather insights or any questions!</p>
            <a href="weather.php" class="cta-button">Check Weather Now</a>
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
    </script>
</body>
</html>