<?php
require_once __DIR__ . '/../dbcon.php';

if (!empty($_SESSION['is_login']) && ($_SESSION['role'] ?? '') === 'member') {
    header('Location: dashboard.php');
    exit;
}
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (fitness_authenticate($conn, 'member', $username, $password)) {
        header('Location: dashboard.php');
        exit;
    }
    $_SESSION['error'] = 'Incorrect Username or Password';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Member Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <main class="container">
        <a class="brand" href="../index.php">Fitness Hub</a>
        <h1>Member Login</h1>
        <div class="form-container">
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success" role="status"><?= fitness_escape($_SESSION['success']) ?></p>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error" role="alert"><?= fitness_escape($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <form action="login.php" method="post">
                <label class="field" for="username">Username</label>
                <div class="input"><input id="username" type="text" name="username" placeholder="Username" value="<?= fitness_escape($username) ?>" autocomplete="username" required></div>
                <label class="field" for="password">Password</label>
                <div class="input"><input id="password" type="password" name="password" placeholder="Password" autocomplete="current-password" required></div>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="action"><a href="../index.php">&larr; Home</a><a href="signup.php">Sign Up</a></div>
        <div class="role-links"><a href="../staffs/index.php">Trainer Login</a><a href="../admin/index.php">Admin Login</a></div>
    </main>
</body>
</html>
