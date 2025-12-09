<?php
// -------------------------------
// DATABASE CONNECTION
// -------------------------------
include "db.php";

session_start();
$timeout_duration = 7200;

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

// -------------------------------
// HANDLE FILTERS
// -------------------------------
$startDate = isset($_GET['startDate']) && $_GET['startDate'] != "" ? $_GET['startDate'] : null;
$endDate   = isset($_GET['endDate']) && $_GET['endDate'] != "" ? $_GET['endDate'] : null;
$assignedPersonFilter = isset($_GET['assignedPerson']) && $_GET['assignedPerson'] != "" ? $_GET['assignedPerson'] : null;

$dateFilter = "";
if ($startDate && $endDate) {
    $dateFilter = " AND assignedDate BETWEEN '$startDate' AND '$endDate'";
}

$assignedFilter = "";
if ($assignedPersonFilter) {
    $assignedFilter = " AND assignedBy = '".$conn->real_escape_string($assignedPersonFilter)."'";
}

// -------------------------------
// FETCH TENDER COUNTS
// -------------------------------
$completed = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE status = 'Completed' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$pending   = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE status = 'Uncompleted' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$ongoing   = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE status = 'Ongoing' $dateFilter $assignedFilter")->fetch_assoc()['total'];

// -------------------------------
// FETCH AWARD STATUS COUNTS
// -------------------------------
$awarded       = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE awardStatus = 'Awarded' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$not_awarded   = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE awardStatus = 'Not Awarded' $dateFilter $assignedFilter")->fetch_assoc()['total'];
$pending_award = $conn->query("SELECT COUNT(*) AS total FROM tenders WHERE awardStatus = 'Pending' $dateFilter $assignedFilter")->fetch_assoc()['total'];

