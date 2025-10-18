<?php
// config/database.php
require __DIR__ . '../../../db_cridentials.php';

if (!function_exists('pdo_connect_mysql')) {
    function pdo_connect_mysql()
    {
        global $DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME, $DATABASE_PORT;

        try {
            $conn = new PDO(
                "mysql:host=$DATABASE_HOST;port=$DATABASE_PORT;dbname=$DATABASE_NAME;charset=utf8",
                $DATABASE_USER,
                $DATABASE_PASS
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $exception) {
            exit('Failed to connect to database: ' . $exception->getMessage());
        }
    }
}
