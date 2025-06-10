<?php
require 'config/database.php';

if (isset($_GET['id'])) {
    $clientID = $_GET['id'];

    // Connect to the database
    $pdo = pdo_connect_mysql();

    // Prepare and execute the delete query
    $stmt = $pdo->prepare("DELETE FROM Clients WHERE ClientID = ?");
    $stmt->execute([$clientID]);

    // Check if the delete was successful
    if ($stmt->rowCount() > 0) {
        // Redirect to the previous page with a success message
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?delete=success');
    } else {
        // Redirect to the previous page with an error message
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?delete=error');
    }
}
?>
