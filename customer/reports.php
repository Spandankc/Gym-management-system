<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('member', 'login.php');
include '../dbcon.php';
include 'includes/authentication.php';
require_once __DIR__ . '/../includes/payments.php';
$payments = fitness_payment_history($conn, (int) $_SESSION['uid']);

//FOR PROGRESS
$id=$_SESSION['uid'];
$sql="SELECT members.*, services.service_name,services.cost, progress.ini_weight, progress.curr_weight,progress.ini_bodytype, progress.curr_bodytype
    FROM members
    LEFT JOIN services ON members.services_id=services.id
    LEFT JOIN progress ON members.id=progress.member_id
    WHERE members.id=$id";
$result=mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
    $ini_weight=$row['ini_weight'];
    $curr_weight=$row['curr_weight'];
    if($ini_weight<$curr_weight){
        $diff=$curr_weight-$ini_weight;
        $progress=$diff." Kg Gained";
    }else{
        $diff=$ini_weight-$curr_weight;
        $progress=$diff." Kg Lost";
    }

    ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub</title>
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="./css/boilerplate.css">
        <link rel="stylesheet" href="css/reports.css">
    </head>
    <body>
   <?php include 'includes/boilerplate.php';?>

<div class="content">
    <h2>Progress Report</h2>
    <div class="progress">
        <p>Member Name: <?=fitness_escape($row['fullname'])?></p>
        <p>Member ID: <?=$id?></p>
        <table class="progress-table">
            <thead>
                <tr>
                    <th>Initial Weight</th>
                    <th>Current Weight</th>
                    <th>Initial Body Type</th>
                    <th>Current Body Type</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?=fitness_escape($ini_weight)?></td>
                    <td><?=fitness_escape($curr_weight)?></td>
                    <td><?=fitness_escape($row['ini_bodytype'])?></td>
                    <td><?=fitness_escape($row['curr_bodytype'])?></td>
                    <td><?=fitness_escape($progress)?></td>
                </tr>
            </tbody>
        </table>
        <div class="message-container">
            <h3>Dear <?=fitness_escape($row['fullname'])?>,</h3>
            <h3>Congratulations on your achievement!</h3>
            <p><em>Thankyou for choosing our services.<br>  -on behalf of whole team</em></p>
        </div>
    </div>
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
                            <td><?=fitness_escape($row['service_name'])?></td>
                            <td><?=fitness_escape($row['plan'])?> Month/s</td>
                            <td><?=fitness_escape($row['address'])?></td>
                            <td><?=fitness_escape($row['cost'])?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bottom">
                <?php if ($payments): ?><h3>Last Payment Done: Rs.<?=fitness_escape($payments[0]['total_amount'])?>/-</h3><?php else: ?><h3>No payment recorded yet.</h3><?php endif; ?>
                <p>Member Since: <?=fitness_escape($row['dor'])?></p>
            </div>
        </div>
        <div class="membership-message">
            <h3>Dear <?=fitness_escape($row['fullname'])?>,</h3><br>
            <h3>Your Membership is currently <?=fitness_escape($row['status'])?></h3>
            <p><em>Thankyou for choosing our services.<br>  -on behalf of whole team</em></p>
        </div>
    </div>
<h2>Payment History</h2>
    <?php if ($payments): ?>
        <table class="progress-table">
            <thead><tr><th>Receipt</th><th>Date</th><th>Duration</th><th>Total</th></tr></thead>
            <tbody><?php foreach ($payments as $payment): ?><tr>
                <td><?= (int) $payment['id'] ?></td><td><?=fitness_escape($payment['paid_on'])?></td>
                <td><?= (int) $payment['months'] ?> Month/s</td><td>Rs.<?=fitness_escape($payment['total_amount'])?></td>
            </tr><?php endforeach; ?></tbody>
        </table>
    <?php else: ?><p>No payments recorded yet.</p><?php endif; ?>
</div>
<?php } ?>

<?php include 'includes/footer.php'?>
