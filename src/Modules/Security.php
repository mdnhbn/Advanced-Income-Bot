<?php

namespace Modules;

use Core\Database;

class Security {
    public static function checkMultiAccount($user_id, $device_fingerprint = null) {
        // Telegram user_id is unique by nature, which is the primary check.
        // A more advanced check could involve looking for patterns from device_fingerprint
        // or IP address, but since VPN is allowed, IP is not reliable.
        
        $stmt = Database::query("SELECT COUNT(*) FROM users WHERE user_id = ?", [$user_id]);
        $count = $stmt->fetchColumn();
        
        // If count > 0, it's an existing user, not a multi-account.
        // The real check should happen at registration based on more data if available.
        // For now, relying on unique user_id is the most straightforward approach.
        return false; // Placeholder
    }
    
    public static function banUser($user_id, $reason = "Violation of terms.") {
        Database::query("UPDATE users SET status = 'banned' WHERE user_id = ?", [$user_id]);
        // Send a notification to the user about the ban
    }
}
