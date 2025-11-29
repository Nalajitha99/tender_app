<?php
session_start();

// Enable error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB Connection
include "db.php";

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['uname'];
    $password = $_POST['pword'];

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }

    // Fetch the user
    $sql = "SELECT * FROM users WHERE uname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $row = $result->fetch_assoc();

        // Check hashed password
        if (password_verify($password, $row['password'])) {

            $_SESSION['username'] = $row['uname'];

            header("Location: Chart.php");
            exit();

        } else {
            header("Location: index.php?error=wrongpass");
            exit();
        }

    } else {
        header("Location: index.php?error=nouser");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>
