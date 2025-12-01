<?php
// -------------------------------
// DB CONNECTION
// -------------------------------
include "db.php";

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); 

// Only Admin allowed
if (!isset($_SESSION['username']) || $_SESSION['username'] != "Admin") {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch users
$query = "SELECT id, uname FROM users ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User List</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f7f7f7; font-family: Arial, sans-serif; }
        .table-container {
            width: 90%;
            margin: auto;
            margin-top: 40px;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <img src="images/footerLogo.png" style="height:50px;" class="navbar-brand">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="Chart.php" style="font-weight:bold;">Dashboard</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="OngoingTenderTable.php" style="font-weight:bold;">Ongoing Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="SubmittedTenderTable.php" style="font-weight:bold;">Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="UncompletedTenderTable.php" style="font-weight:bold;">Not Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="AwardedTenderTable.php" style="font-weight:bold;">Awarded Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item" style="background-color:rgb(166, 166, 166);border-radius:10px"><a class="nav-link active" href="SalesPersonList.php" style="font-weight:bold;">Sales Person List</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="AddSalesPerson.php" style="font-weight:bold;">Add Sales Person </a></li>&nbsp;&nbsp;&nbsp;&nbsp;
            </ul>
            <span class="navbar-text me-3">Logged in as: <?php echo $username; ?></span>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        </div>
    </div>
</nav>

<h2>Sales Person List</h2>

<div class="table-container">
    <table class="table table-bordered table-striped table-hover shadow-sm bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Department</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['uname']); ?></td>
                <td>
                    <?php
                    $deptQuery = "SELECT department FROM users WHERE id = " . $row['id'];
                    $deptResult = $conn->query($deptQuery);
                    $deptRow = $deptResult->fetch_assoc();
                    echo htmlspecialchars($deptRow['department']);
                    ?></td>
                <td>
                    <a href="DeleteSalesPerson.php?id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure to delete this user?');"
                       class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
