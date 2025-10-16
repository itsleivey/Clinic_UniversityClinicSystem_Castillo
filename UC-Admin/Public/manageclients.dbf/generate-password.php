<?php
// manageclients.dbf/generate-password.php
header('Content-Type: application/json');

// Ensure this runs only via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get email or fullname
$email = $_POST['email'] ?? '';
$fullname = $_POST['fullname'] ?? '';

// Basic validation
if (empty($email) && empty($fullname)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Use email as seed if available, otherwise name
$base = !empty($email) ? explode('@', $email)[0] : preg_replace('/\s+/', '', strtolower($fullname));

// Add salt and hash-based randomness
$salt = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%'), 0, 3);
$hashPart = substr(hash('sha256', $base . microtime(true) . rand()), 0, 4);

// Final generated password (Strong + readable)
$generatedPassword = ucfirst($base) . "@" . $hashPart . $salt;

// Return as JSON
echo json_encode(['password' => $generatedPassword]);
