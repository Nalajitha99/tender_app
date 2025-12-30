<?php

session_start();

$timeout_duration = 600;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['username'])) {
    header("Location: table1.php");
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     
    session_destroy();   
    header("Location: index.php?expired=true");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); 


$servername = "localhost";
$username = "powernet";
$password = "Power@#2587";
$dbname = "sales_app";

$conn = new mysqli($servername, $userName, $password, $dbname);

if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}


$id = $_GET['id'];  

$sql = "SELECT * FROM finish_sales WHERE id = $id"; 
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "No record found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Powernet Sales</title>
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-10 col-xl-10">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Fiished Sale</h3>
                        
                        <form name="salesForm" id="salesForm" action="salesUpdate_done.php" method="post">
                            <!-- Hidden ID field -->
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="cusname" value="<?php echo $row['cusname']; ?>" disabled />
                                    <label class="form-label">Customer Name</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="location" value="<?php echo $row['location']; ?>" disabled />
                                    <label class="form-label">Location</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control-lg" name="description" rows="3" disabled><?php echo $row['description']; ?></textarea>
                                    <label class="form-label">Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['cusType']; ?>" disabled />
                                    <label class="form-label">Customer Type</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['contactPerson']; ?>" disabled />
                                    <label class="form-label">Contact Person</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['contactPersonEmail']; ?>" disabled />
                                    <label class="form-label">Email</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['contactPersonTel']; ?>" disabled />
                                    <label class="form-label">Phone Number</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['expectedCost']; ?>" disabled />
                                    <label class="form-label">Expected Cost</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" value="<?php echo $row['profit']; ?>" disabled />
                                    <label class="form-label">Profit</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-4">
                                    <select class="form-select form-control-lg" name="sts" disabled>
                                        <option value="">Select the Status</option>
                                        <option value="Arranged Meeting" <?php if($row['sts'] == 'Arranged Meeting') echo 'selected'; ?>>Arranged Meeting</option>
                                        <option value="Finished" <?php if($row['sts'] == 'Finished') echo 'selected'; ?>>Finished</option>
                                        <option value="Cancled" <?php if($row['sts'] == 'cancled') echo 'selected'; ?>>Cancled</option>
                                    </select>
                                    <label class="form-label">Status</label>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>
