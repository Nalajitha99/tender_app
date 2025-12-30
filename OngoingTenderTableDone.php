<?php
header("Content-Type: application/json; charset=utf-8");
include "db.php";
session_start();  // 🔥 Required to read logged-in username

$response = [
    "error" => null,
    "query" => null,
    "totalPages" => 0,
    "currentPage" => 1,
    "totalRows" => 0,
    "data" => []
];

try {
    // --- Logged-in user ------------------------------------------------------
    $loggedUser = isset($_SESSION['username']) ? $_SESSION['username'] : "";

    // --- Read & validate inputs ---------------------------------------------
    $organization = isset($_GET['organization']) ? trim($_GET['organization']) : "";
    $userFilter   = isset($_GET['user']) ? trim($_GET['user']) : "";
    $startDate    = isset($_GET['startDate']) ? trim($_GET['startDate']) : "";
    $endDate      = isset($_GET['endDate']) ? trim($_GET['endDate']) : "";

    $page  = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 5;
    if ($limit > 200) $limit = 200;

    $offset = ($page - 1) * $limit;
    $response['currentPage'] = $page;

    // --- Build WHERE clause --------------------------------------------------
    $where = "WHERE status = 'ongoing'";

    // ✔ Organization filter
    if ($organization !== "") {
        $where .= " AND organization LIKE '%" . $conn->real_escape_string($organization) . "%'";
    }

    // ✔ Admin & Prasadini can filter by user
    if ($userFilter !== "" && ($loggedUser === "Admin" || $loggedUser === "Prasadini" || $loggedUser === "Wimal" || $loggedUser === "Chanaka")) {
        $where .= " AND assignedBy LIKE '%" . $conn->real_escape_string($userFilter) . "%'";
    }

    // ✔ Date filter
    if ($startDate !== "" && $endDate !== "") {
        $start = $conn->real_escape_string($startDate);
        $end = $conn->real_escape_string($endDate);
        $where .= " AND assignedDate BETWEEN '$start' AND '$end'";
    }

    // 🔥 MOST IMPORTANT PART:
    //    Non-admin users only see their own tenders
    if ($loggedUser !== "Admin" && $loggedUser !== "Prasadini" && $loggedUser !== "Wimal" && $loggedUser !== "Chanaka") {
        $where .= " AND assignedBy = '" . $conn->real_escape_string($loggedUser) . "'";
    }

    // --- Count rows ----------------------------------------------------------
    $countSql = "SELECT COUNT(*) AS total FROM tenders {$where}";
    $countRes = $conn->query($countSql);
    if (!$countRes) throw new Exception("Count query failed: " . $conn->error);

    $totalRows = (int) $countRes->fetch_assoc()['total'];
    $response['totalRows'] = $totalRows;
    $response['totalPages'] = $totalRows > 0 ? (int) ceil($totalRows / $limit) : 1;

    // --- Main data query -----------------------------------------------------
    $sql = "SELECT
                id,
                organization,
                location,
                tenderNo,
                bidSecurity,
                assignedBy AS assignedPerson,
                closingDate,
                approveStatus
            FROM tenders
            {$where}
            ORDER BY recievedDate DESC
            LIMIT {$limit} OFFSET {$offset}";

    $response['query'] = $sql;

    $res = $conn->query($sql);
    if (!$res) throw new Exception("Main query failed: " . $conn->error);

    while ($row = $res->fetch_assoc()) {
        $response['data'][] = $row;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $ex) {
    http_response_code(500);
    $response['error'] = $ex->getMessage();
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
