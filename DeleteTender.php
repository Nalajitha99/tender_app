<?php
session_start();
include "db.php";

header("Content-Type: application/json");

// Check login
if (!isset($_SESSION['username'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

// Only Admin can delete
if ($_SESSION['username'] !== "Admin") {
    echo json_encode(["success" => false, "error" => "Access denied"]);
    exit();
}

// Validate ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["success" => false, "error" => "Invalid tender ID"]);
    exit();
}

$id = intval($_POST['id']);

// Delete Query
$deleteQuery = "DELETE FROM tenders WHERE id = ?";
$stmt = mysqli_prepare($conn, $deleteQuery);
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database delete failed"]);
}
?>
