<?php

namespace Modules;

use Core\Database;
use Core\Language;

class Wallet {
    private $user_id;
    private $db;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->db = Database::getInstance();
    }
    
    public function showWalletMenu() {
        $stmt = Database::query("SELECT balance FROM users WHERE user_id = ?", [$this->user_id]);
        $balance = $stmt->fetchColumn();

        $text = sprintf(Language::get('wallet_menu_text'), $balance);
        $keyboard = [
            [['text' => Language::get('button_deposit'), 'callback_data' => 'wallet_deposit']],
            [['text' => Language::get('button_withdraw'), 'callback_data' => 'wallet_withdraw']],
            [['text' => Language::get('button_back_main'), 'callback_data' => 'main_menu']],
        ];
        // Send this menu
    }
    
    public function handleCallback($data) {
        if ($data === 'wallet_deposit') {
            $text = Language::get('deposit_instruction_text');
            // Show deposit options/instructions
            // This would typically involve showing payment addresses or links
        } elseif ($data === 'wallet_withdraw') {
            // Logic for withdrawal request
        }
    }

    public function addDepositBonus($amount) {
        // Fetch bonus percentage from admin settings
        $stmt = Database::query("SELECT setting_value FROM admin_settings WHERE setting_key = 'deposit_bonus_percentage'");
        $bonus_percent = $stmt->fetchColumn();

        if ($bonus_percent > 0) {
            $bonus_amount = ($amount * $bonus_percent) / 100;
            Database::query("UPDATE users SET balance = balance + ? WHERE user_id = ?", [$bonus_amount, $this->user_id]);
            // Notify user about the bonus
        }
    }
}
