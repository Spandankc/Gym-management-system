<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/../includes/members.php';
$errors = []; // Array to store error messages
$dor = date('Y-m-d');

if (!empty($_POST)) {
    $fname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $services = $_POST['services'] ?? '';
    $dor = $_POST['dor'] ?? date('Y-m-d');
    $registration_date = DateTimeImmutable::createFromFormat('!Y-m-d', $dor);
    if (!$registration_date || $registration_date->format('Y-m-d') !== $dor || $dor > date('Y-m-d')) {
        $errors['dor'] = 'Enter a valid registration date that is not in the future.';
    }

    // Validate all fields are filled
    if (empty($fname)) {
        $errors['fullname'] = "Full Name is required.";
    }
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) <= 8) {
        $errors['password'] = "Password must be greater than 8 characters.";
    }
    if (!preg_match('/^98\d{8}$/', $contact)) {
        $errors['contact'] = 'Contact Number must be 10 digits long and start with 98.';
    }
    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }
    if (!in_array($gender, ['male', 'female', 'other', 'others'], true)) {
        $errors['gender'] = 'Select a valid gender.';
    }
    if (!in_array((string)$plan, ['1', '3', '6', '12'], true)) {
        $errors['plan'] = 'Select a valid plan.';
    }
    if (empty($services)) {
        $errors['services'] = "Service is required.";
    }

    foreach (['fullname' => $fname, 'username' => $username, 'address' => $address] as $field => $value) {
        $maximum = ['fullname' => 100, 'username' => 20, 'address' => 255][$field];
        if (mb_strlen($value) > $maximum) $errors[$field] = 'Use at most ' . $maximum . ' characters.';
    }
    if (empty($errors['username'])) {
        $check = $conn->prepare('SELECT id FROM members WHERE username = ?');
        $check->bind_param('s', $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) $errors['username'] = 'Username already taken.';
    }
    if (empty($errors['services'])) {
        $service_id = (int)$services;
        $check = $conn->prepare('SELECT id FROM services WHERE id = ?');
        $check->bind_param('i', $service_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) $errors['services'] = 'Select a valid service.';
    }
    if (!$errors) {
        try {
            fitness_create_member($conn, [
                'fullname' => $fname, 'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'contact' => $contact, 'address' => $address, 'gender' => $gender,
                'plan' => (int)$plan, 'services' => (int)$services, 'dor' => $dor
            ]);
            $_SESSION['success'] = 'Registered Successfully';
            header('Location: member-list.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            $_SESSION['error'] = 'Error Registering User. Check the member information and try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Admin</title>
    <link rel="stylesheet" href="css/member-registration.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <style>
        .errors { color: red; }
    </style>
</head>
<body>

    <?php include 'includes/template.php';?>
    <div class="content">
        <h2>New Member Register Form</h2>
        <?php if(isset($_SESSION['success'])){ ?> <div class="message"><h3><?=$_SESSION['success']; unset($_SESSION['success'])?></h3></div><?php } ?>
        <?php if(isset($_SESSION['error'])){ ?> <div class="error"><h3><?=$_SESSION['error']; unset($_SESSION['error'])?></h3></div><?php } ?>
        <div class="form-container">
            <form action="" method="post">
                <div class="personal">
                    <h3>Personal-Info</h3>
                    <label for="fullname">Full Name: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['fullname'] ?? ''; ?></span><?php } ?></label>
                    <input type="text" name="fullname" maxlength="100" value="<?php echo htmlspecialchars($fname ?? '', ENT_QUOTES); ?>"><br>


                    <label for="username">Username: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['username'] ?? ''; ?></span><?php } ?></label>
                    <input type="text" name="username" maxlength="20" value="<?php echo htmlspecialchars($username ?? '', ENT_QUOTES); ?>"><br>


                    <label for="password">Password: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['password'] ?? ''; ?></span><?php } ?></label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($password ?? '', ENT_QUOTES); ?>"><br>


                    <label for="gender">Gender: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['gender'] ?? ''; ?></span><?php } ?></label>
                    <select name="gender">
                        <option value="" disabled hidden selected>Select Gender</option>
                        <option value="male" <?php if (($gender ?? '') == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if (($gender ?? '') == 'female') echo 'selected'; ?>>Female</option>
                        <option value="other" <?php if (($gender ?? '') == 'other') echo 'selected'; ?>>Others</option>
                    </select><br>

                    <label for="dor">D.O.R: <span class="errors"><?= $errors['dor'] ?? '' ?></span></label>
                    <input type="date" name="dor" value="<?php echo htmlspecialchars($dor ?? '', ENT_QUOTES); ?>"><br>
                </div>
                <div class="other">
                    <div class="contact">
                        <h3>Contact Details</h3>
                        <label for="contact">Contact Number: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['contact'] ?? ''; ?></span><?php } ?></label>
                        <input type="number" name="contact" value="<?php echo htmlspecialchars($contact ?? '', ENT_QUOTES); ?>"><br>


                        <label for="address">Address: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['address'] ?? ''; ?></span><?php } ?></label>
                        <input type="text" name="address" maxlength="255" value="<?php echo htmlspecialchars($address ?? '', ENT_QUOTES); ?>"><br>

                    </div>
                    <hr>
                    <div class="service">
                        <h3>Service Details</h3>
                        <label for="service">Services: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['services'] ?? ''; ?></span><?php } ?></label>
                        <select name="services">
                            <option value="" disabled hidden selected>Select Service</option>
                            <?php
                            $service_query = "SELECT id, service_name FROM services";
                            $service_result = mysqli_query($conn, $service_query);
                            if ($service_result && mysqli_num_rows($service_result) > 0) {
                                while ($row = mysqli_fetch_assoc($service_result)) {
                                    $service_id = $row['id'];
                                    $service_name = $row['service_name'];
                                    echo "<option value='$service_id' ".(($services ?? '') == $service_id ? 'selected' : '').">$service_name</option>";
                                }
                            }
                            ?>
                        </select><br>

                        <br>
                        <label for="duration">Duration: <?php if(!empty($errors)){?><span class="errors"><?php echo $errors['plan'] ?? ''; ?></span><?php } ?></label>
                        <select name="plan">
                            <option value="" disabled hidden selected>Select Plan</option>
                            <option value="1" <?php if (($plan ?? '') == '1') echo 'selected'; ?>>One Month</option>
                            <option value="3" <?php if (($plan ?? '') == '3') echo 'selected'; ?>>Three Month</option>
                            <option value="6" <?php if (($plan ?? '') == '6') echo 'selected'; ?>>Six Month</option>
                            <option value="12" <?php if (($plan ?? '') == '12') echo 'selected'; ?>>One Year</option>
                        </select><br>
                    </div>
                    <button type="submit">Register Member</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
