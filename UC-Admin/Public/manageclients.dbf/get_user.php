<?php
require_once 'config/database.php';
header('Content-Type: text/html');

function getFilteredClients($clientType, $idFilter = '')
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
        WHERE c.ClientType = :clientType
    ";

    if (!empty($idFilter)) {
        $sql .= " AND c.ClientID = :idFilter";
    }

    $sql .= " ORDER BY c.ClientID DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':clientType', $clientType, PDO::PARAM_STR);
    if (!empty($idFilter)) {
        $stmt->bindValue(':idFilter', $idFilter, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

$clientType = $_GET['client_type'] ?? '';
$idFilter = $_GET['id_filter'] ?? '';

if (!empty($clientType)) {
    $clients = getFilteredClients($clientType, $idFilter) ?? [];

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
<?php endforeach;
    exit;
}

$students = getFilteredClients('Student', $_GET['id_filter'] ?? '');
$faculties = getFilteredClients('Faculty', $_GET['id_filter'] ?? '');
$personnel = getFilteredClients('Personnel', $_GET['id_filter'] ?? '');
$freshman = getFilteredClients('Freshman', $_GET['id_filter'] ?? '');
$newpersonnel = getFilteredClients('NewPersonnel', $_GET['id_filter'] ?? '');
function fetchStudents($limit = 10, $offset = 0)
{
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("
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
        WHERE c.ClientType = 'Student'
        ORDER BY c.ClientID DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchFaculty($limit = 10, $offset = 0)
{
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("
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
        WHERE c.ClientType = 'Faculty'
        ORDER BY c.ClientID DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchPersonnel($limit = 10, $offset = 0)
{
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("
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
        WHERE c.ClientType = 'Personnel'
        ORDER BY c.ClientID DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchFreshman($limit = 10, $offset = 0)
{
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("
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
        WHERE c.ClientType = 'Freshman'
        ORDER BY c.ClientID DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchNewPersonnel($limit = 10, $offset = 0)
{
    $pdo = pdo_connect_mysql();

    $stmt = $pdo->prepare("
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
        WHERE c.ClientType = 'NewPersonnel'
        ORDER BY c.ClientID DESC
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countClientsByType($clientType)
{
    $pdo = pdo_connect_mysql();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE ClientType = :type");
    $stmt->bindValue(':type', $clientType, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}
