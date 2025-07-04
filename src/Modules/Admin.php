<?php

namespace Modules;

use Core\Database;
use Core\Language;
use Core\Keyboard;

class Admin {
    // This class will be extensive. Here is a foundational structure.
    
    public static function showPanel($user_id) {
        if ($user_id != ADMIN_ID) return;

        $text = Language::get('admin_panel_welcome');
        $keyboard = [
            [['text' => Language::get('admin_button_stats'), 'callback_data' => 'admin_stats'], ['text' => Language::get('admin_button_users'), 'callback_data' => 'admin_users_main']],
            [['text' => Language::get('admin_button_settings'), 'callback_data' => 'admin_settings_main'], ['text' => Language::get('admin_button_broadcast'), 'callback_data' => 'admin_broadcast_start']],
            [['text' => Language::get('admin_button_tasks'), 'callback_data' => 'admin_tasks_main'], ['text' => Language::get('admin_button_ads'), 'callback_data' => 'admin_ads_pending']],
            [['text' => Language::get('admin_button_payments'), 'callback_data' => 'admin_payments_main']]
        ];
        self::sendMessage($user_id, $text, $keyboard);
    }

    public static function handleCallback($user_id, $data) {
        if ($user_id != ADMIN_ID) return;
        
        // This will be a router for all admin actions.
        // For example:
        if ($data === 'admin_settings_main') {
            self::showBotSettings($user_id);
        }
        // ... other handlers will be built here
    }

    public static function showBotSettings($user_id) {
        // Fetch current settings from DB
        $maintenance = self::getSetting('maintenance_mode', 'Off');
        // ... fetch other settings

        $text = "⚙️ Bot Settings Mode";
        $keyboard = [
            [['text' => "Maintenance: " . ($maintenance == 'on' ? '✅ On' : '❌ Off'), 'callback_data' => 'admin_toggle_maintenance']],
            // Add other toggle buttons here
            [['text' => "Set Task Timer", 'callback_data' => 'admin_set_timer']],
            [['text' => "⬅️ Back to Admin Panel", 'callback_data' => 'admin_panel_main']]
        ];
        self::sendMessage($user_id, $text, $keyboard);
    }
    
    // Function to get/set settings from the database
    public static function getSetting($key, $default = null) {
        $stmt = Database::query("SELECT setting_value FROM admin_settings WHERE setting_key = ?", [$key]);
        return $stmt->fetchColumn() ?: $default;
    }

    public static function setSetting($key, $value) {
        $sql = "INSERT INTO admin_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?";
        Database::query($sql, [$key, $value, $value]);
    }

    // A helper to send messages within the class
    private static function sendMessage($chat_id, $text, $keyboard = null) {
        $params = ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($keyboard) {
            $params['reply_markup'] = Keyboard::create($keyboard);
        }
        file_get_contents(API_URL . 'sendMessage?' . http_build_query($params));
    }
}