// -------------------------------
// FETCH ASSIGNED TENDER COUNTS
// -------------------------------
$result = $conn->query("
    SELECT assignedBy, COUNT(*) AS total 
    FROM tenders 
    WHERE assignedBy IS NOT NULL $dateFilter $assignedFilter
    GROUP BY assignedBy
");

$assignedPersons = [];
$assignedCounts  = [];
while ($row = $result->fetch_assoc()) {
    $assignedPersons[] = $row['assignedBy'];
    $assignedCounts[]  = $row['total'];
}

// -------------------------------
// FETCH USERS LIST
// -------------------------------
$userResult = $conn->query("SELECT uname, department FROM users ORDER BY uname ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Powernet Tenders</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; }
        .charts-row { display: flex; flex-wrap: wrap; justify-content: space-around; gap: 40px; }
        .chart-container { width: 650px; margin-bottom: 100px; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; text-align: center; margin-top: 10px; font-weight: bold; }
        .date-filter { text-align:center; margin-bottom: 20px; }
        .filter-summary { text-align:center; font-weight:bold; margin-bottom:20px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 min-vw-100">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <img src="images/footerLogo.png" alt="Powernet Logo" class="navbar-brand" style="height: 50px;">
        <div class="collapse navbar-collapse" id="navbarNav">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item" style="background-color:rgb(166, 166, 166);border-radius:10px"><a class="nav-link active" href="Chart.php" style="font-weight:bold;">Dashboard</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="OngoingTenderTable.php" style="font-weight:bold;">Ongoing Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="SubmittedTenderTable.php" style="font-weight:bold;">Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="UncompletedTenderTable.php" style="font-weight:bold;">Not Submitted Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item"><a class="nav-link active" href="AwardedTenderTable.php" style="font-weight:bold;">Awarded Tenders</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php if($userName=="Admin") { ?>
                    <li class="nav-item"><a class="nav-link active" href="SalesPersonList.php" style="font-weight:bold;">Sales Person List</a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                    <li class="nav-item"><a class="nav-link active" href="AddSalesPerson.php" style="font-weight:bold;">Add Sales Person </a></li>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php } ?>
            </ul>
            <button 
                onclick="window.open('https://drive.google.com/drive/folders/16-jPxH3thRyOWdHxssKVbCATwYWXVv6j?usp=sharing', '_blank')" 
                style="padding:10px 20px; background:#4a90e2; color:white; border:none; border-radius:6px; cursor:pointer;">
                Supporting Documents
            </button>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="navbar-text me-3">Logged in as: <?php echo htmlspecialchars($userName); ?></span>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        </div>
    </div>
</nav>
<br>

<h2>Tender Dashboard</h2>

<!-- FILTERS -->
<div class="date-filter">
    <label>Filter Charts</label>
    <select id="assignedPerson" onchange="applyFilters()">
        <option value="">All Assigned Persons</option>
        <?php while($user = $userResult->fetch_assoc()): 
        if (strtolower($user['uname']) === "admin" || ($user['uname']) === "Prasadini" || ($user['uname']) === "Wimal" || ($user['uname']) === "Chanaka") {
                                                    continue;
                                                }
            $display = $user['uname'];
            if(!empty($user['department'])) $display .= " - ".$user['department'];
        ?>
        <option value="<?php echo htmlspecialchars($user['uname']); ?>" <?php echo ($assignedPersonFilter==$user['uname'])?'selected':''; ?>>
            <?php echo $display; ?>
        </option>
        <?php endwhile; ?>
    </select>&nbsp;&nbsp;

    <input type="date" id="startDate" value="<?php echo $startDate ?? ''; ?>">&nbsp;&nbsp;
    <input type="date" id="endDate" value="<?php echo $endDate ?? ''; ?>">&nbsp;&nbsp;
    <button class="btn btn-primary btn-sm" onclick="applyFilters()">Apply Filter</button>&nbsp;&nbsp;
    <button class="btn btn-secondary btn-sm" onclick="clearFilters()">Clear Filter</button>&nbsp;&nbsp;
    <button class="btn btn-success btn-sm" onclick="exportPDF()">Print / PDF</button>
</div>

<div class="container text-end mb-3">
<?php if ($userName == "Prasadini" || $userName == "Admin") { ?>
    <a href="AddTender.php" class="btn btn-danger">Add New Tender</a>
<?php } ?>

</div>


<!-- FILTER SUMMARY -->
<div class="filter-summary">
    <?php
        $summary = [];
        if($startDate && $endDate) $summary[] = "Date: ".date("d M Y",strtotime($startDate))." - ".date("d M Y",strtotime($endDate));
        if($assignedPersonFilter) $summary[] = "Assigned Person: ".htmlspecialchars($assignedPersonFilter);
        echo count($summary)? "Filters Applied: ".implode(" | ",$summary) : "Filters Applied: All Data";
    ?>
</div>

<div id="chartsContainer" class="charts-row">
    <div class="chart-container">
        <canvas id="tenderStatusChart"></canvas>
        <label>Tender Status Distribution</label>
    </div>
    <div class="chart-container">
        <canvas id="awardStatusChart"></canvas>
        <label>Award Status Distribution</label>
    </div>
    <div class="chart-container">
        <canvas id="assignedTendersChart"></canvas>
        <label>Assigned Tenders Per Person</label>
    </div>
</div>

<script>

// ------------------ COLOR FUNCTION ------------------
function getRandomColors(count) {
    const colors = [];
    for (let i = 0; i < count; i++) {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
    }
    return colors;
}

// ------------------ FILTER HANDLERS ------------------
function applyFilters(){
    const start = document.getElementById('startDate').value;
    const end   = document.getElementById('endDate').value;
    const person = document.getElementById('assignedPerson').value;
    let params = [];
    if(start) params.push("startDate="+start);
    if(end)   params.push("endDate="+end);
    if(person) params.push("assignedPerson="+encodeURIComponent(person));
    window.location.href = "Chart.php?"+params.join("&");
}
function clearFilters(){ window.location.href="Chart.php"; }

// ------------------ RANDOM COLORS FOR ASSIGNED CHART ------------------
const assignedColors = getRandomColors(<?php echo count($assignedPersons); ?>);

// ------------------ CHARTS ------------------
const tenderStatusChart = new Chart(document.getElementById('tenderStatusChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Completed','Not Completed','Ongoing'],
        datasets:[{
            label:'Number of Tenders',
            data:[<?php echo $completed; ?>,<?php echo $pending; ?>,<?php echo $ongoing; ?>],
            backgroundColor:['green','red','orange']
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end',color:'black',font:{weight:'bold',size:14}}},scales:{y:{beginAtZero:true,precision:0,ticks:{ stepSize: 1,callback: function(value){return Number.isInteger(value) ? value : null;}}}}},plugins:[ChartDataLabels]
});

const awardStatusChart = new Chart(document.getElementById('awardStatusChart').getContext('2d'),{
    type:'bar',
    data:{
        labels:['Awarded','Not Awarded','Pending'],
        datasets:[{
            label:'Award Status',
            data:[<?php echo $awarded; ?>,<?php echo $not_awarded; ?>,<?php echo $pending_award; ?>],
            backgroundColor:['blue','gray','purple']
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false},datalabels:{anchor:'end',align:'end',color:'black',font:{weight:'bold',size:14}}},scales:{y:{beginAtZero:true,precision:0,ticks:{ stepSize: 1,callback: function(value){return Number.isInteger(value) ? value : null;}}}}},plugins:[ChartDataLabels]
});

const assignedTendersChart = new Chart(document.getElementById('assignedTendersChart').getContext('2d'),{
    type:'bar',
    data:{
        labels:<?php echo json_encode($assignedPersons); ?>,
        datasets:[{
            label:'Assigned Tenders',
            data:<?php echo json_encode($assignedCounts); ?>,
            backgroundColor:assignedColors
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{display:false},
            datalabels:{
                anchor:'end',
                align:'end',
                color:'black',
                font:{weight:'bold',size:14}
            }
        },
        scales:{y:{beginAtZero:true,precision:0,ticks:{ stepSize: 1,callback: function(value){return Number.isInteger(value) ? value : null;}}}}
    },
    plugins:[ChartDataLabels]
});

// ------------------ PDF EXPORT ------------------
async function exportPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'px', format: 'a4' });

    // Header with filter info
    const start = document.getElementById('startDate').value || 'All';
    const end   = document.getElementById('endDate').value || 'All';
    const person = document.getElementById('assignedPerson').value || 'All';
    pdf.setFontSize(14);
    pdf.text(`Assigned Person: ${person}           Date: ${start} to ${end}`, 20, 30);

    let yOffset = 50;
    const charts = [tenderStatusChart, awardStatusChart, assignedTendersChart];

    for (const chart of charts) {
        const imgData = chart.toBase64Image();
        const pdfWidth = pdf.internal.pageSize.getWidth() - 40;
        const aspectRatio = chart.height / chart.width;
        const imgHeight = pdfWidth * aspectRatio;

        pdf.addImage(imgData, 'PNG', 20, yOffset, pdfWidth, imgHeight);
        yOffset += imgHeight + 20;

        if (yOffset + imgHeight > pdf.internal.pageSize.getHeight()) {
            pdf.addPage();
            yOffset = 20;
        }
    }

    pdf.save("TenderDashboard.pdf");
}
</script>

<footer style="background-color:#320303" class="mt-auto py-4 px-4 px-xl-5 text-white d-flex justify-content-between align-items-center">
    <div>© 2026 Powernet (pvt) Ltd.<br>All rights reserved.</div>
    <div id="footer-datetime"></div>
</footer>

<script>
function getFormattedDate(){ const d=new Date(); const suffix=(d.getDate()>3 && d.getDate()<21)?'th':((d.getDate()%10==1)?'st':(d.getDate()%10==2)?'nd':(d.getDate()%10==3)?'rd':'th'); return d.getDate()+suffix+" "+d.toLocaleString('default',{month:'long'})+" "+d.getFullYear(); }
function getFormattedTime(){ return new Date().toLocaleTimeString(); }
function updateFooterDateTime(){ document.getElementById('footer-datetime').innerHTML = getFormattedDate() + "<br>" + getFormattedTime(); }
updateFooterDateTime(); setInterval(updateFooterDateTime,1000);
</script>

</body>
</html>
