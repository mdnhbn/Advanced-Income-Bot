<?php

namespace Core;

use Modules\User;
use Modules\Admin;
// ... other use statements

class Bot {
    private $update;
    private $user_id;
    private $chat_id;
    private $text;
    private $data;
    private $is_callback;
    private $message_id;

    public function __construct(array $update) {
        $this->update = $update;
        if (!$this->parseUpdate()) {
            throw new \Exception("Invalid update structure.");
        }
    }

    private function parseUpdate() {
        if (isset($this->update['message'])) {
            $message = $this->update['message'];
            $this->user_id = $message['from']['id'] ?? null;
            $this->chat_id = $message['chat']['id'] ?? null;
            $this->message_id = $message['message_id'] ?? null;
            $this->text = $message['text'] ?? '';
            $this->is_callback = false;
        } elseif (isset($this->update['callback_query'])) {
            $callback = $this->update['callback_query'];
            $this->user_id = $callback['from']['id'] ?? null;
            $this->chat_id = $callback['message']['chat']['id'] ?? null;
            $this->message_id = $callback['message']['message_id'] ?? null;
            $this->data = $callback['data'] ?? '';
            $this->text = '';
            $this->is_callback = true;
        } else {
            return false; // Not a message or callback we can handle
        }
        return $this->user_id && $this->chat_id;
    }

    public function run() {
        // Basic check to prevent processing loops
        if (empty($this->user_id)) {
            error_log("No user_id found in update.");
            return;
        }

        // Initialize User Module
        $userModule = new User($this->user_id, $this->chat_id);
        $is_new_user = $userModule->initialize();
        Language::setLanguage($userModule->getLanguage());

        // Handle text commands
        if (!$this->is_callback) {
            if ($this->text === '/start') {
                $userModule->handleStart();
            } else if ($this->text === '/admin' && $this->user_id == ADMIN_ID) {
                Admin::showPanel($this->user_id);
            } else {
                // Default response for unknown text
                 $this->sendMessage(Language::get('unknown_command'));
            }
        } 
        // Handle callback queries
        else {
            $this->answerCallbackQuery(); // Acknowledge the button press
            $parts = explode('_', $this->data);
            $action = $parts[0];
            
            if ($action === 'lang') {
                $userModule->setLanguage($parts[1]);
                $userModule->handleStart(); // Show main menu after language change
            }
            // Add other callback routing here
        }
    }
    
    // Send a message to the user
    private function sendMessage($text, $keyboard = null) {
        $params = ['chat_id' => $this->chat_id, 'text' => $text, 'parse_mode' => 'HTML'];
        if ($keyboard) {
            $params['reply_markup'] = Keyboard::create($keyboard);
        }
        $this->apiRequest('sendMessage', $params);
    }
    
    // Acknowledge a callback query to stop the loading icon on the button
    private function answerCallbackQuery() {
        $this->apiRequest('answerCallbackQuery', ['callback_query_id' => $this->update['callback_query']['id']]);
    }
    
    private function apiRequest($method, $parameters) {
        $url = API_URL . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
