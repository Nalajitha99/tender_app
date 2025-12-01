<?php
header("Content-Type: application/json");
include "db.php";

$response = ["success" => false, "error" => ""];

if (!isset($_POST['id'])) {
    $response['error'] = "No tender ID received";
    echo json_encode($response);
    exit;
}

$id = intval($_POST['id']);

// Update ApprovedStatus and ApprovedTime
$sql = "UPDATE tenders 
        SET approveStatus='Accepted', approvedTime = NOW() , approvedDate= NOW()
        WHERE id = $id";

if ($conn->query($sql)) {
    $response['success'] = true;
} else {
    $response['error'] = $conn->error;
}

echo json_encode($response);
?>
