<?php
use Dotenv\Dotenv;
require_once __DIR__ . "../../config/database.php";
require_once "../../utils/cors.php";
class AiServices {
    private $apiKey;
    private $dotenv;

    public function __construct() {
        $this->dotenv = Dotenv::createImmutable("C:/xampp/htdocs/your-project-name");
        $this->dotenv->load();
        $this->apiKey = getenv('AI_API_KEY');
        $database = new Database();
        $this->db = $database->connect();
    }

    public function generateResponse($coachId, $userMessage, $chatHistory) {
        try {
            $formattedHistory = $this->formatChatHistory($chatHistory);
            $prompt = $this->constructPrompt($coachId, $userMessage, $formattedHistory);
            $response = $this->makeApiCall($prompt);
            return $response;
        } catch (Exception $e) {
            error_log("AI Service Error: " . $e->getMessage());
            return "I am sorry, I encountered an error processing your request. Please try again later.";  
        }
    }

    private function formatChatHistory($chatHistory) {
        $formatted = [];
        foreach ($chatHistory as $message) {
            $formatted[] = [
                'role' => $message['sender_type'],
                'content' => $message['message'],
                'timestamp' => $message['timestamp']
            ];
        }
        // Limit the history to the last 10 messages, so that the AI doesn't get overwhelmed(I'm just poor)
        return array_slice($formatted, -10);
    }

    private function constructPrompt($coachId, $userMessage, $formattedHistory) {
        $coachPersonality = $this->getCoachPersonality($coachId);
        $prompt = "You are a {$coachPersonality['profession']} with the following personality and background: {$coachPersonality['bio']}\n\n";

        foreach ($formattedHistory as $msg) {
            $prompt .= "{$msg['role']}: {$msg['content']}\n";
        }

        $prompt .= "user: {$userMessage}\ncoach: ";

        return $prompt;
    }

    private function makeApiCall($prompt) {
        $ch = curl_init();
        $payload = [
            'model' => 'claude-3-opus-20240229',
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ];
        
        error_log("API Request payload: " . json_encode($payload));
        
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.anthropic.com/v1/messages", // I'm using Anthropic's Claude API, You can use other APIs as well
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
                'content-type: application/json'
            ]
        ]);
    
        $response = curl_exec($ch);
        error_log("Raw API response: " . $response);
        
        if (curl_errno($ch)) {
            throw new Exception('API call failed: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("HTTP response code: " . $httpCode);
        
        curl_close($ch);
    
        $decoded = json_decode($response, true);
        if (!isset($decoded['content'][0]['text'])) {
            error_log("Decoded response structure: " . json_encode($decoded));
            throw new Exception('Invalid API response format');
        }
        
        return $decoded['content'][0]['text'];
    }

    private function processResponse($response) {
        if (!isset($response['content'][0]['text'])) {
            throw new Exception('Invalid API response format');
        }
        return trim($response);
    }

    private function getCoachPersonality($coachId) {
        $stmt = $this->db->prepare('SELECT profession, bio FROM coaches WHERE id = :id');
        $stmt->bindParam(':id', $coachId);
        $stmt->execute();
        $coach = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coach) {
            throw new Exception('Coach not found');
        }
        return $coach;
    }
}