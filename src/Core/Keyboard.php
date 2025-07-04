<?php
class Keyboard {
    public static function create(array $buttons) {
        return json_encode(['inline_keyboard' => $buttons]);
    }
}
