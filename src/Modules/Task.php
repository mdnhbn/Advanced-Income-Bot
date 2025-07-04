<?php

namespace Modules;

use Core\Database;
use Core\Language;
use Core\Keyboard;

class Task {
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }

    public function assignTask($task_type) {
        $admin_timer = Admin::getSetting('task_timer', 30); // Get timer from admin settings, default 30s

        // Fetch a task that is active, not created by the current user
        $sql = "SELECT * FROM tasks WHERE task_type = ? AND is_active = 1 AND (created_by IS NULL OR created_by != ?) ORDER BY RAND() LIMIT 1";
        $stmt = Database::query($sql, [$task_type, $this->user_id]);
        $task = $stmt->fetch();

        if ($task) {
            $webapp_url = BOT_WEBAPP_URL . "/task_viewer.html?url=" . urlencode($task['url']) . "&timer={$admin_timer}&taskId={$task['id']}";
            
            $text = Language::get('start_task_text');
            $keyboard = [[['text' => Language::get('button_start_task'), 'web_app' => ['url' => $webapp_url]]]];
            
            // Send message with the web app button (via Bot class)
        } else {
            // No tasks available message
        }
    }
    
    public function verifyTaskCompletion($taskData) {
        $data = json_decode($taskData, true);
        if (isset($data['status'], $data['taskId']) && $data['status'] === 'completed') {
            // Check if user has already completed this task to prevent replay attacks
            // (Requires a `completed_tasks` table)
            
            $stmt = Database::query("SELECT reward FROM tasks WHERE id = ?", [$data['taskId']]);
            $reward = $stmt->fetchColumn();

            if ($reward) {
                Database::query("UPDATE users SET balance = balance + ? WHERE user_id = ?", [$reward, $this->user_id]);
                // Reset user's warning count
                Database::query("UPDATE users SET warnings = 0 WHERE user_id = ?", [$this->user_id]);
                // Send success message
            }
        } else {
            Database::query("UPDATE users SET warnings = warnings + 1 WHERE user_id = ?", [$this->user_id]);
            $stmt = Database::query("SELECT warnings FROM users WHERE user_id = ?", [$this->user_id]);
            $warnings = $stmt->fetchColumn();
            
            if ($warnings >= 5) {
                Security::banUser($this->user_id, "Banned for failing tasks repeatedly.");
            } else {
                // Send warning message
            }
        }
    }
}
