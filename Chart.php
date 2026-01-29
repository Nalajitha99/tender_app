<?php
include "db.php";
session_start();

$timeout_duration = 7200;

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
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

/* COUNTS */
$completed = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Completed'")->fetch_assoc()['total'];
$pending   = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Uncompleted'")->fetch_assoc()['total'];
$ongoing   = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Ongoing'")->fetch_assoc()['total'];
$totalTenders = $conn->query("SELECT COUNT(*) total FROM tenders")->fetch_assoc()['total'];

/* ASSIGNED TENDERS */
$res = $conn->query("SELECT assignedBy, COUNT(*) total FROM tenders GROUP BY assignedBy");
$assignedPersons = [];
$assignedCounts = [];
while ($r = $res->fetch_assoc()) {
    if ($r['assignedBy']) {
        $assignedPersons[] = $r['assignedBy'];
        $assignedCounts[] = $r['total'];
    }
}

/* SALES */
$salesRes = $conn->query("SELECT salesPerson, COUNT(*) total FROM sales GROUP BY salesPerson");
$salesPersons = [];
$salesCounts = [];
while ($s = $salesRes->fetch_assoc()) {
    $salesPersons[] = $s['salesPerson'];
    $salesCounts[] = $s['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Powernet Dashboard</title>
<link rel="icon" href="images/logo.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<style>
body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: linear-gradient(135deg, #eef2f7, #f9fbfd);
    color: #2c3e50;
}

.dashboard-title {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    margin: 30px 0;
}

/* KPI CARDS */
.kpi-card {
    border-radius: 18px;
    transition: all 0.3s ease;
}
.kpi-card:hover {
    transform: translateY(-6px);
}

/* CHART CARDS */
.chart-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* BUTTONS */
.btn-gradient {
    background: linear-gradient(135deg, #4a90e2, #007bff);
    color: white;
    border-radius: 30px;
    padding: 10px 28px;
    border: none;
    transition: all 0.3s ease;
}
.btn-gradient:hover {
    background: linear-gradient(135deg, #007bff, #0056b3);
    transform: scale(1.05);
}
</style>
</head>

<body class="d-flex flex-column min-vh-100">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark"
     style="background: linear-gradient(135deg, #1f2d3d, #2c3e50);">
<div class="container-fluid">
    <img src="images/footerLogo.png" style="height:45px" class="me-3">
    <span class="navbar-text text-white me-auto">
        Logged in as: <?php echo htmlspecialchars($userName); ?>
    </span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</div>
</nav>

<div class="container">

    <div class="dashboard-title">Tender & Sales Dashboard</div>

    <!-- KPI ROW -->
    <div class="row g-4 text-center mb-5">
        <div class="col-md-3">
            <div class="card shadow border-0 kpi-card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Tenders</h6>
                    <h2><?php echo $totalTenders; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-0 kpi-card bg-success text-white">
                <div class="card-body">
                    <h6>Submitted</h6>
                    <h2><?php echo $completed; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-0 kpi-card bg-danger text-white">
                <div class="card-body">
                    <h6>Not Submitted</h6>
                    <h2><?php echo $pending; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow border-0 kpi-card bg-warning">
                <div class="card-body">
                    <h6>Ongoing</h6>
                    <h2><?php echo $ongoing; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="chart-card">
                <canvas id="assignedChart"></canvas>
                <div class="text-center mt-4">
                    <button class="btn btn-gradient" onclick="location.href='Chart1.php'">View Tenders</button>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <canvas id="salesChart"></canvas>
                <div class="text-center mt-4">
                    <?php if($userName!="Prasadini"){ ?>
                    <button class="btn btn-gradient" onclick="location.href='table1.php'">View Sales</button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- FOOTER -->
<footer style="background:#1f2d3d"
        class="mt-auto py-4 px-5 text-white d-flex justify-content-between">
    <div>© 2026 Powernet (Pvt) Ltd.</div>
    <div id="footerTime"></div>
</footer>

<script>
function colors(n){
    const p=['#4a90e2','#50c878','#f5a623','#9b59b6','#e74c3c','#16a085'];
    return Array.from({length:n},(_,i)=>p[i%p.length]);
}

new Chart(document.getElementById('assignedChart'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($assignedPersons); ?>,
        datasets:[{
            data:<?php echo json_encode($assignedCounts); ?>,
            backgroundColor:colors(<?php echo count($assignedPersons); ?>)
        }]
    },
    options:{
        plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end',font:{weight:'bold'}}},
        scales:{y:{beginAtZero:true}}
    },
    plugins:[ChartDataLabels]
});

new Chart(document.getElementById('salesChart'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($salesPersons); ?>,
        datasets:[{
            data:<?php echo json_encode($salesCounts); ?>,
            backgroundColor:colors(<?php echo count($salesPersons); ?>)
        }]
    },
    options:{
        plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end',font:{weight:'bold'}}},
        scales:{y:{beginAtZero:true}}
    },
    plugins:[ChartDataLabels]
});

function updateTime(){
    document.getElementById('footerTime').innerHTML =
        new Date().toLocaleDateString()+" "+new Date().toLocaleTimeString();
}
updateTime(); setInterval(updateTime,1000);
</script>

</body>
</html>
