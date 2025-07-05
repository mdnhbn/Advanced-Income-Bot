<?php

// Set error reporting for debugging during development
// In production, you might want to turn this off and rely on logs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the request is from Telegram via POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Bot is running. Please access via Telegram.";
    exit;
}

// Define the base path for easier file inclusion
define('BASE_PATH', dirname(__DIR__));

// Require the main configuration and class files
require_once BASE_PATH . '/config/settings.php';
require_once BASE_PATH . '/src/Core/Database.php';
require_once BASE_PATH . '/src/Core/Language.php';
require_once BASE_PATH . '/src/Core/Keyboard.php';
require_once BASE_PATH . '/src/Core/Bot.php';
require_once BASE_PATH . '/src/Modules/User.php';
require_once BASE_PATH . '/src/Modules/Admin.php';
// ... include other modules as needed

use Core\Bot;

// Get the raw POST data from Telegram
$update_json = file_get_contents('php://input');
$update = json_decode($update_json, true);

// If the update is valid, process it
if ($update) {
    try {
        $bot = new Bot($update);
        $bot->run();
        // Respond to Telegram to acknowledge receipt of the update
        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        // Log any exception that occurs during processing
        error_log("Critical Error in Bot Execution: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
    }
} else {
    // If the POST data is empty or invalid JSON
    http_response_code(400); // Bad Request
    error_log("Invalid or empty update received.");
}
