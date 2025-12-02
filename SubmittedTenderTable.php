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

$userQuery = "SELECT uname, department FROM users ORDER BY uname ASC";
$userResult = mysqli_query($conn, $userQuery);

// DATE FILTER
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
?>


<!DOCTYPE html>

<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Powernet Tenders</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/table.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body { font-family: Arial, sans-serif; }
        #tender-data tr:hover { background-color: #f1f1f1; cursor: pointer; }
        .date-filter { text-align:center; margin-bottom: 20px; }
        .date-filter input { margin: 0 5px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <img src="images/footerLogo.png" alt="Powernet Logo" class="navbar-brand" style="height: 50px;">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="Chart.php" style="font-weight:bold;">Dashboard</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="OngoingTenderTable.php" style="font-weight:bold;">Ongoing Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item" style="background-color:rgb(166, 166, 166);border-radius:10px"><a class="nav-link active" href="SubmittedTenderTable.php" style="font-weight:bold;">Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="UncompletedTenderTable.php" style="font-weight:bold;">Not Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="AwardedTenderTable.php" style="font-weight:bold;">Awarded Tenders</a></li>
            </ul>
            <span class="navbar-text me-3">Logged in as: <?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        </div>
    </div>
</nav>
<br><br>
<div class="container my-3">
    <h1>Submitted Tenders</h1>

    <!-- DATE FILTER -->
    <div class="date-filter">
        <label>Select Date Range:</label>
        <input type="date" id="startDate" value="<?php echo htmlspecialchars($startDate); ?>">
        <input type="date" id="endDate" value="<?php echo htmlspecialchars($endDate); ?>">
        <button class="btn btn-primary btn-sm" onclick="applyDateFilter()">Apply Filter</button>
        <button class="btn btn-secondary btn-sm" onclick="clearFilter()">Clear Filter</button>
        <!-- <button class="btn btn-success btn-sm" onclick="window.print()">Print / PDF</button> -->
        <button class="btn btn-success btn-sm" onclick="printPDF()">Print PDF</button>

<script>
function printPDF(){
    const customer = document.getElementById('searchCustomer')?.value || '';
    const filterUser = document.getElementById('filterByUser')?.value || '';
    const startDate = document.getElementById('startDate')?.value || '';
    const endDate = document.getElementById('endDate')?.value || '';

    const url = `PrintSubmitted.php?organization=${encodeURIComponent(customer)}&user=${encodeURIComponent(filterUser)}&startDate=${startDate}&endDate=${endDate}`;
    window.open(url, '_blank');
}
</script>

    </div>

<div class="row mb-3">
    <?php if ($userName == "Admin" || $userName == "Prasadini" || $userName == "Wimal" || $userName == "Chanaka") { ?>
    <div class="col-md-4">
        <label for="filterByUser" class="form-label">Filter by Assigned Person:</label>
        <select id="filterByUser" class="form-select" onchange="loadSalesData()">
                <option value="">All</option>

                <?php
                if (mysqli_num_rows($userResult) > 0) {
                    while ($row = mysqli_fetch_assoc($userResult)) {
                        if (strtolower($row['uname']) === "admin" || ($row['uname']) === "Prasadini" || ($row['uname']) === "Wimal" || ($row['uname']) === "Chanaka") {
                                                    continue;
                                                }
                        $display = $row['uname'];
                        if (!empty($row['department'])) {
                            $display .= " - " . $row['department'];
                        }
                        echo "<option value='" . $row['uname'] . "'>$display</option>";
                    }
                }
                ?>
            </select>
    </div>
    <?php } ?>
    <div class="col-md-4">
        <label for="searchCustomer" class="form-label">Search by Organization Name:</label>
        <input type="text" id="searchCustomer" class="form-control" onkeyup="loadSalesData()" placeholder="Enter organization name...">
    </div>
</div>

<div class="table-responsive px-3">
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Organization</th>
                <th>Location</th>
                <th>Tender No</th>
                <th>Bid Security</th>
                <th>Assigned Person</th>
                <th>Closing Date</th>
            </tr>
        </thead>
        <tbody id="tender-data"></tbody>
    </table>
</div>

<!-- PAGINATION -->
    <div class="text-center my-3">
        <nav>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>


</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let currentPage = 1;
let startDate = document.getElementById('startDate').value;
let endDate = document.getElementById('endDate').value;

function loadSalesData(page = 1) {
 currentPage = page;

    const customer = document.getElementById('searchCustomer')?.value || '';
    const filterUser = document.getElementById('filterByUser')?.value || '';
    startDate = document.getElementById('startDate').value;
    endDate = document.getElementById('endDate').value;

    let url = `SubmittedTenderTableDone.php?page=${page}&limit=5&organization=${encodeURIComponent(customer)}&user=${encodeURIComponent(filterUser)}`;
    if(startDate && endDate) url += `&startDate=${startDate}&endDate=${endDate}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tender-data');
            tbody.innerHTML = '';

            data.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'text-center';
                tr.innerHTML = `
                    <td>${row.organization}</td>
                    <td>${row.location}</td>
                    <td>${row.tenderNo}</td>
                    <td>${row.bidSecurity}</td>
                    <td>${row.assignedPerson}</td>
                    <td>${row.closingDate}</td>
                `;
                tr.onclick = () => window.location.href = `ViewSubmittedTender.php?id=${row.id}`;
                tbody.appendChild(tr);
            });

            buildPagination(data.totalPages, data.currentPage);
        });
}

function buildPagination(totalPages, currentPage) {
    const pag = document.getElementById("pagination");
    pag.innerHTML = "";

    if (totalPages < 1) return;

    if (currentPage > 1) {
        pag.innerHTML += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadSalesData(${currentPage - 1})">Prev</a>
        </li>`;
    }

    for (let i = 1; i <= totalPages; i++) {
        pag.innerHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadSalesData(${i})">${i}</a>
            </li>
        `;
    }

    if (currentPage < totalPages) {
        pag.innerHTML += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadSalesData(${currentPage + 1})">Next</a>
        </li>`;
    }
}

function applyDateFilter() {
    loadSalesData(1);
}

function clearFilter() {
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    loadSalesData(1);
}

document.addEventListener('DOMContentLoaded', loadSalesData);
</script>
<footer style="background-color:#320303" class="mt-auto py-4 px-4 px-xl-5 text-white d-flex justify-content-between align-items-center">
    <div> © 2026 Powernet (pvt) Ltd.<br> All rights reserved.</div>
    <div id="footer-datetime"></div>
</footer>


<script>
  function getFormattedDate() {
    const date = new Date();
    const day = date.getDate();
    const month = date.toLocaleString('default', { month: 'long' });
    const year = date.getFullYear();

    // Get day suffix (st, nd, rd, th)
    const suffix = (d) => {
      if (d > 3 && d < 21) return 'th';
      switch (d % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
      }
    };

    return `${day}${suffix(day)} ${month} ${year}`;
  }

  function getFormattedTime() {
    const date = new Date();
    return date.toLocaleTimeString(); // Customize if needed
  }

  function updateFooterDateTime() {
    document.getElementById('footer-datetime').innerHTML =
      `${getFormattedDate()}<br>${getFormattedTime()}`;
  }

  updateFooterDateTime();
  setInterval(updateFooterDateTime, 1000);
</script>


</body>
</html>
