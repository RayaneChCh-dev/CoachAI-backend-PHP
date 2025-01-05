<?php
require_once '../../config/database.php';
require_once '../../utils/response.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Method not allowed', 405);
}

try {
    $db = (new Database())->connect();
    $query = 'SELECT id, name, description, price, stock, image_url FROM products';
    $stmt = $db->prepare($query);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as &$product) {
        $product['image_url'] = htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8');
    }

    Response::json($products);
} catch (PDOException $e) {
    Response::error('Failed to fetch coaches: ' . $e->getMessage(), 500);
}
?>