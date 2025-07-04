<?php

// Autoload all classes from src/
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/../config/settings.php';

use Core\Bot;

// Get update from Telegram
$update = json_decode(file_get_contents('php://input'), true);

if ($update) {
    try {
        $bot = new Bot($update);
        $bot->run();
        http_response_code(200);
    } catch (Exception $e) {
        // Log critical errors
        error_log("Fatal Error: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
    }
} else {
    echo "Bot is running. Access via Telegram.";
}
