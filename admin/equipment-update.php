<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$id = isset($_GET['id']) && is_string($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($id === false) {
    http_response_code(404);
    exit('Equipment not found.');
}
$statement = $conn->prepare('SELECT equipment.*, vendors.vendor_name, vendors.address, vendors.contact FROM equipment LEFT JOIN vendors ON equipment.vendor_id = vendors.id WHERE equipment.id = ?');
$statement->bind_param('i', $id);
$statement->execute();
$equipment = $statement->get_result()->fetch_assoc();
$statement->close();
if (!$equipment) {
    http_response_code(404);
    exit('Equipment not found.');
}
$values = [
    'name' => $equipment['name'], 'description' => $equipment['description'], 'date' => $equipment['date'],
    'quantity' => $equipment['quantity'], 'vendor' => $equipment['vendor_name'] ?? '',
    'address' => $equipment['address'] ?? '', 'contact' => $equipment['contact'] ?? '', 'cost' => $equipment['amount'],
];

$errors = [];
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$length = static function ($value) {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $values[$field] = isset($_POST[$field]) && is_string($_POST[$field]) ? trim($_POST[$field]) : '';
    }
    foreach (['name' => 30, 'description' => 255, 'vendor' => 100, 'address' => 100] as $field => $maximum) {
        if ($values[$field] === '' || $length($values[$field]) > $maximum) {
            $errors[] = ucfirst($field) . ' is required and must be no longer than ' . $maximum . ' characters.';
        }
    }
    $purchaseDate = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $values['date']) ? DateTimeImmutable::createFromFormat('!Y-m-d', $values['date']) : false;
    if (!$purchaseDate || $purchaseDate->format('Y-m-d') !== $values['date'] || (int) $purchaseDate->format('Y') < 1000) {
        $errors[] = 'Enter a valid purchase date.';
    }
    if (!preg_match('/^[0-9]{10}$/', $values['contact'])) {
        $errors[] = 'Vendor contact must contain 10 digits.';
    }
    $quantity = filter_var($values['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
    $cost = filter_var($values['cost'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
    if ($quantity === false) {
        $errors[] = 'Quantity must be a positive whole number.';
    }
    if ($cost === false) {
        $errors[] = 'Cost must be a non-negative whole number.';
    }
    if ($quantity !== false && $cost !== false && $quantity * $cost > 2147483647) {
        $errors[] = 'The total equipment cost is too large.';
    }

    if (!$errors) {
        $transactionStarted = false;
        try {
            $conn->begin_transaction();
            $transactionStarted = true;
            $statement = $conn->prepare('SELECT id FROM equipment WHERE id = ? FOR UPDATE');
            $statement->bind_param('i', $id);
            $statement->execute();
            $existingEquipment = $statement->get_result()->fetch_assoc();
            $statement->close();
            if (!$existingEquipment) {
                $conn->rollback();
                http_response_code(404);
                exit('Equipment not found.');
            }
            $vendorStatement = $conn->prepare('SELECT id FROM vendors WHERE vendor_name = ? LIMIT 1 FOR UPDATE');
            $vendorStatement->bind_param('s', $values['vendor']);
            $vendorStatement->execute();
            $vendor = $vendorStatement->get_result()->fetch_assoc();
            $vendorStatement->close();
            if ($vendor) {
                $vendorId = (int) $vendor['id'];
                $vendorStatement = $conn->prepare('UPDATE vendors SET address = ?, contact = ? WHERE id = ?');
                $vendorStatement->bind_param('ssi', $values['address'], $values['contact'], $vendorId);
            } else {
                $vendorStatement = $conn->prepare('INSERT INTO vendors (vendor_name, address, contact) VALUES (?, ?, ?)');
                $vendorStatement->bind_param('sss', $values['vendor'], $values['address'], $values['contact']);
            }
            $vendorStatement->execute();
            if (!$vendor) {
                $vendorId = (int) $conn->insert_id;
            }
            $vendorStatement->close();
            $totalCost = $quantity * $cost;

            $statement = $conn->prepare('UPDATE equipment SET name = ?, description = ?, date = ?, quantity = ?, vendor_id = ?, amount = ?, total_amount = ? WHERE id = ?');
            $statement->bind_param('sssiiiii', $values['name'], $values['description'], $values['date'], $quantity, $vendorId, $cost, $totalCost, $id);
            $statement->execute();
            $statement->close();
            $conn->commit();
            $_SESSION['success'] = 'Information Updated';
            header('Location: equipment-list.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ($transactionStarted) {
                $conn->rollback();
            }
            $errors[] = 'Unable to update equipment. Please try again.';
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
        <link rel="stylesheet" href="css/equipment-update.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    </head>
    <body>
    <?php include 'includes/template.php'; ?>

<div class="content">
    <h2>Update Equipment Information</h2>
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="message"><h3><?= $escape($_SESSION['success']); unset($_SESSION['success']); ?></h3></div>
    <?php } ?>
    <?php foreach ($errors as $error) { ?>
        <div class="message" role="alert"><?= $escape($error) ?></div>
    <?php } ?>
    <div class="form-container">
        <form action="" method="post">
            <div class="left">
                <h3>Equipment-Info</h3>
                <label for="name">Equipment Name</label>
                <input id="name" type="text" name="name" maxlength="30" required value="<?= $escape($values['name']) ?>"><br>
                <label for="description">Description</label>
                <input id="description" type="text" name="description" maxlength="255" required value="<?= $escape($values['description']) ?>"><br>
                <label for="date">Date of Purchase</label>
                <input id="date" type="date" name="date" required value="<?= $escape($values['date']) ?>"><br>
                <label for="quantity">Quantity</label>
                <input id="quantity" type="number" name="quantity" min="1" max="2147483647" step="1" required value="<?= $escape($values['quantity']) ?>"><br>
            </div>
            <div class="right">
                <div class="vendor-detail">
                    <h3>Vendor Details</h3>
                    <label for="vendor">Vendor</label>
                    <input id="vendor" type="text" name="vendor" maxlength="100" required value="<?= $escape($values['vendor']) ?>"><br>
                    <label for="address">Address</label>
                    <input id="address" type="text" name="address" maxlength="100" required value="<?= $escape($values['address']) ?>"><br>
                    <label for="contact">Contact</label>
                    <input id="contact" type="text" name="contact" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required value="<?= $escape($values['contact']) ?>"><br>
                </div>
                <div class="pricing">
                    <h3>Pricing Details</h3>
                    <label for="cost">Cost Per Item</label>
                    <input id="cost" type="number" name="cost" min="0" max="2147483647" step="1" required value="<?= $escape($values['cost']) ?>"><br>
                </div>
                <button type="submit">Update Information</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
