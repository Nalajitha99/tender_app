<?php
// ---------------------------------------------
// Database Connection
// ---------------------------------------------
include "db.php";

// ---------------------------------------------
// Receive POST Data
// ---------------------------------------------
$organization    = $_POST['organization'];
$tenderNo        = $_POST['tenderNo'];
$tenderTitle     = $_POST['tenderTitle'];
$description     = $_POST['description'];
$location        = $_POST['location'];
$closingDate     = $_POST['closingDate'];
$recievedFrom    = $_POST['recievedFrom'];
$assignedBy      = $_POST['assignedBy'];

$bidSecurity     = $_POST['bidSecurity'];
$recievedDate    = $_POST['recievedDate'];  
$recievedTime    = $_POST['recievedTime'];
$assignedDate    = $_POST['assignedDate']; 
$assignedTime    = $_POST['assignedTime'];
$approveStatus   = 'Pending';

// Handle Bid Fields
if ($bidSecurity === "Yes") {
    $bidAmount  = $_POST['bidAmount'];
    $bidValidity = $_POST['bidValidity'];
} else {
    // If NO → store empty values
    $bidAmount  = "";
    $bidValidity = "";
}

// ---------------------------------------------
// SQL Query
// ---------------------------------------------
$sql = "INSERT INTO tenders 
        (organization, tenderNo, tenderTitle, description, location, closingDate, bidSecurity, bidAmount, bidValidity, recievedFrom, recievedDate, recievedTime, assignedBy, assignedDate, assignedTime, status, approveStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ongoing', ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssssss",
    $organization,
    $tenderNo,
    $tenderTitle,
    $description,
    $location,
    $closingDate,
    $bidSecurity,
    $bidAmount,
    $bidValidity,
    $recievedFrom,
    $recievedDate,
    $recievedTime,
    $assignedBy,
    $assignedDate,
    $assignedTime,
    $approveStatus
);

// ---------------------------------------------
// Execute & Response
// ---------------------------------------------
if ($stmt->execute()) {
    echo "
        <script>
            alert('Tender Successfully Added!');
            window.location.href = 'OngoingTenderTable.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Error: " . $stmt->error . "');
            window.history.back();
        </script>
    ";
}

$stmt->close();
$conn->close();

?>
