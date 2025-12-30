<?php

session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$loggedInUsername = $_SESSION['username'];

include "db.php";


$id = $_POST['id'];
$description = $_POST['description'];
$status = $_POST['sts'];
$cusname = $_POST['cusname'];
$location = $_POST['location'];
$contactPerson = $_POST['contactPerson'];
$contactPersonTel = $_POST['contactPersonTel'];
$contactPersonEmail = $_POST['contactPersonEmail'];
$expectedCost = $_POST['expectedCost'];
$profit = $_POST['profit'];
// $salesPerson = "Malaka";
$lastUpdatedDate = date('Y-m-d');

$sql = "UPDATE sales SET cusname = ?, location = ?,description = ?,contactPerson = ?,contactPersonTel = ?,contactPersonEmail = ?,expectedCost = ?,profit = ?, sts = ?, salesPerson = ?, lastUpdatedDate = ?  WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssi",$cusname, $location, $description, $contactPerson, $contactPersonTel, $contactPersonEmail, $expectedCost, $profit, $status, $loggedInUsername, $lastUpdatedDate, $id);

if ($stmt->execute()) {
    echo "<script>alert('Record updated successfully'); window.location.href='table1.php';</script>";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>