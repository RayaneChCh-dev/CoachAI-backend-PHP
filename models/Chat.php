<?php

require_once '../../config/database.php';

class Chat {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function saveMessage($senderId, $receiverId, $message, $senderType) {
        $query = 'INSERT INTO chat_messages (sender_id, receiver_id, message, sender_type, timestamp) 
                  VALUES (:sender_id, :receiver_id, :message, :sender_type, NOW())';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':sender_id', $senderId);
        $stmt->bindParam(':receiver_id', $receiverId);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':sender_type', $senderType);
        
        return $stmt->execute();
    }

    public function getChatHistory($userId, $coachId) {
        $query = 'SELECT * FROM chat_messages 
                  WHERE (sender_id = :user_id AND receiver_id = :coach_id)
                     OR (sender_id = :coach_id AND receiver_id = :user_id)
                  ORDER BY timestamp ASC';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':coach_id', $coachId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteHistory($userId, $coachId) {
        $query = 'DELETE FROM chat_messages 
                  WHERE (sender_id = :user_id AND receiver_id = :coach_id)
                     OR (sender_id = :coach_id AND receiver_id = :user_id)';
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':coach_id', $coachId);
        
        return $stmt->execute();
    }
}
