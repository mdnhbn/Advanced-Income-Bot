<?php

namespace Modules;

use Core\Database;

class Wallet {
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }
    
    public function processDeposit($amount, $user_id) {
        // Add deposited amount
        $sql = "UPDATE users SET balance = balance + ? WHERE user_id = ?";
        Database::query($sql, [$amount, $user_id]);
        
        // Check and add deposit bonus
        $bonus_threshold = Admin::getSetting('deposit_bonus_threshold', 500); // e.g., 500 points
        $bonus_percent = Admin::getSetting('deposit_bonus_percent', 10); // e.g., 10%

        if ($amount >= $bonus_threshold) {
            $bonus = ($amount * $bonus_percent) / 100;
            Database::query("UPDATE users SET balance = balance + ? WHERE user_id = ?", [$bonus, $user_id]);
            // Notify user about the bonus received
        }
    }

    public function requestWithdrawal($amount, $wallet_address) {
        $min_withdraw = Admin::getSetting('min_withdraw_amount', 1000);
        $stmt = Database::query("SELECT balance FROM users WHERE user_id = ?", [$this->user_id]);
        $balance = $stmt->fetchColumn();

        if ($amount >= $min_withdraw && $balance >= $amount) {
            // Deduct from balance and log the request for admin approval
            Database::query("UPDATE users SET balance = balance - ? WHERE user_id = ?", [$amount, $this->user_id]);
            
            $sql = "INSERT INTO withdrawals (user_id, amount, wallet_address, status) VALUES (?, ?, ?, 'pending')";
            Database::query($sql, [$this->user_id, $amount, $wallet_address]);
            
            // Notify user and admin
        } else {
            // Send error message (insufficient balance or below minimum)
        }
    }
}
