<?php
session_start();
$timeout_duration = 600;

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); 

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: index.php?expired=true");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();
$userName = $_SESSION['username'];

include "db.php";

if(isset($_POST['save'])){
    $email       = $_POST['email'];
    $uname       = $_POST['uname'];
    $department  = $_POST['department'];
    $password    = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (email, uname, password, department)
            VALUES ('$email', '$uname', '$password', '$department')";

    mysqli_query($conn, $sql);

    header("Location: SalesPersonList.php");
    exit();
}
?>

<!DOCTYPE html>

<html>
<head>
<title>Add User</title>
<link rel="icon" href="images/logo.ico" type="image/x-icon">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
<style>
    body {
        background: #f1f1f1;
    }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.1);
    }
</style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <img src="images/footerLogo.png" style="height:50px;" class="navbar-brand">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="Chart.php" style="font-weight:bold;">Home</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <!-- <li class="nav-item"><a class="nav-link active" href="Chart1.php" style="font-weight:bold;">Dashboard</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="OngoingTenderTable.php" style="font-weight:bold;">Ongoing Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="SubmittedTenderTable.php" style="font-weight:bold;">Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="UncompletedTenderTable.php" style="font-weight:bold;">Not Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="AwardedTenderTable.php" style="font-weight:bold;">Awarded Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp; -->
                <li class="nav-item"><a class="nav-link active" href="SalesPersonList.php" style="font-weight:bold;">Sales Person List</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item" style="background-color:rgb(166, 166, 166);border-radius:10px"><a class="nav-link active" href="AddSalesPerson.php" style="font-weight:bold;">Add Sales Person</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
            </ul>
            <span class="navbar-text me-3">Logged in as: <?php echo $userName; ?></span>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card p-4" style="width: 450px;">
        <h3 class="text-center mb-4">Add New User</h3>

    <form method="POST">

        <div class="mb-3">
            <label>Email</label>
            <input type="text" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Login Username</label>
            <input type="text" name="uname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Department</label>
            <select name="department" class="form-control" required>
                <option value="" disabled selected>Select</option>
                <option>IT</option>
                <option>Interior</option>
                <option>AC</option>
                <option>Generator</option>
                <option>Fire</option>
                <option>Sales</option>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" name="save" class="btn btn-success">
                Save User
            </button>
            <a href="SalesPersonList.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>

    </form>
</div>
```

</div>
</body>
</html>
