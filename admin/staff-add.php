<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$errors = [];
$designations = ['Trainer', 'Cashier', 'Gym Assistant', 'Front Desk Officer', 'Manager'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $designation = $_POST['designation'] ?? 'Trainer';
    foreach (['fullname' => $fname, 'username' => $username, 'address' => $address] as $field => $value) {
        if ($value === '') $errors[$field] = 'This field is required.';
        elseif (mb_strlen($value) > 100) $errors[$field] = 'Use at most 100 characters.';
    }
    if (strlen($password) < 9) $errors['password'] = 'Password must be greater than 8 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) $errors['email'] = 'Enter a valid email address.';
    if (!preg_match('/^98\d{8}$/', $contact)) $errors['contact'] = 'Contact Number must be 10 digits long and start with 98.';
    if (!in_array($gender, ['Male', 'Female', 'Others'], true)) $errors['gender'] = 'Select a gender.';
    if (!in_array($designation, $designations, true)) $errors['designation'] = 'Select a valid designation.';
    if (!$errors) {
        $check = $conn->prepare('SELECT id FROM staffs WHERE username = ?');
        $check->bind_param('s', $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) $errors['username'] = 'Username already taken.';
    }
    if (!$errors) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare('INSERT INTO staffs (fullname, username, password, email, contact, address, gender, designation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insert->bind_param('ssssssss', $fname, $username, $hashed_password, $email, $contact, $address, $gender, $designation);
        if ($insert->execute()) {
            $_SESSION['success'] = 'New Staff Added';
            header('Location: staff-manage.php');
            exit;
        }
        $_SESSION['error'] = 'Error Adding Staff';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Admin</title>
    <link rel="stylesheet" href="css/staff-add.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <style>
        .errors { color: red; }
    </style>
</head>
<body>

    <?php include 'includes/template.php';?>
    <div class="content">
        <h2><i class="fa-solid fa-edit" style="margin-right: 0.5rem"></i>Staff Registration Form</h2>
        <?php if(isset($_SESSION['success'])){ ?> <div class="message"><h3><?=$_SESSION['success']; unset($_SESSION['success'])?></h3></div><?php } ?>
        <?php if(isset($_SESSION['error'])){ ?> <div class="error"><h3><?=$_SESSION['error']; unset($_SESSION['error'])?></h3></div><?php } ?>
        <div class="form-container">
            <form action="" method="post">
                <div class="left">
                    <label for="fullname">Enter Fullname: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['fullname'] ?? ''; ?></span><?php } ?></label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($fname ?? '', ENT_QUOTES); ?>"><br>

                    <label for="username">Enter Username: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['username'] ?? ''; ?></span><?php } ?></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES); ?>"><br>

                    <label for="password">Password: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['password'] ?? ''; ?></span><?php } ?></label>
                    <input type="password" name="password"><br>

                    <label for="gender">Gender: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['gender'] ?? ''; ?></span><?php } ?></label>
                    <select name="gender">
                        <option value="" disabled hidden selected>Select Gender</option>
                        <option value="Male" <?php if (($gender ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if (($gender ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Others" <?php if (($gender ?? '') == 'Others') echo 'selected'; ?>>Others</option>
                    </select><br>
                </div>
                <div class="right">
                    <label for="email">Email: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['email'] ?? ''; ?></span><?php } ?></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES); ?>"><br>

                    <label for="contact">Contact: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['contact'] ?? ''; ?></span><?php } ?></label>
                    <input type="number" name="contact" placeholder="Contact Number" pattern="98\d{8}" title="Contact Number must be 10 digits long and start with 98" value="<?php echo htmlspecialchars($contact ?? '', ENT_QUOTES); ?>" >
                    <label for="address">Address: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['address'] ?? ''; ?></span><?php } ?></label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($address ?? '', ENT_QUOTES); ?>"><br>

                    <label for="designation">Designation: <span class="errors"><?php echo $errors['designation'] ?? ''; ?></span></label>
                    <select name="designation" id="designation">
                        <?php foreach ($designations as $option) { ?>
                            <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= ($designation ?? 'Trainer') === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php } ?>
                    </select><br>
                    <button type="submit" name="add">Add Staff</button>
                </div>
            </form>
            <p class="back"><a href="staff-manage.php">&larr;Go back</a></p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
