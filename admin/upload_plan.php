<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/../includes/training-plan.php';

$memberId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$member = null;
$planError = '';
$planSuccess = $_SESSION['admin_plan_success'] ?? '';
unset($_SESSION['admin_plan_success']);
if ($memberId !== false && $memberId !== null) {
    $statement = $conn->prepare('SELECT id, fullname, plan_link FROM members WHERE id = ?');
    $statement->bind_param('i', $memberId);
    $statement->execute();
    $member = $statement->get_result()->fetch_assoc();
    $statement->close();
}

if (!isset($_SESSION['training_plan_csrf'])) {
    $_SESSION['training_plan_csrf'] = bin2hex(random_bytes(32));
}

if (!$member) {
    http_response_code(404);
    $planError = 'That member was not found. Choose a member from the progress page.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $link = fitness_training_plan_url($_POST['link'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['training_plan_csrf'], $token)) {
        http_response_code(400);
        $planError = 'Your form expired. Please try again.';
    } elseif ($link === null) {
        $planError = 'Enter a valid HTTPS Google Sheets link (up to 255 characters).';
    } else {
        $statement = $conn->prepare('UPDATE members SET plan_link = ? WHERE id = ?');
        $statement->bind_param('si', $link, $memberId);
        $statement->execute();
        $statement->close();
        $_SESSION['admin_plan_success'] = 'Training plan saved. The member can view it on their dashboard.';
        header('Location: upload_plan.php?id=' . $memberId);
        exit;
    }
}

$planInput = isset($_POST['link']) && is_string($_POST['link']) ? $_POST['link'] : ($member['plan_link'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Plan | Fitness Hub</title>
    <link rel="stylesheet" href="css/template.css">
    <link rel="stylesheet" href="../customer/css/schedules.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body class="fitness-schedules">
<?php include __DIR__ . '/includes/template.php'; ?>
<main class="content schedule-content">
    <h1><?= $member && $member['plan_link'] !== '' ? 'Update' : 'Add' ?> Training Plan</h1>
    <?php if ($planSuccess !== ''): ?><div class="message" role="status"><?= fitness_escape($planSuccess) ?></div><?php endif; ?>
    <?php if ($planError !== ''): ?><div class="error" role="alert"><?= fitness_escape($planError) ?></div><?php endif; ?>
    <?php if ($member): ?>
    <p>Member: <strong><?= fitness_escape($member['fullname']) ?></strong></p>
    <form method="post" action="upload_plan.php?id=<?= (int) $memberId ?>" class="schedule-form">
        <input type="hidden" name="csrf_token" value="<?= fitness_escape($_SESSION['training_plan_csrf']) ?>">
        <div class="schedule-fields">
            <label class="schedule-wide">Google Sheets training plan link
                <input type="url" name="link" required maxlength="255" placeholder="https://docs.google.com/spreadsheets/d/.../edit" value="<?= fitness_escape($planInput) ?>">
            </label>
        </div>
        <p>Share the sheet with the member so they can view and update their routine.</p>
        <div class="schedule-form-actions"><button type="submit" class="schedule-button">Save Training Plan</button></div>
    </form>
    <?php if (fitness_training_plan_url($member['plan_link']) !== null): ?>
        <p><a href="<?= fitness_escape($member['plan_link']) ?>" target="_blank" rel="noopener noreferrer">Open Current Training Plan</a></p>
    <?php endif; ?>
    <?php endif; ?>
    <p><a href="member-progress.php">&larr; Back to Member Progress</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
