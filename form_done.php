<?php 

session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401); 
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$loggedInUsername = $_SESSION['username'];

    $cusname =$_POST['cusname'];
    $location=$_POST['location'];
    $description=$_POST['description'];
    $cusType=$_POST['cusType'];
    $contactPerson=$_POST['contactPerson'];
    $email=$_POST['contactPersonEmail'];
    $phoneNumber=$_POST['contactPersonTel'];
    $expectedCost =$_POST['expectedCost'];
    $profit=$_POST['profit'];
    $date=$_POST['estimatedFinishDate'];
    $Status = isset($_POST['status']) ? $_POST['status'] : '';
    // $salesPerson="Chinthaka";
    $lastUpdatedDate=date('Y-m-d');

    $formattedDate = !empty($date) ? date('Y-m-d', strtotime($date)) : null;

include "db.php";

$sql = "INSERT INTO sales (cusname,location,description,cusType,contactPerson,contactPersonTel,contactPersonEmail,expectedCost
,profit,estimatedFinishDate,sts,salesPerson,lastUpdatedDate)
VALUES ('$cusname', '$location', '$description','$cusType', '$contactPerson','$phoneNumber','$email','$expectedCost','$profit',
" . 
  ($formattedDate ? "'$formattedDate'" : "NULL") . ",'$Status','$loggedInUsername','$lastUpdatedDate')";

if ($conn->query($sql) === TRUE) {
    header("Location:table1.php");
//   echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

?>