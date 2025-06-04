<?php
require_once '../config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT name FROM products 
        WHERE name LIKE :query 
        UNION 
        SELECT name FROM categories 
        WHERE name LIKE :query 
        LIMIT 5
    ");
    $stmt->execute(['query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode([]);
}