<?php
require_once 'env.php';

class Logger {
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';

    private static $levels = ['debug', 'info', 'warning', 'error'];

    public static function debug($message, $context = []) {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    public static function info($message, $context = []) {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    public static function warning($message, $context = []) {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    public static function error($message, $context = []) {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    private static function log($level, $message, $context = []) {
        $logLevel = Env::get('LOG_LEVEL', 'debug');
        $levelIndex = array_search($level, self::$levels);
        $logLevelIndex = array_search($logLevel, self::$levels);

        if ($levelIndex < $logLevelIndex) {
            return;
        }

        $logPath = Env::get('LOG_PATH', 'logs/');
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }

        $date = date('Y-m-d');
        $filename = "{$logPath}app_{$date}.log";
        $timestamp = date('Y-m-d H:i:s');

        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";

        file_put_contents($filename, $logLine, FILE_APPEND);
    }
}
?>
