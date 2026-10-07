<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { header('Location: staff-manage.php'); exit; }
$errors = [];
$designations = ['Trainer', 'Cashier', 'Gym Assistant', 'Front Desk Officer', 'Manager'];
$select = $conn->prepare('SELECT * FROM staffs WHERE id = ?');
$select->bind_param('i', $id);
$select->execute();
$value = $select->get_result()->fetch_assoc();
if (!$value) { http_response_code(404); exit('Staff member not found.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['fullname', 'username', 'email', 'contact', 'address', 'gender', 'designation'] as $field) {
        $value[$field] = trim($_POST[$field] ?? '');
    }
    foreach (['fullname', 'username', 'address'] as $field) {
        if ($value[$field] === '' || mb_strlen($value[$field]) > 100) $errors[] = 'Enter a valid ' . $field . ' (at most 100 characters).';
    }
    if (!filter_var($value['email'], FILTER_VALIDATE_EMAIL) || strlen($value['email']) > 100) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^98\d{8}$/', $value['contact'])) $errors[] = 'Contact Number must be 10 digits long and start with 98.';
    if (!in_array($value['gender'], ['Male', 'Female', 'Others'], true)) $errors[] = 'Select a gender.';
    if (!in_array($value['designation'], $designations, true)) $errors[] = 'Select a designation.';
    $check = $conn->prepare('SELECT id FROM staffs WHERE username = ? AND id <> ?');
    $check->bind_param('si', $value['username'], $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) $errors[] = 'Username already taken.';
    if (!$errors) {
        $update = $conn->prepare('UPDATE staffs SET fullname=?, username=?, gender=?, email=?, contact=?, address=?, designation=? WHERE id=?');
        $update->bind_param('sssssssi', $value['fullname'], $value['username'], $value['gender'], $value['email'], $value['contact'], $value['address'], $value['designation'], $id);
        $update->execute();
        $_SESSION['success'] = 'Staff information updated successfully';
        header('Location: staff-manage.php');
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
        <link rel="stylesheet" href="css/staff-update.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/template.php';?>
<div class="content">
    <h2><i class="fa-solid fa-edit" style="margin-right: 0.5rem"></i>Update Staff's Information</h2>
    <?php foreach ($errors as $error) { ?><p style="color:#EE4266"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
    <div class="form-container">
        <?php foreach($res as $value) { ?>
        <form action="" method="post">
            <div class="left">
                <label for="fullname">Enter Fullname</label>
                <input type="text" name="fullname" value="<?=htmlspecialchars($value['fullname'], ENT_QUOTES, 'UTF-8')?>">
                <label for="username">Enter Username</label>
                <input type="text" name="username" value="<?=htmlspecialchars($value['username'], ENT_QUOTES, 'UTF-8')?>">
                <label for="gender">Gender</label>
                <select name="gender">
                    <option value="Male" <?php if($value['gender']=="Male") echo "selected";?> >Male</option>
                    <option value="Female" <?php if($value['gender']=="Female") echo "selected";?>>Female</option>
                    <option value="Others" <?php if($value['gender']=="Others") echo "selected";?>>Others</option>
                </select>
                <label for="email">Email</label>
                <input type="email" name="email" value="<?=htmlspecialchars($value['email'], ENT_QUOTES, 'UTF-8')?>">
            </div>
            <div class="right">
                <label for="contact">Contact</label>
                <input type="text" name="contact" value="<?=htmlspecialchars($value['contact'], ENT_QUOTES, 'UTF-8')?>">
                <label for="address">Address</label>
                <input type="text" name="address" value="<?=htmlspecialchars($value['address'], ENT_QUOTES, 'UTF-8')?>">
                <label for="designation">Designation</label>
                <select name="designation">
                    <option value="Cashier" <?php if($value['designation']=="Cashier") echo "selected";?>>Cashier</option>
                    <option value="Trainer" <?php if($value['designation']=="Trainer") echo "selected";?>>Trainer</option>
                    <option value="Gym Assistant" <?php if($value['designation']=="Gym Assistant") echo "selected";?>>Gym Assistant</option>
                    <option value="Front Desk Officer" <?php if($value['designation']=="Front Desk Officer") echo "selected";?>>Front Desk Officer</option>
                    <option value="Manager" <?php if($value['designation']=="Manager") echo "selected";?>>Manager</option>
                </select>
                <button name="add">Update Staff</button>
            </div>
        </form>
        <?php } ?>
        <p class="back"><a href="staff-manage.php">&larr;Go back</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>