<?php
require_once __DIR__ . '/../includes/auth.php';
if (!isset($_SESSION['signup_data']['password_hash'])) {
    header('Location: signup.php');
    exit;
}
require_once __DIR__ . '/../dbcon.php';
require_once __DIR__ . '/../includes/members.php';
$errors = [];
$plan = is_string($_POST['plan'] ?? null) ? $_POST['plan'] : '';
$selectedService = filter_var($_POST['services'] ?? '', FILTER_VALIDATE_INT) ?: 0;
$services = $conn->query('SELECT id, service_name, cost FROM services ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$serviceIds = array_map('intval', array_column($services, 'id'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($plan, ['1', '3', '6', '12'], true)) { $errors['plan'] = 'Please select a valid plan.'; }
    if (!in_array($selectedService, $serviceIds, true)) { $errors['services'] = 'Please select a valid service.'; }
    if (!$errors) {
        $data = $_SESSION['signup_data'];
        $data['plan'] = $plan;
        $data['services'] = $selectedService;
        try {
            fitness_create_member($conn, $data);
            unset($_SESSION['signup_data']);
            $_SESSION['success'] = 'Registered Successfully. Please log in to Fitness Hub.';
            header('Location: login.php');
            exit;
        } catch (mysqli_sql_exception $error) {
            $errors['registration'] = $error->getCode() === 1062
                ? 'Username already taken. Go back and choose another username.'
                : 'Unable to register. Please try again.';
            error_log('Fitness Hub registration: ' . $error->getMessage());
        }
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
    <p class="step">Step 2 of 2: Service and membership</p>
    <div class="form-container">
        <?php if (isset($errors['registration'])): ?><p class="error" role="alert"><?= fitness_escape($errors['registration']) ?></p><?php endif; ?>
        <form action="signup_step2.php" method="post">
            <fieldset class="services-container">
                <legend>Select Service</legend>
                <div class="single-service">
                    <?php foreach ($services as $service): ?>
                        <label class="service-card">
                            <input type="radio" name="services" value="<?= (int) $service['id'] ?>" <?= $selectedService === (int) $service['id'] ? 'checked' : '' ?> required>
                            <h3><?= fitness_escape($service['service_name']) ?></h3>
                            <p>Price: Rs.<?= fitness_escape($service['cost']) ?>/month</p>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php if (isset($errors['services'])): ?><p class="error" role="alert"><?= fitness_escape($errors['services']) ?></p><?php endif; ?>
            <div class="input-container plan">
                <label for="plan">Membership Plan</label>
                <?php if (isset($errors['plan'])): ?><p class="error" role="alert"><?= fitness_escape($errors['plan']) ?></p><?php endif; ?>
                <div class="input"><select id="plan" name="plan" required>
                    <option value="">Select Plan</option>
                    <?php foreach (['1' => 'One Month', '3' => 'Three Months', '6' => 'Six Months', '12' => 'One Year'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $plan === (string) $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select></div>
            </div>
            <div class="action next"><a href="signup.php">&larr; Back</a><button type="submit">Submit Details</button></div>
        </form>
        <p class="home-link">Already a member? <a href="login.php">Login</a></p>
    </div>
</main>
</body>
</html>
