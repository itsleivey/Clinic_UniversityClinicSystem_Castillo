<?php
require 'config/database.php';
$pdo = pdo_connect_mysql();

// Fetch recent patients who have consultationrecords or prescriptions
$stmt = $pdo->query("
    SELECT DISTINCT c.ClientID,
           c.Firstname,
           c.Lastname,
           c.ClientType,
           c.profilePicturePath,
           GREATEST(
               IFNULL(MAX(cr.datecreated), '1970-01-01'),
               IFNULL(MAX(p.date_created), '1970-01-01')
           ) AS last_record_datetime
    FROM clients c
    LEFT JOIN consultationrecords cr ON cr.ClientID = c.ClientID
    LEFT JOIN prescriptions p ON p.ClientID = c.ClientID
    WHERE cr.consultationid IS NOT NULL OR p.id IS NOT NULL
    GROUP BY c.ClientID
    ORDER BY last_record_datetime DESC
    LIMIT 10
");

$recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
