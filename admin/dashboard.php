<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

//Active Member Count
$sql = "SELECT * FROM members WHERE status='Active'";
$result = mysqli_query($conn, $sql);
$activeCount = mysqli_num_rows($result);

//Registered Member Count
$sql = "SELECT * FROM members";
$result = mysqli_query($conn, $sql);
$memberCount = mysqli_num_rows($result);

// Total Earning
$sql = "SELECT COALESCE(SUM(total_amount), 0) AS total_earning FROM payments";
$sum = mysqli_query($conn, $sql);
$row_amount = mysqli_fetch_assoc($sum);
$total_earning = $row_amount['total_earning'];

//Total Announcements
$sql = "SELECT * FROM announcements";
$res = mysqli_query($conn, $sql);
$num = mysqli_num_rows($res);

// For male and female chart
$query = "SELECT gender, COUNT(*) as count FROM members GROUP BY gender";
$result = mysqli_query($conn, $query);

$genderDataPoints = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $label = ucfirst(strtolower($row["gender"]));
        $genderDataPoints[] = array("y" => $row["count"], "label" => $label);
    }
} else {
    // Handle the case when there are no records
    $genderDataPoints[] = array("y" => 0, "label" => "Male");
    $genderDataPoints[] = array("y" => 0, "label" => "Female");
}

// For services chart
$service_query = "
    SELECT services.service_name AS service_name, COUNT(members.id) AS count
    FROM members
    JOIN services ON members.services_id = services.id
    GROUP BY services.service_name
";
$service_result = mysqli_query($conn, $service_query);

$serviceDataPoints = array();

if ($service_result->num_rows > 0) {
    while ($row = $service_result->fetch_assoc()) {
        $serviceDataPoints[] = array("y" => $row["count"], "label" => ucfirst($row["service_name"]));
    }
} else {
    // Handle the case when there are no records
    $serviceDataPoints[] = array("y" => 0, "label" => "Fitness");
    $serviceDataPoints[] = array("y" => 0, "label" => "Sauna");
    $serviceDataPoints[] = array("y" => 0, "label" => "Cardio");
}

// For active and inactive members chart
$status_query = "SELECT status, COUNT(*) as count FROM members GROUP BY status";
$status_result = mysqli_query($conn, $status_query);

$statusDataPoints = array();

if ($status_result->num_rows > 0) {
    while ($row = $status_result->fetch_assoc()) {
        $label = strtolower($row["status"]) === 'active' ? "Active" : "Inactive";
        $statusDataPoints[] = array("y" => $row["count"], "label" => ucfirst($label));
    }
} else {
    // Handle the case when there are no records
    $statusDataPoints[] = array("y" => 0, "label" => "Active");
    $statusDataPoints[] = array("y" => 0, "label" => "Inactive");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Admin</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <script>
        window.onload = function () {
            var genderChart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Members by Gender"
                },
                axisY: {
                    title: "Number of Members"
                },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0.##",
                    dataPoints: <?php echo json_encode($genderDataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });
            genderChart.render();

            var serviceChart = new CanvasJS.Chart("serviceChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Members by Service"
                },
                axisY: {
                    title: "Number of Members"
                },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0.##",
                    dataPoints: <?php echo json_encode($serviceDataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });
            serviceChart.render();

            var statusChart = new CanvasJS.Chart("statusChartContainer", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Members by Status"
                },
                axisY: {
                    title: "Number of Members"
                },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0.##",
                    dataPoints: <?php echo json_encode($statusDataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });
            statusChart.render();
        }
    </script>
</head>

<body>

    <?php include 'includes/template.php'; ?>
    <div class="content">
        <div class="stats">
            <div class="stat-box">
                <i class="fa-solid fa-user-check active"></i>
                <h2 class="num"><?= $activeCount ?></h2>
                <h2>Active Members</h2>
            </div>
            <div class="stat-box">
                <i class="fa-solid fa-users"></i>
                <h2 class="num"><?= $memberCount ?></h2>
                <h2>Registered Members</h2>
            </div>
            <div class="stat-box">
                <i class="fa-solid fa-rupee-sign"></i>
                <h2 class="num"><?= $total_earning ?></h2>
                <h2>Total Earning</h2>
            </div>
            <a href="announcement-manage.php">
            <div class="stat-box">
                <i class="fa-solid fa-bullhorn"></i>
                <h2 class="num"><?= $num ?></h2>
                <h2>Announcements</h2>
            </div>
            </a>
        </div>
        <div class="charts">
            <div id="chartContainer"></div>
            <div id="statusChartContainer"></div>
        </div>
        <div class="serviceChart">
            <div id="serviceChartContainer"></div>
        </div>
    </div>

    <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>



    <?php include 'includes/footer.php' ?>