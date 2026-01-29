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

/* FILTERS */
$startDate = $_GET['startDate'] ?? null;
$endDate   = $_GET['endDate'] ?? null;
$assignedPersonFilter = $_GET['assignedPerson'] ?? null;

$dateFilter = ($startDate && $endDate) ? " AND assignedDate BETWEEN '$startDate' AND '$endDate'" : "";
$assignedFilter = $assignedPersonFilter ? " AND assignedBy='".$conn->real_escape_string($assignedPersonFilter)."'" : "";

/* COUNTS */
$completed = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Completed' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$pending   = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Uncompleted' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$ongoing   = $conn->query("SELECT COUNT(*) total FROM tenders WHERE status='Ongoing' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$totalTenders = $conn->query("SELECT COUNT(*) total FROM tenders WHERE 1 $dateFilter $assignedFilter")->fetch_assoc()['total'];

$awarded = $conn->query("SELECT COUNT(*) total FROM tenders WHERE awardStatus='Awarded' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$not_awarded = $conn->query("SELECT COUNT(*) total FROM tenders WHERE awardStatus='Not Awarded' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$pending_award = $conn->query("SELECT COUNT(*) total FROM tenders WHERE awardStatus='Pending' $dateFilter $assignedFilter")->fetch_assoc()['total'];

/* ASSIGNED */
$res = $conn->query("
    SELECT assignedBy, COUNT(*) total 
    FROM tenders 
    WHERE assignedBy IS NOT NULL $dateFilter $assignedFilter
    GROUP BY assignedBy
");

$assignedPersons = [];
$assignedCounts = [];
while ($r = $res->fetch_assoc()) {
    $assignedPersons[] = $r['assignedBy'];
    $assignedCounts[] = $r['total'];
}

$userResult = $conn->query("SELECT uname, department FROM users ORDER BY uname ASC");
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
body{
    font-family:'Segoe UI',Roboto,Arial,sans-serif;
    background:linear-gradient(135deg,#eef2f7,#f9fbfd);
}
.dashboard-title{ font-size:32px;font-weight:700;text-align:center;margin:30px 0; }
.kpi-card{ border-radius:18px; transition:.3s; }
.kpi-card:hover{ transform:translateY(-6px); }
.chart-card{ background:#fff;border-radius:20px;padding:25px;box-shadow:0 10px 30px rgba(0,0,0,.08); }
.filter-box{ background:#fff;padding:15px 20px;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,.08);display:inline-block; }
footer{ background:#1f2d3d;color:#fff; }
</style>
</head>

<body class="d-flex flex-column min-vh-100">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(135deg,#1f2d3d,#2c3e50);">
<div class="container-fluid">
    <img src="images/footerLogo.png" style="height:45px" class="me-3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <ul class="navbar-nav me-auto mb-2 mb-lg-0"> 
        <li class="nav-item"><a class="nav-link active" href="Chart.php" style="font-weight:bold;">Home</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <li class="nav-item" style="background-color:rgb(255, 255, 255, 0.3);border-radius:10px"><a class="nav-link active" href="Chart1.php" style="font-weight:bold;">Dashboard</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <li class="nav-item"><a class="nav-link active" href="OngoingTenderTable.php" style="font-weight:bold;">Ongoing Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <?php if ($userName == "Prasadini" || $userName == "Admin") { ?> 
        <li class="nav-item"><a class="nav-link active" href="CheckedTenderTable.php" style="font-weight:bold;">Check Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <?php } ?>
        <li class="nav-item"><a class="nav-link active" href="SubmittedTenderTable.php" style="font-weight:bold;">Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <li class="nav-item"><a class="nav-link active" href="UncompletedTenderTable.php" style="font-weight:bold;">Not Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
        <li class="nav-item"><a class="nav-link active" href="AwardedTenderTable.php" style="font-weight:bold;">Awarded Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
    </ul>&nbsp;&nbsp;&nbsp;&nbsp;
    <button onclick="window.open('https://drive.google.com/drive/folders/16-jPxH3thRyOWdHxssKVbCATwYWXVv6j','_blank')" style="padding:10px 20px; background:#4a90e2; color:white; border:none; border-radius:6px; cursor:pointer;"> Supporting Documents </button>&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="navbar-text text-white me-auto">Logged in as: <?php echo htmlspecialchars($userName); ?></span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</div>
</nav>

<div class="container">
<div class="dashboard-title">Total Tenders: <?php echo $totalTenders; ?></div>

 

<!-- KPI ROW -->
<div class="row g-4 text-center mb-5">
    <div class="col-md-3"><div class="card kpi-card bg-primary text-white shadow"><div class="card-body"><h6>Total</h6><h2><?php echo $totalTenders; ?></h2></div></div></div>
    <div class="col-md-3"><div class="card kpi-card bg-success text-white shadow"><div class="card-body"><h6>Completed</h6><h2><?php echo $completed; ?></h2></div></div></div>
    <div class="col-md-3"><div class="card kpi-card bg-danger text-white shadow"><div class="card-body"><h6>Not Submitted</h6><h2><?php echo $pending; ?></h2></div></div></div>
    <div class="col-md-3"><div class="card kpi-card bg-warning shadow"><div class="card-body"><h6>Ongoing</h6><h2><?php echo $ongoing; ?></h2></div></div></div>
</div>

<div class="container text-end mb-3"> <?php if ($userName == "Prasadini" || $userName == "Admin") { ?> <a href="AddTender.php" class="btn btn-danger">Add New Tender</a> <?php } ?></div>
<!-- FILTER -->
<div class="text-center mb-4">
<div class="filter-box">
    <select id="assignedPerson">
        <option value="">All Assigned Persons</option>
        <?php while($user = $userResult->fetch_assoc()):
            if(in_array($user['uname'], ['Admin','Prasadini','Wimal','Chanaka'])) continue;
            $display = $user['uname']; if(!empty($user['department'])) $display .= " - ".$user['department'];
        ?>
        <option value="<?php echo htmlspecialchars($user['uname']); ?>" <?php echo ($assignedPersonFilter==$user['uname'])?'selected':''; ?>><?php echo $display; ?></option>
        <?php endwhile; ?>
    </select>
    From:
    <input type="date" id="startDate" value="<?php echo $startDate; ?>">
    To:
    <input type="date" id="endDate" value="<?php echo $endDate; ?>">
    <button class="btn btn-primary btn-sm" onclick="applyFilters()">Apply</button>
    <button class="btn btn-secondary btn-sm" onclick="clearFilters()">Clear</button>
    <button class="btn btn-success btn-sm" onclick="exportPDF()">PDF</button>
</div>
</div>

<!-- CHARTS -->
<div class="row g-5 mb-5">
    <div class="col-lg-6"><div class="chart-card"><canvas id="tenderStatusChart"></canvas></div></div>
    <div class="col-lg-6"><div class="chart-card"><canvas id="awardStatusChart"></canvas></div></div>
    <div class="col-lg-12"><div class="chart-card"><canvas id="assignedTendersChart"></canvas></div></div>
</div>
</div>

<footer class="mt-auto py-4 px-5 d-flex justify-content-between">
<div>© 2026 Powernet (Pvt) Ltd.</div>
<div id="footerTime"></div>
</footer>

<script>
function applyFilters(){
    let p=[];
    if(startDate.value) p.push("startDate="+startDate.value);
    if(endDate.value) p.push("endDate="+endDate.value);
    if(assignedPerson.value) p.push("assignedPerson="+assignedPerson.value);
    location.href="Chart1.php?"+p.join("&");
}
function clearFilters() {
    // Clear input fields
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    const assignedPerson = document.getElementById('assignedPerson');
    if (assignedPerson) assignedPerson.value = '';

    // Redirect to the base page without query parameters
    window.location.href = "Chart1.php";
}

function colors(n){ const p=['#4a90e2','#50c878','#f5a623','#9b59b6','#e74c3c']; return Array.from({length:n},(_,i)=>p[i%p.length]); }

const tenderStatusChart = new Chart(document.getElementById('tenderStatusChart'),{
    type:'bar',
    data:{labels:['Completed','Not Completed','Ongoing'], datasets:[{data:[<?php echo "$completed,$pending,$ongoing"; ?>], backgroundColor:['#50c878','#e74c3c','#f5a623']}]},
    options:{plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end'}}},
    plugins:[ChartDataLabels]
});

const awardStatusChart = new Chart(document.getElementById('awardStatusChart'),{
    type:'bar',
    data:{labels:['Awarded','Not Awarded','Pending'], datasets:[{data:[<?php echo "$awarded,$not_awarded,$pending_award"; ?>], backgroundColor:['#4a90e2','#95a5a6','#9b59b6']}]},
    options:{plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end'}}},
    plugins:[ChartDataLabels]
});

const assignedTendersChart = new Chart(document.getElementById('assignedTendersChart'),{
    type:'bar',
    data:{labels:<?php echo json_encode($assignedPersons); ?>, datasets:[{data:<?php echo json_encode($assignedCounts); ?>, backgroundColor:colors(<?php echo count($assignedPersons); ?>)}]},
    options:{plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end'}}},
    plugins:[ChartDataLabels]
});

function updateTime(){ footerTime.innerHTML=new Date().toLocaleDateString()+" "+new Date().toLocaleTimeString(); }
updateTime(); setInterval(updateTime,1000);

async function exportPDF(){
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation:'landscape', unit:'px', format:'a4' });
    const start=document.getElementById('startDate').value||'All';
    const end=document.getElementById('endDate').value||'All';
    const person=document.getElementById('assignedPerson').value||'All';
    pdf.setFontSize(14);
    pdf.text(`Assigned Person: ${person}    Date: ${start} to ${end}`, 20, 30);
    let yOffset=50;
    const charts=[tenderStatusChart, awardStatusChart, assignedTendersChart];
    for(const c of charts){
        const img=c.toBase64Image();
        const pdfWidth=pdf.internal.pageSize.getWidth()-40;
        const imgHeight=(c.height/c.width)*pdfWidth;
        if(yOffset+imgHeight>pdf.internal.pageSize.getHeight()){ pdf.addPage(); yOffset=20; }
        pdf.addImage(img,'PNG',20,yOffset,pdfWidth,imgHeight);
        yOffset+=imgHeight+20;
    }
    pdf.save("TenderDashboard.pdf");
}
</script>
</body>
</html>
