<?php
header("Content-Type: application/json; charset=utf-8");
include "db.php";

$response = [
    "error" => null,
    "query" => null,
    "totalPages" => 0,
    "currentPage" => 1,
    "totalRows" => 0,
    "data" => []
];

try {
    // --- Read & validate inputs ------------------------------------------------
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

    // --- Build WHERE clause safely --------------------------------------------
    $where = "WHERE status = 'ongoing'";
    if ($organization !== "") {
        $where .= " AND organization LIKE '%" . $conn->real_escape_string($organization) . "%'";
    }
    if ($userFilter !== "") {
        $where .= " AND assignedBy LIKE '%" . $conn->real_escape_string($userFilter) . "%'";
    }

    // --- Add date filter if provided -----------------------------------------
    if ($startDate !== "" && $endDate !== "") {
        $start = $conn->real_escape_string($startDate);
        $end = $conn->real_escape_string($endDate);
        $where .= " AND assignedDate BETWEEN '$start' AND '$end'";
    }

    // --- Count total rows -----------------------------------------------------
    $countSql = "SELECT COUNT(*) AS total FROM tenders {$where}";
    $countRes = $conn->query($countSql);
    if (!$countRes) throw new Exception("Count query failed: " . $conn->error);

    $totalRows = (int) $countRes->fetch_assoc()['total'];
    $response['totalRows'] = $totalRows;
    $response['totalPages'] = $totalRows > 0 ? (int) ceil($totalRows / $limit) : 1;

    // --- Main data query ------------------------------------------------------
    $sql = "SELECT
                id,
                organization,
                location,
                tenderNo,
                bidSecurity,
                assignedBy AS assignedPerson,
                closingDate
            FROM tenders
            {$where}
            ORDER BY closingDate DESC
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
