<?php
require 'config/database.php';

header('Content-Type: application/json');

// Create a new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event'])) {
    try {
        $conn = pdo_connect_mysql();
        
        $date = $_POST['date'];
        $time = $_POST['time'];
        $event = $_POST['event'];
        $location = $_POST['location'] ?? null;
        $noted = $_POST['noted'] ?? null;

        $stmt = $conn->prepare('INSERT INTO todolist (date, time, event, location, noted) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$date, $time, $event, $location, $noted]);
        
        echo json_encode(['status' => 'success', 'message' => 'Task added successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to add task: ' . $e->getMessage()]);
    }
    exit;
}

// Read all tasks
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conn = pdo_connect_mysql();
        $stmt = $conn->query('SELECT * FROM todolist ORDER BY date, time');
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($tasks);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch tasks: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['todolistid'])) {
    try {
        $conn = pdo_connect_mysql();
       
        $check = $conn->prepare('SELECT todolistid FROM todolist WHERE todolistid = ?');
        $check->execute([$_POST['todolistid']]);
        
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Task not found']);
            exit;
        }

        $stmt = $conn->prepare('DELETE FROM todolist WHERE todolistid = ?');
        $stmt->execute([$_POST['todolistid']]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Task deleted',
            'deletedId' => $_POST['todolistid'] 
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

?>