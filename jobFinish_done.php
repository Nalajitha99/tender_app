<?php
include "db.php";
session_start();

$id = $_POST['id'];

// 1. Fetch the record from sales
$sql = "SELECT * FROM sales WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Record not found.";
    exit();
}

$row = $result->fetch_assoc();
$sts = 'Finished';
$formattedDate = date('Y-m-d');

$insert = $conn->prepare("INSERT INTO finish_sales (cusname, location, description, cusType, contactPerson, contactPersonEmail, contactPersonTel, expectedCost, profit, estimatedFinishDate, sts, salesPerson, lastUpdatedDate)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("sssssssssssss", 
    $row['cusname'], 
    $row['location'], 
    $row['description'],
    $row['cusType'], 
    $row['contactPerson'], 
    $row['contactPersonEmail'], 
    $row['contactPersonTel'],
    $row['expectedCost'],
    $row['profit'],
    $row['estimatedFinishDate'], 
    $sts, 
    $row['salesPerson'], 
    $formattedDate
);

if ($insert->execute()) {
    $delete = $conn->prepare("DELETE FROM sales WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    echo "<script>alert('Moved to Finished Sales successfully'); window.location.href='jobFinishTable.php';</script>";
} else {
    echo "Error while moving record: " . $insert->error;
}


$conn->close();
?>
