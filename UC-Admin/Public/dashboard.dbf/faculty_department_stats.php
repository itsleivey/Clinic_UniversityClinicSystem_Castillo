<?php
function getFacultyDepartmentStats() {
    try {
        $pdo = pdo_connect_mysql();

        $stmt = $pdo->prepare("
            SELECT Department, COUNT(*) as count 
            FROM Clients 
            WHERE ClientType = 'Faculty'
            AND Department IS NOT NULL
            GROUP BY Department
            ORDER BY count DESC
        ");
        $stmt->execute();
        $departmentCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalStudents = array_sum(array_column($departmentCounts, 'count'));

        return [
            'departments' => $departmentCounts,
            'total' => $totalStudents
        ];

    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        return [
            'error' => 'Error loading department data',
            'details' => $e->getMessage()
        ];
    }
}