<?php

namespace Modules;

use Core\Database;

class AdPlatform {
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }
    
    public function createAd($adType, $url, $budget, $cpv) {
        // Fetch user balance
        $stmt = Database::query("SELECT balance FROM users WHERE user_id = ?", [$this->user_id]);
        $balance = $stmt->fetchColumn();

        if ($balance >= $budget) {
            // Deduct points
            Database::query("UPDATE users SET balance = balance - ? WHERE user_id = ?", [$budget, $this->user_id]);

            // Add ad to the tasks table, pending approval (is_active = 0)
            $task_type = ($adType === 'youtube') ? 'video' : 'ad';
            $timer = Admin::getSetting('default_ad_timer', 30); // Default timer for user ads

            $sql = "INSERT INTO tasks (task_type, url, reward, timer, is_active, created_by) VALUES (?, ?, ?, ?, 0, ?)";
            Database::query($sql, [$task_type, $url, $cpv, $timer, $this->user_id]);
            
            // Notify user: "Your ad has been submitted for review."
        } else {
            // Notify user: "Insufficient balance. Please deposit points to create an ad."
        }
    }
}
