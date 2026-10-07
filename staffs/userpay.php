<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/../includes/payments.php';
require_once __DIR__ . '/includes/member-access.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) { http_response_code(404); exit('Member not found.'); }
    fitness_require_assigned_member($conn, $id);
    $plan = filter_var($_POST['plan'] ?? 0, FILTER_VALIDATE_INT);
    $amount = filter_var($_POST['amount'] ?? '', FILTER_VALIDATE_FLOAT);
    if (!in_array($plan, [1, 3, 6, 12], true) || $amount === false || !is_finite($amount) || $amount <= 0 || $amount > 10000000) {
        $_SESSION['error'] = 'Select a valid duration and monthly payment amount.';
        header('Location: user-payment.php?id=' . $id);
        exit;
    }
    try {
        $payment_id = fitness_record_payment($conn, $id, $plan, round($amount, 2), 'trainer', (int)$_SESSION['uid']);
    } catch (Throwable $error) {
        $_SESSION['error'] = 'Unable to record this payment. Try again.';
        header('Location: user-payment.php?id=' . $id);
        exit;
    }
    header('Location: userpay.php?receipt=' . $payment_id);
    exit;
}
$payment_id = filter_var($_GET['receipt'] ?? 0, FILTER_VALIDATE_INT);
if (!$payment_id || $payment_id < 1) { header('Location: payment.php'); exit; }
$select = $conn->prepare('SELECT payments.*, members.fullname, members.contact, services.service_name FROM payments JOIN members ON payments.member_id = members.id LEFT JOIN services ON members.services_id = services.id WHERE payments.id = ?');
$select->bind_param('i', $payment_id);
$select->execute();
$select_res = $select->get_result();
$receipt = $select_res->fetch_assoc();
if (!$receipt) { http_response_code(404); exit('Payment receipt not found.'); }
fitness_require_assigned_member($conn, (int)$receipt['member_id']);
$date = $receipt['paid_on'];
$plan = (int)$receipt['months'];
$amount = (float)$receipt['monthly_amount'];
$total = (float)$receipt['total_amount'];
$select_res->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link rel="stylesheet" href="css/userpay.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'includes/template.php'?>

<div class="content">
    <h2><i class="fa-solid fa-receipt" style="margin-right:0.5rem"></i>Payment Receipt</h2>
    <div class="receipt-container">
        <table>
            <thead>
                <tr>
                    <th colspan="2">Fitness Hub</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user=mysqli_fetch_assoc($select_res)){ ?>
                <tr>
                    <td>Receipt #<?= (int)$payment_id ?> | Date: <?=$date?> </td>
                </tr>
                <tr>
                    <td>Fullname</td>
                    <td><?=htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8')?></td>
                </tr>
                <tr>
                    <td>Contact</td>
                    <td><?=htmlspecialchars($user['contact'], ENT_QUOTES, 'UTF-8')?></td>
                </tr>
                <tr>
                    <td>Service Taken</td>
                    <td><?=htmlspecialchars($user['service_name'], ENT_QUOTES, 'UTF-8')?></td>
                </tr>
                <tr>
                    <td>Amount per Month</td>
                    <td><?=number_format($amount, 2)?></td>
                </tr>
                <tr>
                    <td>Duration</td>
                    <td><?=$plan?></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><?=number_format($total, 2)?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="message-container">
            <h2 class="message-text">Payment Succesfull!!</h2>
            <p>Thankyou for choosing us!</p>
            <p>We sincerely appreciate your promptness regarding all payments from your side.</p>
        </div>
        <form>
            <input type="button" value="Print this page" onClick="window.print()">
        </form>
    </div>
</div>


    <?php include 'includes/footer.php'?>