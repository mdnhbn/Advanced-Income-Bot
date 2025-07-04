<?php

namespace Modules;

use Core\Database;

class Security {
    // Relying on Telegram's unique user_id is the most effective way
    // to prevent multi-accounting on their platform. The database `users` table
    // already has a UNIQUE constraint on `user_id`, which will prevent duplicates.
    // The logic in `User.php` already handles new vs existing users.
    
    public static function banUser($user_id, $reason = "Violation of terms.") {
        Database::query("UPDATE users SET status = 'banned' WHERE user_id = ?", [$user_id]);
        // Send a notification to the user about the ban
        $text = sprintf(Language::get('you_are_banned_reason'), $reason);
        // Use a generic sender function since this is a static method
        self::sendMessageToUser($user_id, $text);
    }
    
    private static function sendMessageToUser($chat_id, $text) {
        $params = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        file_get_contents(API_URL . 'sendMessage?' . http_build_query($params));
    }
}
