<?php
require_once '../../utils/cors.php';    
enableCORS();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../utils/jwt.php';
require_once '../../utils/response.php';
require __DIR__ . '../../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    Response::error('Missing required fields', 400);
}

$database = new Database();
$db = $database->connect();
$user = new User($db);

$user_data = $user->findByEmail($data->email);

if (!$user_data || !password_verify($data->password, $user_data['password'])) {
    Response::error('Invalid credentials', 401);
}

$token = JwtHandler::generate($user_data['id']);

Response::json([
    'token' => $token,
    'user' => [
        'id' => $user_data['id'],
        'name' => $user_data['name'],
        'email' => $user_data['email']
    ]
], 200);
