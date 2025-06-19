<?php
require_once 'config/database.php';

function getFilteredClients($clientType, $search = '', $visitDate = '')
{
    $pdo = pdo_connect_mysql();

    $sql = "
        SELECT 
            c.ClientID,
            c.profilePicturePath,
            CONCAT(c.Firstname, ' ', c.Lastname) AS FullName,
            c.Email,
            COALESCE(pi.Course, 'N/A') AS Course,
            c.Department,
            c.ClientType
        FROM clients c
        LEFT JOIN personalinfo pi ON c.ClientID = pi.ClientID
        LEFT JOIN history h ON c.ClientID = h.ClientID
        WHERE c.ClientType = :clientType
    ";

    if (!empty($search)) {
        $sql .= " AND (
            c.ClientID LIKE :search OR
            c.Firstname LIKE :search OR
            c.Lastname LIKE :search OR
            c.Email LIKE :search OR
            c.Department LIKE :search OR
            pi.Course LIKE :search
        )";
    }

    if (!empty($visitDate)) {
        $sql .= " AND h.actionDate = :visitDate";
    }

    $sql .= " GROUP BY c.ClientID ORDER BY c.ClientID DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':clientType', $clientType, PDO::PARAM_STR);
    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    if (!empty($visitDate)) {
        $stmt->bindValue(':visitDate', $visitDate, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}


foreach ($clients as $client): ?>
    <tr class="client-row">
        <td class="searchable-id"><?= htmlspecialchars($client['ClientID']) ?></td>
        <td>
            <?php
            $profilePath = !empty($client['profilePicturePath']) ? '../../uploads/' . $client['profilePicturePath'] : '../../uploads/profilepic2.png';
            ?>
            <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="rounded-circle" width="50" height="50">
        </td>
        <td class="searchable-name"><?= htmlspecialchars($client['FullName']) ?></td>
        <td><?= htmlspecialchars($client['Email']) ?></td>
        <td><?= htmlspecialchars($client['Course']) ?></td>
        <td><?= htmlspecialchars($client['Department']) ?></td>
        <td><?= htmlspecialchars($client['ClientType']) ?></td>
        <td class="actions-column">
            <div class="action-buttons">
                <a href="ClientProfile.php?id=<?= $client['ClientID'] ?>" title="Edit User">
                    <img class="table-icon-img" src="assets/images/edit-blue-icon.svg" alt="Edit Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                </a>
                <a href="ClientProfile.php?id=<?= $client['ClientID'] ?>" class="btn btn-primary btn-sm">View</a>
                <a href="manageclients.dbf/delete_client.php?id=<?= $client['ClientID'] ?>"
                    onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                    <img class="table-icon-img" src="assets/images/delete-icon.svg" alt="Delete Icon" style="border-radius: 0; object-fit: unset; width: 20px; height: 20px;">
                </a>
            </div>
        </td>
    </tr>
<?php endforeach; ?>
