<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../dbcon.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (fitness_authenticate($conn, 'trainer', $username, $password)) {
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
    <title>Fitness Hub | Trainer Login</title>
    <link rel="stylesheet" href="./css/login.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <h1>Staff Login</h1>
        <div class="form-container">
            <p style="color: #EE4266">
            <?php
            if(isset($_SESSION['error'])){
                echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8');
                unset($_SESSION['error']);
            }
            ?></p>
            <form action="" method="post">
                <div class="input"><span><i class="fa-solid fa-at"></i></span><input type="text" autocomplete="username" aria-label="Username" name="username" placeholder="Username" required></div>
                <div class="input"><i class="fa-solid fa-lock"></i><input type="password" autocomplete="current-password" aria-label="Password" name="password" placeholder="Password" required></div>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="action" style="display:flex; flex-wrap:wrap; gap:1rem; margin-top:1rem">
            <a href="../index.php">Home</a>
            <a href="../customer/login.php">Member Login</a>
            <a href="../staffs/index.php">Trainer Login</a>
            <a href="../admin/index.php">Admin Login</a>
        </div>
    </div>
</body>
</html>