<?php

namespace Modules;

use Core\Database;
use Core\Language;
use Core\Keyboard;

class Admin {
    public static function showPanel($user_id) {
        if ($user_id != ADMIN_ID) return;

        $text = Language::get('admin_panel_welcome');
        $keyboard = [
            [['text' => Language::get('admin_button_stats'), 'callback_data' => 'admin_stats']],
            [['text' => Language::get('admin_button_users'), 'callback_data' => 'admin_users']],
            [['text' => Language::get('admin_button_settings'), 'callback_data' => 'admin_settings']],
            [['text' => Language::get('admin_button_broadcast'), 'callback_data' => 'admin_broadcast']],
            [['text' => Language::get('admin_button_tasks'), 'callback_data' => 'admin_tasks']],
            [['text' => Language::get('admin_button_ads'), 'callback_data' => 'admin_ads_pending']],
        ];
        self::sendMessage($user_id, $text, $keyboard);
    }
    
    public static function handleCallback($user_id, $data) {
        if ($user_id != ADMIN_ID) return;
        
        // Example: Handling user management
        if ($data === 'admin_users') {
            $text = "Enter a User ID to manage or use buttons below.";
            // Add buttons for searching, viewing banned users, etc.
            self::sendMessage($user_id, $text);
        }
        
        // Add more handlers for other admin actions
    }

    public static function isMaintenanceActive() {
        $stmt = Database::query("SELECT setting_value FROM admin_settings WHERE setting_key = 'maintenance_mode'");
        $result = $stmt->fetchColumn();
        return $result === 'on';
    }

    public static function getRequiredChannels() {
        // This should fetch from the 'admin_settings' or a dedicated 'channels' table
        // For now, returning a static list. Admin panel will update this.
        return [
            ['name' => 'Channel 1', 'username' => 'telegram'],
            ['name' => 'Channel 2', 'username' => 'durov'],
        ];
    }
    
    // Helper to send message
    private static function sendMessage($chat_id, $text, $keyboard = null) {
        $params = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($keyboard) {
            $params['reply_markup'] = Keyboard::create($keyboard);
        }
        file_get_contents(API_URL . 'sendMessage?' . http_build_query($params));
    }
}
