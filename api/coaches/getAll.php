<?php
require_once '../../config/database.php';
require_once '../../utils/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

try {
    $db = (new Database())->connect();
    $query = 'SELECT id, name, profession, bio, image_url FROM coaches';
    $stmt = $db->prepare($query);
    $stmt->execute();

    $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($coaches as &$coach) {
        $coach['image_url'] = htmlspecialchars($coach['image_url'], ENT_QUOTES, 'UTF-8');
    }

    Response::json($coaches);
} catch (PDOException $e) {
    Response::error('Failed to fetch coaches: ' . $e->getMessage(), 500);
}
?>
