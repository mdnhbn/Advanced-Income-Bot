<?php

namespace Core;

use Modules\User;
use Modules\Admin;
use Modules\Task;
use Modules\Wallet;

class Bot {
    private $update;
    private $user_id;
    private $chat_id;
    private $text;
    private $data;
    private $is_callback;

    public function __construct(array $update) {
        $this->update = $update;
        $this->parseUpdate();
    }

    private function parseUpdate() {
        if (isset($this->update['message'])) {
            $message = $this->update['message'];
            $this->user_id = $message['from']['id'];
            $this->chat_id = $message['chat']['id'];
            $this->text = $message['text'] ?? '';
            $this->is_callback = false;
        } elseif (isset($this->update['callback_query'])) {
            $callback = $this->update['callback_query'];
            $this->user_id = $callback['from']['id'];
            $this->chat_id = $callback['message']['chat']['id'];
            $this->data = $callback['data'];
            $this->text = '';
            $this->is_callback = true;
        }
    }

    public function run() {
        if (!$this->user_id) return;

        // Initialize user module to handle registration and language
        $userModule = new User($this->user_id, $this->chat_id);
        $userModule->initialize();
        Language::setLanguage($userModule->getLanguage());

        // Check for maintenance mode (except for admin)
        // This setting would be fetched from the database, managed by Admin module
        // For now, let's assume it's a function in the Admin module
        if (Admin::isMaintenanceActive() && $this->user_id != ADMIN_ID) {
            $this->sendMessage(Language::get('maintenance_message'));
            return;
        }

        // Route commands and callbacks
        $this->routeRequest();
    }

    private function routeRequest() {
        $userModule = new User($this->user_id, $this->chat_id);
        $taskModule = new Task($this->user_id);
        $walletModule = new Wallet($this->user_id);

        if ($this->is_callback) {
            // Handle callback data routing
            $parts = explode('_', $this->data);
            $action = $parts[0];

            switch ($action) {
                case 'lang':
                    $userModule->setLanguage($parts[1]);
                    break;
                case 'task':
                    $taskModule->handleCallback($this->data);
                    break;
                case 'wallet':
                    $walletModule->handleCallback($this->data);
                    break;
                case 'admin':
                    Admin::handleCallback($this->user_id, $this->data);
                    break;
                default:
                    $this->sendMessage(Language::get('main_menu_text'), $this->getMainMenu());
            }
        } else {
            // Handle text command routing
            switch ($this->text) {
                case '/start':
                    $userModule->handleStart();
                    break;
                case '/admin':
                    Admin::showPanel($this->user_id);
                    break;
                default:
                    // Could be a reply to a bot message (e.g., entering amount)
                    // We need a state machine for this, which we can implement later.
                    $this->sendMessage(Language::get('unknown_command'));
            }
        }
    }

    private function sendMessage($text, $keyboard = null) {
        $params = [
            'chat_id' => $this->chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if ($keyboard) {
            $params['reply_markup'] = Keyboard::create($keyboard);
        }
        $this->apiRequest('sendMessage', $params);
    }

    private function getMainMenu() {
        return [
            [['text' => Language::get('button_earn'), 'callback_data' => 'task_menu']],
            [['text' => Language::get('button_wallet'), 'callback_data' => 'wallet_menu']],
            [['text' => Language::get('button_ads'), 'callback_data' => 'ads_menu']],
            [['text' => Language::get('button_profile'), 'callback_data' => 'user_profile']],
        ];
    }
    
    private function apiRequest($method, $parameters) {
        $url = API_URL . $method;
        if (!empty($parameters)) {
            $url .= "?" . http_build_query($parameters);
        }
        return file_get_contents($url);
    }
}
