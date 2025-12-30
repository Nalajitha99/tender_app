<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$loggedInUsername = $_SESSION['username'];

include "db.php";

$searchCustomer = $_GET['customer'] ?? '';
$filterUser = $_GET['user'] ?? '';
$showAll = isset($_GET['showAll']) && $_GET['showAll'] === 'true';

$searchCustomer = "%" . $conn->real_escape_string($searchCustomer) . "%";

// If not showAll, set pagination
if (!$showAll) {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 5;
    $offset = ($page - 1) * $perPage;
}

// Build SQL
if ($loggedInUsername === "Wimal" || $loggedInUsername === "Admin") {
    $sql = "SELECT * FROM sales WHERE cusname LIKE ?";
    if (!empty($filterUser)) {
        $sql .= " AND salesPerson = ?";
    }
    $sql .= " ORDER BY lastUpdatedDate DESC";
    if (!$showAll) {
        $sql .= " LIMIT ?, ?";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($filterUser) && !$showAll) {
        $stmt->bind_param("ssii", $searchCustomer, $filterUser, $offset, $perPage);
    } elseif (!empty($filterUser)) {
        $stmt->bind_param("ss", $searchCustomer, $filterUser);
    } elseif (!$showAll) {
        $stmt->bind_param("sii", $searchCustomer, $offset, $perPage);
    } else {
        $stmt->bind_param("s", $searchCustomer);
    }

} else {
    $sql = "SELECT * FROM sales WHERE salesPerson = ? AND cusname LIKE ? ORDER BY estimatedFinishDate DESC";
    if (!$showAll) {
        $sql .= " LIMIT ?, ?";
    }

    if (!$showAll) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $loggedInUsername, $searchCustomer, $offset, $perPage);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $loggedInUsername, $searchCustomer);
    }
}

$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Total count
$totalQuery = "SELECT COUNT(*) as total FROM sales WHERE cusname LIKE ?";
$params = [$searchCustomer];
$types = "s";

if ($loggedInUsername === "Wimal" || $loggedInUsername === "Admin") {
    if (!empty($filterUser)) {
        $totalQuery .= " AND salesPerson = ?";
        $params[] = $filterUser;
        $types .= "s";
    }
} else {
    $totalQuery .= " AND salesPerson = ?";
    $params[] = $loggedInUsername;
    $types .= "s";
}

$countStmt = $conn->prepare($totalQuery);
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRow = $countResult->fetch_assoc();
$total = $totalRow['total'];

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'data' => $data,
    'total' => $total,
    'page' => $showAll ? 1 : $page,
    'perPage' => $showAll ? $total : $perPage
]);
?>
