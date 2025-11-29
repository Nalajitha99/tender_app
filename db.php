<?php 

$_SESSION['LAST_ACTIVITY'] = time(); 

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "tender_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

?>