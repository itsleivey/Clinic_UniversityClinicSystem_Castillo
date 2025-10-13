<?php
require_once 'config/database.php'; // adjust your path if needed
$conn = pdo_connect_mysql();

try {
    $stmt = $conn->prepare("
        SELECT ClientType, COUNT(*) AS count
        FROM clients
        GROUP BY ClientType
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
