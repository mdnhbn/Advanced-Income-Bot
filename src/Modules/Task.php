<?php

namespace Modules;

use Core\Database;
use Core\Language;

class Task {
    private $user_id;
    private $db;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->db = Database::getInstance();
    }

    public function showTaskMenu() {
        $text = Language::get('task_menu_text');
        $keyboard = [
            [['text' => Language::get('button_watch_video'), 'callback_data' => 'task_watch_video']],
            [['text' => Language::get('button_view_ad'), 'callback_data' => 'task_view_ad']],
            [['text' => Language::get('button_back_main'), 'callback_data' => 'main_menu']],
        ];
        // Send this menu to the user
    }
    
    public function handleCallback($data) {
        if ($data === 'task_watch_video') {
            $this->assignTask('video');
        } elseif ($data === 'task_view_ad') {
            $this->assignTask('ad');
        }
    }

    private function assignTask($task_type) {
        // Fetch an active task that the user has not completed and is not their own
        $sql = "SELECT * FROM tasks WHERE task_type = ? AND is_active = 1 AND (created_by != ? OR created_by IS NULL) ORDER BY RAND() LIMIT 1";
        $stmt = Database::query($sql, [$task_type, $this->user_id]);
        $task = $stmt->fetch();

        if ($task) {
            $taskId = $task['id'];
            $url = $task['url'];
            $timer = $task['timer'];

            $webapp_url = BOT_WEBAPP_URL . "/task_viewer.html?url=" . urlencode($url) . "&timer={$timer}&taskId={$taskId}";
            
            $text = Language::get('start_task_text');
            $keyboard = [[['text' => Language::get('button_start_task'), 'web_app' => ['url' => $webapp_url]]]];
            
            // Send message with the web app button
            // This needs to be called from the Bot class
        } else {
            // No tasks available message
        }
    }
    
    public function verifyTaskCompletion($taskData) {
        // This function is called when the web app sends data back
        $data = json_decode($taskData, true);
        if (isset($data['status']) && $data['status'] === 'completed') {
            $taskId = $data['taskId'];
            
            // Fetch task reward from DB
            $stmt = Database::query("SELECT reward FROM tasks WHERE id = ?", [$taskId]);
            $reward = $stmt->fetchColumn();

            if ($reward) {
                // Add points to user's balance
                Database::query("UPDATE users SET balance = balance + ? WHERE user_id = ?", [$reward, $this->user_id]);
                // Send success message
            }
        } else {
            // Handle failed task (e.g., increment warning count)
            Database::query("UPDATE users SET warnings = warnings + 1 WHERE user_id = ?", [$this->user_id]);
            // Check if warning count reached 5, then ban user.
        }
    }
}
