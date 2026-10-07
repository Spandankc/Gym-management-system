<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/../includes/payments.php';
include "includes/authentication.php";

require_once __DIR__ . '/includes/member-access.php';
$id = (int)($_GET['id'] ?? 0);
fitness_require_assigned_member($conn, $id);
$sql="SELECT members.*, services.service_name , services.cost
    FROM members
    LEFT JOIN services on members.services_id=services.id
    WHERE members.id=$id";
$res=mysqli_query($conn,$sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <link rel="stylesheet" href="css/user-payment.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>

</head>
<body>
<?php include "includes/template.php"?>

<div class="content">
    <h2><i class="fa-solid fa-money-check-dollar" style="margin-right: 0.5rem"></i>Payment Form</h2>
    <?php if (isset($_SESSION['error'])) { ?><p style="color:#EE4266"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p><?php } ?>
    <div class="form-container">
                <form action="userpay.php" method="post">
                    <table>
                        <?php while($user=mysqli_fetch_assoc($res)){ ?>
                    <tr>
                        <td><label for="name">Fullname: </label></td>
                        <td><input type="hidden" name="fullname" value="<?= fitness_escape($user['fullname']) ?>"><b><?= fitness_escape($user['fullname']) ?></b></td>
                    </tr>
                    <tr>
                        <td><label for="service">Service Taken: </label></td>
                        <td><input type="hidden" name="services" value="<?=$user['services_id'] ?>"><b><?= fitness_escape($user['service_name']) ?></b></td>
                    </tr>
                    <tr>
                        <td><label for="amount">Amount per Month</label></td>
                        <td><input type="number" min="0.01" max="10000000" step="0.01" required id="payment-amount" name="amount" value="<?=$user['cost']?>"></td>
                    </tr>
                    <tr>
                        <td><label for="plan">Plan</label></td>
                        <td><select name="plan" id="payment-plan" required>
                            <?php foreach ([1, 3, 6, 12] as $duration) { ?><option value="<?= $duration ?>" <?= (int)$user['plan'] === $duration ? 'selected' : '' ?>><?= $duration ?> Month/s</option><?php } ?>
                        </select></td>
                    </tr>
                    <tr>
                        <td><label for="total">Total</label></td>
                        <td><input type="number" step="0.01" readonly id="payment-total" name="total" value="<?=$user['cost']*$user['plan']?>"></td>
                    </tr>
                </table>
                <input type="hidden" name="id" value="<?=$user['id']?>">
                <button>Make Payment</button>
                <?php } ?>
        </form>
    </div>
<h2>Payment History</h2>
    <table>
        <thead><tr><th>Receipt</th><th>Paid On</th><th>Amount per Month</th><th>Duration</th><th>Total</th></tr></thead>
        <tbody>
        <?php $payment_history = fitness_payment_history($conn, $id); ?>
        <?php if (!$payment_history) { ?><tr><td colspan="5">No payments recorded yet.</td></tr><?php } ?>
        <?php foreach ($payment_history as $payment) { ?>
            <tr>
                <td><a href="userpay.php?receipt=<?= (int)$payment['id'] ?>">#<?= (int)$payment['id'] ?></a></td>
                <td><?= fitness_escape($payment['paid_on']) ?></td>
                <td>Rs. <?= number_format((float)$payment['monthly_amount'], 2) ?></td>
                <td><?= (int)$payment['months'] ?> Month/s</td>
                <td>Rs. <?= number_format((float)$payment['total_amount'], 2) ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>



<script>
const paymentAmount = document.getElementById('payment-amount');
const paymentPlan = document.getElementById('payment-plan');
const paymentTotal = document.getElementById('payment-total');
if (paymentAmount && paymentPlan && paymentTotal) {
    const updateTotal = () => paymentTotal.value = (Number(paymentAmount.value) * Number(paymentPlan.value)).toFixed(2);
    paymentAmount.addEventListener('input', updateTotal);
    paymentPlan.addEventListener('change', updateTotal);
    updateTotal();
}
</script>
<?php include "includes/footer.php" ?>