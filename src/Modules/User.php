<?php

namespace Modules;

use Core\Database;
use Core\Language;

class User {
    private $user_id;
    private $chat_id;
    private $db;

    public function __construct($user_id, $chat_id) {
        $this->user_id = $user_id;
        $this->chat_id = $chat_id;
        $this->db = Database::getInstance();
    }

    public function initialize() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->registerNewUser();
        }
    }
    
    private function registerNewUser() {
        $sql = "INSERT INTO users (user_id, chat_id, language, status, join_date) VALUES (?, ?, 'en', 'pending', NOW())";
        Database::query($sql, [$this->user_id, $this->chat_id]);
        // Show language selection first
        $this->showLanguageSelection();
    }
    
    public function handleStart() {
        $user = $this->getUserData();
        if ($user['status'] === 'pending') {
            $this->requestChannelVerification();
        } elseif ($user['status'] === 'active') {
             $this->sendMessage(Language::get('welcome_back'), $this->getMainMenu());
        } else { // Banned
             $this->sendMessage(Language::get('account_banned'));
        }
    }
    
    public function setLanguage($lang_code) {
        Database::query("UPDATE users SET language = ? WHERE user_id = ?", [$lang_code, $this->user_id]);
        Language::setLanguage($lang_code);
        $this->sendMessage(Language::get('language_updated'));
        $this->handleStart(); // Show main menu after language selection
    }

    public function getLanguage() {
        $stmt = $this->db->prepare("SELECT language FROM users WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt->fetchColumn() ?: 'en';
    }

    // Helper to send message (could be moved to a core helper class)
    private function sendMessage($text, $keyboard = null) {
        // This function needs to be implemented or called from Bot class
    }
    
    private function getUserData() {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt->fetch();
    }
    
    private function showLanguageSelection() {
        $text = "Please select your language. / Пожалуйста, выберите ваш язык.";
        $keyboard = [
            [['text' => "🇬🇧 English", 'callback_data' => 'lang_en']],
            [['text' => "🇧🇩 বাংলা", 'callback_data' => 'lang_bn']],
            [['text' => "🇮🇳 हिन्दी", 'callback_data' => 'lang_hi']]
            // Add more languages here
        ];
        $this->sendMessage($text, $keyboard);
    }
    
    private function requestChannelVerification() {
        // Fetch channels from DB (managed by admin)
        // For now, let's assume we have a function for that
        $channels = Admin::getRequiredChannels();
        $text = Language::get('verify_channel_join_text');
        $keyboard = [];
        foreach($channels as $channel) {
            $keyboard[] = [['text' => "Join {$channel['name']}", 'url' => "https://t.me/{$channel['username']}"]];
        }
        $keyboard[] = [['text' => Language::get('button_verify_join'), 'callback_data' => 'user_verify_join']];
        $this->sendMessage($text, $keyboard);
    }
}
