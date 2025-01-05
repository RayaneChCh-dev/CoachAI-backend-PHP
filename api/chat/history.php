<?php

header('Content-Type: application/json');
require_once '../../models/Chat.php';

class ChatHistoryController {
    private $chat;

    public function __construct() {
        $this->chat = new Chat();
    }

    public function handleRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Method not allowed', 405);
            }

            // Validate required parameters
            if (!isset($_GET['coachId'])) {
                throw new Exception('Missing coach ID', 400);
            }

            // Validate authentication
            $authToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (!$this->validateAuth($authToken)) {
                throw new Exception('Unauthorized', 401);
            }

            $userId = $this->getUserIdFromToken($authToken);
            $coachId = $_GET['coachId'];

            // Get chat history
            $history = $this->chat->getChatHistory($userId, $coachId);

            echo json_encode([
                'success' => true,
                'history' => $history
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function validateAuth($authToken) {
        if (empty($authToken) || !str_starts_with($authToken, 'Bearer ')) {
            return false;
        }
        $token = substr($authToken, 7);
        $decoded = JwtHandler::validate($token);
        return $decoded;
    }

    private function getUserIdFromToken($authToken) {
        $token = substr($authToken, 7);
        $decoded = JwtHandler::validate($token);
        return $decoded;
    }
}

$controller = new ChatHistoryController();
$controller->handleRequest();