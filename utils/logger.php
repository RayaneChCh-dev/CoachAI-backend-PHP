<?php

class Logger {
    public static function log($message, $type = 'INFO') {
        $log_file = __DIR__ . '/../logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$type] $message\n";
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}