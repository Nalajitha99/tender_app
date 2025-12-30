<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Powernet Sales</title>
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/table.css'>
    <style>
        #sales-data tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
<?php
session_start();
$timeout_duration = 7200;
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

require_once "db.php";
$salesUsers = [];
$stmt = $conn->prepare("SELECT uname, department FROM users");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $salesUsers[] = $row;
}

?>

<nav style="background-color:#fff;" class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <!-- <h1 class="navbar-brand"> Powernet</h1> -->
         <img src="./images/logo.png" alt="Powernet Logo" class="navbar-brand" style="height: 50px; width: auto;">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="table1.php" style="color:green; font-weight:bold;">    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="Chart.php" style="color:black; font-weight:bold;">Home</a>
                </li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item" style="background-color:rgb(166, 166, 166);border-radius:10px">
                    <a class="nav-link active" href="table1.php" style="color:black; font-weight:bold;">Ongoing Sales</a>
                </li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <li class="nav-item">
                    <a class="nav-link active" href="jobFinishTable.php" style="color:black; font-weight:bold;">Finished Sales</a>
                </li>
            </ul>
            <span class="navbar-text text-dark me-3">
                Logged in as: <?php echo htmlspecialchars($userName); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Logout</a>
        </div>
    </div>
</nav>
<hr>
<div class="container my-3">
    <h1 class="ps-3 pt-3">Ongoing Sales</h1>
    <div class="row">
        <div class="col-md-4">
            <?php if ($userName === 'Wimal' || $userName === 'Admin') : ?>
                <label for="filterByUser" class="form-label">Filter by Last Updated By:</label>
                <select id="filterByUser" class="form-select" onchange="filterTable()">
                    <option value="">All</option>
                    <?php foreach ($salesUsers as $user): ?>
            <option value="<?php echo htmlspecialchars($user['uname']); ?>">
                <?php echo htmlspecialchars($user['uname']); ?>
                (<?php echo htmlspecialchars($user['department']); ?>)
            </option>
        <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label for="searchCustomer" class="form-label">Search by Customer Name:</label>
            <input type="text" id="searchCustomer" class="form-control" onkeyup="filterTable()" placeholder="Enter customer name...">
        </div>
        <div class="col-md-4">
            <h6 class="text-center">My Sales Status Summary</h6>
            <canvas id="statusChart" height="300"></canvas>
        </div>
    </div>
    <div class="col-md-4 mt-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showAllCheckbox" onchange="toggleShowAll()">
            <label class="form-check-label" for="showAllCheckbox">Show All Data</label>
        </div>
    </div>
    <div class="px-5">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr style="text-align: center;">
                        <th scope="col">Customer</th>
                        <th scope="col">Location</th>
                        <th scope="col">Customer Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Sales Person</th>
                        <th scope="col">Last Updated Date</th>
                    </tr>
                </thead>
                <tbody id="sales-data"></tbody>
            </table>
            <div id="pagination" class="text-center my-3 d-flex justify-content-center align-items-center"></div>
        </div>
    </div>
</div>

<div class="container my-3 text-end">
    <a href="salesForm.php" class="btn btn-primary">Add New Sale</a>
</div>

<footer style="background-color:#320303" class="mt-auto py-4 px-4 px-xl-5 text-white d-flex justify-content-between align-items-center">
    <div> © 2025 Powernet (pvt) Ltd.<br> All rights reserved.</div>
    <div id="footer-datetime"></div>
</footer>

<script>
let currentPage = 1;
const perPage = 5;
let showAll = false;

const statusColors = {
    "Arranged Meeting": "#ffc107",       // Yellow
    "Ongoing": "#198754",      // Green
    "Job Lost": "#dc3545",      // Red
    "Quotation Making": "#0d6efd",   // Blue
    "Site Visited": "#6f42c1",       // Purple
    "cancled": "#343a40",     // Dark
    "Quotation Sent": "#20c997",     // Teal
    "Job Won": "#fd7e14",     // Orange
    "Waiting For PO": "#0dcaf0",      // Cyan
    "Postponed": "#6610f2"       // Indigo
};


