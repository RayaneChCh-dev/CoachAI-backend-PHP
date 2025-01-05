<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '../../vendor/autoload.php';
class JwtHandler {
    private static $secret;
    private static $expiration = 3600;

    // Static initializer
    public static function init() {
        self::$secret = getenv('JWT_SECRET');
    }

    public static function generate($user_id) {
        $payload = [
            'user_id' => $user_id,
            'exp' => time() + self::$expiration
        ];
        
        return JWT::encode($payload, self::$secret, 'HS256');
    }

    public static function validate($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getUserIdFromToken($token) {
        $decoded = self::validate($token);
        return $decoded->user_id;
    }

    public static function getUserId($token) {
        return self::getUserIdFromToken($token);
    }
}


JwtHandler::init();
