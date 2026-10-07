<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$id = (int)($_GET['id'] ?? 0);
if ($id < 1) { http_response_code(404); exit('Member not found.'); }
$select = $conn->prepare('SELECT members.*, services.service_name FROM members LEFT JOIN services ON members.services_id = services.id WHERE members.id = ?');
$select->bind_param('i', $id);
$select->execute();
$value = $select->get_result()->fetch_assoc();
if (!$value) { http_response_code(404); exit('Member not found.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['fullname', 'username', 'gender', 'dor', 'contact', 'address', 'plan'] as $field) $value[$field] = trim($_POST[$field] ?? '');
    $value['services_id'] = (int)($_POST['services'] ?? 0);
    foreach (['fullname', 'username', 'address'] as $field) {
        $maximum = ['fullname' => 100, 'username' => 20, 'address' => 255][$field];
        if ($value[$field] === '' || mb_strlen($value[$field]) > $maximum) $errors[] = 'Enter a valid ' . $field . ' (at most ' . $maximum . ' characters).';
    }
    if (!preg_match('/^98\d{8}$/', $value['contact'])) $errors[] = 'Contact Number must be 10 digits long and start with 98.';
    if (!in_array($value['gender'], ['male', 'female', 'other', 'others'], true)) $errors[] = 'Select a valid gender.';
    if (!in_array($value['plan'], ['1', '3', '6', '12'], true)) $errors[] = 'Select a valid duration.';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value['dor']);
    if (!$date || $date->format('Y-m-d') !== $value['dor'] || $value['dor'] > date('Y-m-d')) $errors[] = 'Enter a valid registration date that is not in the future.';
    $check = $conn->prepare('SELECT id FROM members WHERE username = ? AND id <> ?');
    $check->bind_param('si', $value['username'], $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) $errors[] = 'Username already taken.';
    $check = $conn->prepare('SELECT id FROM services WHERE id = ?');
    $check->bind_param('i', $value['services_id']);
    $check->execute();
    if ($check->get_result()->num_rows === 0) $errors[] = 'Select a valid service.';
    if (!$errors) {
        $update = $conn->prepare('UPDATE members SET fullname=?, username=?, gender=?, dor=?, contact=?, address=?, services_id=?, plan=? WHERE id=?');
        $update->bind_param('ssssssisi', $value['fullname'], $value['username'], $value['gender'], $value['dor'], $value['contact'], $value['address'], $value['services_id'], $value['plan'], $id);
        $update->execute();
        $_SESSION['success'] = 'Member information updated successfully';
        header('Location: member-list.php');
        exit;
    }
}
$res = [$value];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub - Admin</title>
        <link rel="stylesheet" href="css/update-member.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    </head>
    <body>
    <?php include 'includes/template.php'; ?>
<div class="content">
    <h2>Update Member's Information</h2>
    <?php foreach ($errors as $error) { ?><p style="color:#EE4266"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
    <div class="form-container">
        <?php foreach ($res as $value) { ?>
        <form action="" method="post">
            <div class="personal">
                <h3>Personal-Info</h3>
                <label for="fullname">Full Name: </label><input type="text" name="fullname" maxlength="100" value="<?=htmlspecialchars($value['fullname'], ENT_QUOTES, 'UTF-8')?>"><br>
                <label for="username">Username: </label><input type="text" name="username" maxlength="20" value="<?=htmlspecialchars($value['username'], ENT_QUOTES, 'UTF-8')?>"><br>
                <label for="gender">Gender: </label>
                <select name="gender">
                    <option value="male" <?php if($value['gender']=='male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if($value['gender']=='female') echo 'selected'; ?>>Female</option>
                    <option value="other" <?php if(in_array($value['gender'], ['other', 'others'], true)) echo 'selected'; ?> >Others</option>
                </select><br>
                <label for="dor">D.O.R: </label><input type="date" name="dor" value="<?=htmlspecialchars($value['dor'], ENT_QUOTES, 'UTF-8')?>">
            </div>
            <div class="other">
                <div class="contact">
                    <h3>Contact Details</h3>
                    <label for="contact">Contact Number: </label><input type="text" name="contact" value="<?=htmlspecialchars($value['contact'], ENT_QUOTES, 'UTF-8')?>"><br>
                    <label for="address">Address: </label><input type="text" name="address" maxlength="255" value="<?=htmlspecialchars($value['address'], ENT_QUOTES, 'UTF-8')?>"><br>
                </div>
                <hr>
                <div class="service">
                    <h3>Service Details</h3>
                    <label for="service">Services: </label>
                    <select name="services">
                        <?php
                        // Fetching service options from the services table
                        $service_query = "SELECT * FROM services";
                        $service_result = mysqli_query($conn, $service_query);
                        while ($service = mysqli_fetch_assoc($service_result)) {
                            // Comparing each option with the value stored in the database
                            $selected = ($service['id'] == $value['services_id']) ? 'selected' : '';
                            echo "<option value='" . $service['id'] . "' $selected>" . $service['service_name'] . "</option>";
                        }
                        ?>
                    </select><br>
                    <label for="duration">Duration: </label><select name="plan">
                        <option value="1" <?php if($value['plan']=='1') echo 'selected'; ?>>One Month</option>
                        <option value="3" <?php if($value['plan']=='3') echo 'selected'; ?>>Three Month</option>
                        <option value="6" <?php if($value['plan']=='6') echo 'selected'; ?>>Six Month</option>
                        <option value="12" <?php if($value['plan']=='12') echo 'selected'; ?>>One Year</option>
                    </select>
                </div>
            <button>Update Record</button>
            </div>
        </form>
        <?php } ?>
    </div>
</div>



<?php include 'includes/footer.php'?>