function loadSalesData() {
    const customer = document.getElementById('searchCustomer')?.value || '';
    const filterByUser = document.getElementById('filterByUser')?.value || '';
    const url = `table.php?page=${currentPage}&perPage=${showAll ? 1000 : perPage}&customer=${encodeURIComponent(customer)}&user=${encodeURIComponent(filterByUser)}`;

    fetch(url)
        .then(response => response.json())
        .then(res => {
            const tbody = document.getElementById('sales-data');
            tbody.innerHTML = '';
            res.data.forEach(row => {
                const tr = document.createElement('tr');
                tr.style.textAlign = "center";
                tr.innerHTML = `
                    <td class="customer-name">${row.cusname}</td>
                    <td>${row.location}</td>
                    <td>${row.cusType}</td>
                    <td style="color: ${statusColors[row.sts] || '#6c757d'}; border-radius: 4px; font-weight: bold;">
                    ${row.sts}
                    </td>

                    <td>${row.salesPerson}</td>
                    <td>${row.lastUpdatedDate}</td>
                `;
                tr.onclick = () => window.location.href = `salesFormUpdate.php?id=${row.id}`;
                tbody.appendChild(tr);
            });
            renderPagination(res.total);
        })
        .catch(err => console.error('Load error:', err));
}

function renderPagination(totalItems) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    if (showAll) return;
    const totalPages = Math.ceil(totalItems / perPage);

    const prevBtn = document.createElement('button');
    prevBtn.className = 'btn btn-sm btn-outline-primary mx-1';
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; loadSalesData(); } };
    pagination.appendChild(prevBtn);

    const pageInfo = document.createElement('span');
    pageInfo.className = 'mx-2';
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    pagination.appendChild(pageInfo);

    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn btn-sm btn-outline-primary mx-1';
    nextBtn.textContent = 'Next';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; loadSalesData(); } };
    pagination.appendChild(nextBtn);
}

function filterTable() {
    currentPage = 1;
    loadSalesData();
}

function toggleShowAll() {
    showAll = document.getElementById('showAllCheckbox').checked;
    loadSalesData();
}

document.addEventListener('DOMContentLoaded', () => {
    loadSalesData();
    document.getElementById('searchCustomer')?.addEventListener('keyup', filterTable);
    document.getElementById('filterByUser')?.addEventListener('change', filterTable);
});
</script>

<script>
let statusChart;
function loadSalesChart(user = '') {
    fetch(`salesStatusStats.php?user=${encodeURIComponent(user)}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            const ctx = document.getElementById('statusChart').getContext('2d');
            if (statusChart) statusChart.destroy();
            statusChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'Sales Count',
                        data: Object.values(data),
                        backgroundColor: ['#0d6efd', '#dc3545', '#198754', '#ffc107', '#6f42c1', '#20c997'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: { display: true, text: 'Sales Status Distribution' },
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadSalesChart('<?php echo $userName; ?>');
    document.getElementById('filterByUser')?.addEventListener('change', (e) => loadSalesChart(e.target.value));
});
</script>

<script>
function getFormattedDate() {
    const date = new Date();
    const day = date.getDate();
    const month = date.toLocaleString('default', { month: 'long' });
    const year = date.getFullYear();
    const suffix = (d) => (d > 3 && d < 21) ? 'th' : ['st', 'nd', 'rd'][d % 10 - 1] || 'th';
    return `${day}${suffix(day)} ${month} ${year}`;
}
function getFormattedTime() {
    return new Date().toLocaleTimeString();
}
function updateFooterDateTime() {
    document.getElementById('footer-datetime').innerHTML = `${getFormattedDate()}<br>${getFormattedTime()}`;
}
updateFooterDateTime();
setInterval(updateFooterDateTime, 1000);
</script>

</body>
</html>
