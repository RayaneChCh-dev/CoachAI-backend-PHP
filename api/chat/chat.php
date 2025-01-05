<?php
require_once '../../utils/cors.php';
enableCORS();
header ('Content-Type: application/json');

require_once '../../models/Chat.php';
require_once '../../services/AiServices.php';
require_once '../../utils/response.php';
require_once '../../utils/jwt.php';

class ChatController {

    private $aiServices;
    private $chat;

    public function __construct() {
        $this->aiServices = new AiServices();
        $this->chat = new Chat();
    }

    public function handleRequest() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents("php://input"), true);
            if (!isset($input['coachId']) || (!isset($input['message']))) {
                throw new Exception('Missing required fields', 400);
            }
            
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            error_log('Received Authorization header: ' . $authHeader);
            error_log('All headers: ' . print_r(getallheaders(), true));
            if (!$this->validateAuth($authHeader)) {
                throw new Exception('Unauthorized', 401);
            }

            $userId = $this->getUserIdFromToken($authHeader);
            
            $this->chat->saveMessage($userId, $input['coachId'], $input['message'], 'user');

            $chatHistory = $this->chat->getChatHistory($userId, $input['coachId']);

            $aiResponse = $this->aiServices->generateResponse(
                $input['coachId'],
                $input['message'],
                $chatHistory
            );
                
            

            // Sauvegarder la réponse de l'IA
            $this->chat->saveMessage($input['coachId'], $userId, $aiResponse, 'coach');

            echo json_encode([
                'success' => true,
                'ai_response' => $aiResponse,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);

        }
    }

    private function validateAuth($authHeader) {
        error_log('Validating auth header: ' . $authHeader);
        if (empty($authHeader)) {
            error_log('Auth header is empty');
            return false;
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            error_log('Auth header does not start with "Bearer "');
            return false;
        }
        $token = substr($authHeader, 7);
        $decoded = JwtHandler::validate($token);
        return $decoded;
    }

    private function getUserIdFromToken($authHeader) {
        try {
            $token = substr($authHeader, 7);
            return JwtHandler::getUserId($token);
        } catch (Exception $e) {
            error_log('Error validating token: ' . $e->getMessage());
            return false;
        }
    }

}

$controller = new ChatController();
$controller->handleRequest();
