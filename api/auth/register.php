<?php
require_once '../../utils/cors.php';
enableCORS();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../utils/response.php';
require_once '../../utils/validation.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

try {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!$data) {
        Response::error('Invalid JSON data');
    }

    if (!Validation::validateRegistration($data)) {
        Response::error('Invalid input data');
    }

    $database = new Database();
    $db = $database->connect();
    $user = new User($db);

    if ($user->findByEmail($data->email)) {
        Response::error('Email already exists', 409);
    }

    if ($user->create($data->name, $data->email, $data->password)) {
        Response::json([
            'message' => 'User created successfully',
            'user' => [
                'name' => $data->name,
                'email' => $data->email
            ]
        ], 201);
    } else {
        Response::error('Unable to create user', 500);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}

$database = new Database();
if (!$database->testConnection()) {
    Response::error('Database connection failed', 500);
}

?>