<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/includes/member-access.php';
$id = (int)($_GET['id'] ?? 0);
fitness_require_assigned_member($conn, $id);
$select = $conn->prepare('SELECT fullname, contact, address, gender FROM members WHERE id = ?');
$select->bind_param('i', $id);
$select->execute();
$member = $select->get_result()->fetch_assoc();
if (!$member) { http_response_code(404); exit('Member not found.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['fullname', 'contact', 'address', 'gender'] as $field) $member[$field] = trim($_POST[$field] ?? '');
    if ($member['fullname'] === '' || mb_strlen($member['fullname']) > 100) $errors[] = 'Enter a full name using at most 100 characters.';
    if ($member['address'] === '' || mb_strlen($member['address']) > 255) $errors[] = 'Enter an address using at most 255 characters.';
    if (!preg_match('/^98\d{8}$/', $member['contact'])) $errors[] = 'Contact Number must be 10 digits long and start with 98.';
    if (!in_array($member['gender'], ['male', 'female', 'other', 'others'], true)) $errors[] = 'Select a valid gender.';
    if (!$errors) {
        $trainer_id = (int)$_SESSION['uid'];
        $update = $conn->prepare('UPDATE members SET fullname=?, contact=?, address=?, gender=? WHERE id=? AND trainer_id=?');
        $update->bind_param('ssssii', $member['fullname'], $member['contact'], $member['address'], $member['gender'], $id, $trainer_id);
        $update->execute();
        $_SESSION['success'] = 'Member information updated successfully';
        header('Location: member-progress.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub | Update Member</title>
    <link rel="stylesheet" href="css/user-payment.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'includes/template.php'; ?>
<div class="content">
    <h2>Update Member Information</h2>
    <?php foreach ($errors as $error) { ?><p style="color:#EE4266"><?= fitness_escape($error) ?></p><?php } ?>
    <div class="form-container">
        <form method="post">
            <table>
                <tr><td><label for="fullname">Full Name</label></td><td><input id="fullname" name="fullname" maxlength="100" required value="<?= fitness_escape($member['fullname']) ?>"></td></tr>
                <tr><td><label for="contact">Contact Number</label></td><td><input id="contact" name="contact" type="tel" maxlength="10" pattern="98[0-9]{8}" required value="<?= fitness_escape($member['contact']) ?>"></td></tr>
                <tr><td><label for="address">Address</label></td><td><input id="address" name="address" maxlength="255" required value="<?= fitness_escape($member['address']) ?>"></td></tr>
                <tr><td><label for="gender">Gender</label></td><td><select id="gender" name="gender" required>
                    <option value="male" <?= strtolower($member['gender']) === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= strtolower($member['gender']) === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="others" <?= in_array(strtolower($member['gender']), ['other', 'others'], true) ? 'selected' : '' ?>>Others</option>
                </select></td></tr>
            </table>
            <button type="submit">Save Member Information</button>
        </form>
    </div>
    <p><a href="member-progress.php">Back to Members' Progress</a></p>
</div>
<?php include 'includes/footer.php'; ?>
