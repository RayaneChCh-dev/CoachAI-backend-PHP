<?php

class Response {
    public static function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public static function error($message, $status = 400) {
        self::json(['error' => $message], $status);
    }
}