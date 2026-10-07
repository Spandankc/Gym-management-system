<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
include "includes/authentication.php";

require_once __DIR__ . '/includes/member-access.php';
$id = (int)($_GET['id'] ?? 0);
fitness_require_assigned_member($conn, $id);


require_once __DIR__ . '/../includes/training-plan.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link = fitness_training_plan_url($_POST['link'] ?? '');
    if ($link === null) {
        $error = 'Enter a valid HTTPS Google Sheets link.';
    } else {
        $statement = $conn->prepare('UPDATE members SET plan_link = ? WHERE id = ?');
        $statement->bind_param('si', $link, $id);
        $statement->execute();
        $_SESSION['success'] = 'Training Plan Added';
        header('Location: member-progress.php');
        exit;
    }
}
$select = $conn->prepare('SELECT plan_link FROM members WHERE id = ?');
$select->bind_param('i', $id);
$select->execute();
$existing = $select->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Training Plan</title>
    <link rel="stylesheet" href="css/member-progress.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'includes/template.php'; ?>

<div class="content">
    <h1>Upload Training Plan</h1>
    <?php if (isset($_SESSION['success'])) { ?>
            <div class="message">
                <h3><?= $_SESSION['success'];
                unset($_SESSION['success']) ?></h3>
            </div><?php } ?>
    <?php if ($error) { ?><p style="color:#EE4266"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
    <form action="" method="post" class="plan">
        <label for="link">Add Training Plan Link</label>
        <input type="url" required name="link" placeholder="Add Plan Link" value="<?= htmlspecialchars($_POST['link'] ?? $existing['plan_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button class="add">Add</button>
    </form>
    <div class="back-div"><a href="member-progress.php" class="back">&#8592; Go Back</a></div>

</div>


<?php include 'includes/footer.php' ?>