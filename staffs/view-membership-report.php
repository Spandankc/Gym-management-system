<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
include "includes/authentication.php";

require_once __DIR__ . '/includes/member-access.php';
$id = (int)($_GET['id'] ?? 0);
fitness_require_assigned_member($conn, $id);
$sql="SELECT members.*, services.service_name, services.cost
    FROM members
    LEFT JOIN services ON members.services_id=services.id
    WHERE members.id=$id";
$result=mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Trainer</title>
    <link rel="stylesheet" href="css/view-membership-report.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'includes/template.php'; ?>
<div class="content">
<h2>Membership Report</h2>
    <div class="membership">
        <div class="gym-info">
            <h3>Fitness Hub</h3>
            <p>Kathmandu, Nepal <br>Tel: 9876543210 <br>support@fitnesshub.com</p>
        </div>
        <div class="details">
            <div class="top">
                <table>
                    <thead>
                        <tr>
                            <th>Member ID</th>
                            <th>Service Taken</th>
                            <th>Duration</th>
                            <th>Address</th>
                            <th>Charge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$id?></td>
                            <td><?= fitness_escape($row['service_name']) ?></td>
                            <td><?=$row['plan']?> Month/s</td>
                            <td><?= fitness_escape($row['address']) ?></td>
                            <td><?=$row['cost']?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bottom">
                <h3>Last Payment Done: Rs.<?=$row['cost']?>/-</h3>
                <p>Member Since: <?=$row['dor']?></p>
            </div>
        </div>
        <div class="membership-message">
            <h3>Member Name: <?= fitness_escape($row['fullname']) ?>,</h3><br>
            <h3>Membership is currently <?=$row['status']?></h3>
            <p class="thanks"><em>Thankyou for choosing our services.<br>  -on behalf of whole team</em></p>
        </div>
    </div>
    <p class="back"><a href="membership-report.php">&larr;Go back</a></p>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
