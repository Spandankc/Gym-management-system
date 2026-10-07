<?php
require_once __DIR__ . '/../dbcon.php';
if (!empty($_SESSION['is_login']) && ($_SESSION['role'] ?? '') === 'member') {
    header('Location: dashboard.php');
    exit;
}
$errors = [];
$data = $_SESSION['signup_data'] ?? [];
foreach (['fullname', 'username', 'contact', 'address', 'gender'] as $field) {
    $$field = $data[$field] ?? '';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['fullname', 'username', 'contact', 'address', 'gender', 'password'] as $field) {
        $$field = is_string($_POST[$field] ?? null) ? $_POST[$field] : '';
        if ($field !== 'password') { $$field = trim($$field); }
    }
    foreach (['fullname' => 'Full Name', 'username' => 'Username', 'contact' => 'Contact Number', 'address' => 'Address'] as $field => $label) {
        if ($$field === '') { $errors[$field] = $label . ' is required.'; }
    }
    if (strlen($fullname) > 100) { $errors['fullname'] = 'Full Name must be at most 100 characters.'; }
    if (strlen($username) > 20) { $errors['username'] = 'Username must be at most 20 characters.'; }
    if (strlen($address) > 255) { $errors['address'] = 'Address must be at most 255 characters.'; }
    if (strlen($password) <= 8) { $errors['password'] = 'Password must be greater than 8 characters.'; }
    if (!preg_match('/^98\d{8}$/', $contact)) { $errors['contact'] = 'Contact Number must be 10 digits long and start with 98.'; }
    if (!in_array($gender, ['male', 'female', 'others'], true)) { $errors['gender'] = 'Please select a valid gender.'; }
    if (!isset($errors['username'])) {
        $check = $conn->prepare('SELECT id FROM members WHERE username = ? LIMIT 1');
        $check->bind_param('s', $username);
        $check->execute();
        if ($check->get_result()->num_rows) { $errors['username'] = 'Username already taken.'; }
    }
    if (!$errors) {
        $_SESSION['signup_data'] = [
            'fullname' => $fullname, 'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'contact' => $contact, 'address' => $address, 'gender' => $gender,
        ];
        header('Location: signup_step2.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Member Sign Up</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
<main class="container">
    <a class="brand" href="../index.php">Fitness Hub</a>
    <h1>Member Sign Up</h1>
    <p class="step">Step 1 of 2: Your details</p>
    <div class="form-container">
        <form action="signup.php" method="post">
            <div class="input-container">
                <?php foreach (['fullname' => 'Full Name', 'username' => 'Username', 'password' => 'Password', 'contact' => 'Contact Number', 'address' => 'Address'] as $field => $label): ?>
                    <label for="<?= $field ?>"><?= $label ?></label>
                    <?php if (isset($errors[$field])): ?><p class="error" role="alert"><?= fitness_escape($errors[$field]) ?></p><?php endif; ?>
                    <div class="input"><input id="<?= $field ?>" name="<?= $field ?>" type="<?= $field === 'password' ? 'password' : ($field === 'contact' ? 'tel' : 'text') ?>" placeholder="<?= $label ?>" value="<?= $field === 'password' ? '' : fitness_escape($$field) ?>" <?= $field === 'password' ? 'minlength="9" autocomplete="new-password"' : '' ?> <?= $field === 'contact' ? 'inputmode="numeric" pattern="98[0-9]{8}" maxlength="10"' : '' ?> <?= $field === 'username' ? 'maxlength="20" autocomplete="username"' : '' ?> <?= $field === 'fullname' ? 'maxlength="100" autocomplete="name"' : '' ?> <?= $field === 'address' ? 'maxlength="255" autocomplete="street-address"' : '' ?> required></div>
                <?php endforeach; ?>
                <label for="gender">Gender</label>
                <?php if (isset($errors['gender'])): ?><p class="error" role="alert"><?= fitness_escape($errors['gender']) ?></p><?php endif; ?>
                <div class="input"><select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <?php foreach (['male' => 'Male', 'female' => 'Female', 'others' => 'Others'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $gender === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select></div>
            </div>
            <div class="action next"><a href="login.php">Login</a><button type="submit">Next</button></div>
        </form>
    </div>
    <p class="home-link"><a href="../index.php">&larr; Home</a></p>
</main>
</body>
</html>
