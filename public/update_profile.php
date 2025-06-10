<?php
session_start();
require('UC-System/config/database.php');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['ClientID'])) {
    $clientID = $_SESSION['ClientID'];
    $pdo = pdo_connect_mysql();
    
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profilePic'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; 
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $uploadDir = '../uploads';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $stmt = $pdo->prepare("SELECT ProfilePicturePath FROM Clients WHERE ClientID = ?");
            $stmt->execute([$clientID]);
            $oldPicture = $stmt->fetchColumn();
            
            if ($oldPicture && file_exists('../'.$oldPicture)) {
                unlink('../'.$oldPicture);
            }
            
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_'.$clientID.'_'.time().'.'.$extension;
            $relativePath = '../uploads'.$filename;
            $absolutePath = '../'.$relativePath;
            
            if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
                $stmt = $pdo->prepare("UPDATE Clients SET ProfilePicturePath = ? WHERE ClientID = ?");
                $stmt->execute([$relativePath, $clientID]);
                
                $response['success'] = true;
                $response['filePath'] = '/UC-Login/'.$relativePath;
            }
        } else {
            $response['error'] = 'Invalid file type (only JPG, PNG, WEBP) or size > 2MB';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>