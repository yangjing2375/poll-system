<?php
class Env {
    private static $vars = [];

    public static function load($path = '.env') {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            self::$vars[trim($key)] = trim($value);
        }
    }

    public static function get($key, $default = null) {
        return isset(self::$vars[$key]) ? self::$vars[$key] : $default;
    }
}

Env::load(dirname(__DIR__) . '/.env');
?>
