<?php

namespace Modules;

use Core\Database;

class AdPlatform {
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }
    
    public function showAdMenu() {
        // Show "Create Ad", "My Ads" buttons
    }
    
    public function createAd($adType, $url, $budget, $cpv) {
        // Check if user has enough balance
        $stmt = Database::query("SELECT balance FROM users WHERE user_id = ?", [$this->user_id]);
        $balance = $stmt->fetchColumn();
        
        if ($balance >= $budget) {
            // Deduct budget from user's balance
            Database::query("UPDATE users SET balance = balance - ? WHERE user_id = ?", [$budget, $this->user_id]);
            
            // Insert ad into 'tasks' table with 'is_active' = 0 (pending approval)
            $sql = "INSERT INTO tasks (task_type, url, reward, timer, is_active, created_by) VALUES (?, ?, ?, ?, 0, ?)";
            // The 'reward' here is the CPV, and timer is set by admin
            Database::query($sql, [$adType, $url, $cpv, 30, $this->user_id]);
            
            // Notify user that ad is pending approval
        } else {
            // Insufficient balance message
        }
    }
}
