<?php

// Bot Configuration - Set these in your Render.com Environment Variables
define('BOT_TOKEN', getenv('BOT_TOKEN'));
define('ADMIN_ID', getenv('ADMIN_ID'));
define('BOT_WEBAPP_URL', getenv('BOT_WEBAPP_URL')); // e.g., https://your-service.onrender.com/public/webapp

// Database Configuration - Set these in your Render.com Environment Variables
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// API URL
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
