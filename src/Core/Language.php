<?php
class Language {
    private static $strings = [];
    private static $lang_code = 'en';

    public static function setLanguage($lang_code) {
        self::$lang_code = $lang_code;
        $file_path = __DIR__ . '/../../lang/' . $lang_code . '.json';
        if (file_exists($file_path)) {
            self::$strings = json_decode(file_get_contents($file_path), true);
        } else {
            // Fallback to English if language file not found
            self::$strings = json_decode(file_get_contents(__DIR__ . '/../../lang/en.json'), true);
        }
    }

    public static function get($key) {
        return self::$strings[$key] ?? $key; // Return the key itself if not found
    }
}
