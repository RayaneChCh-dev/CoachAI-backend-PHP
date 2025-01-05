<?php

class Validation {
    public static function validateRegistration($data) {
        error_log("Validating registration data: " . print_r($data, true));
        
        if (!isset($data->name) || empty($data->name)) {
            error_log("Name validation failed");
            return false;
        }
        
        if (!isset($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            error_log("Email validation failed");
            return false;
        }
        
        if (!isset($data->password) || strlen($data->password) < 6) {
            error_log("Password validation failed");
            return false;
        }
        
        return true;
    }
}