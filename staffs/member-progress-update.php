<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('trainer', 'index.php');
require_once __DIR__ . '/../dbcon.php';
include "includes/authentication.php";

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) { http_response_code(404); exit('Member not found.'); }
require_once __DIR__ . '/includes/member-access.php';
fitness_require_assigned_member($conn, $id);
$member_check = $conn->prepare('SELECT id FROM members WHERE id = ?');
$member_check->bind_param('i', $id);
$member_check->execute();
if ($member_check->get_result()->num_rows === 0) { http_response_code(404); exit('Member not found.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ini_weight = filter_var($_POST['ini_weight'] ?? '', FILTER_VALIDATE_INT);
    $curr_weight = filter_var($_POST['curr_weight'] ?? '', FILTER_VALIDATE_INT);
    $ini_bodytype = trim($_POST['ini_bodytype'] ?? '');
    $curr_bodytype = trim($_POST['curr_bodytype'] ?? '');
    if (!$ini_weight || $ini_weight < 1 || $ini_weight > 1000 || !$curr_weight || $curr_weight < 1 || $curr_weight > 1000) {
        $errors[] = 'Enter initial and current weight between 1 and 1000 kg.';
    }
    if ($ini_bodytype === '' || $curr_bodytype === '' || mb_strlen($ini_bodytype) > 100 || mb_strlen($curr_bodytype) > 100) {
        $errors[] = 'Enter both body types using at most 100 characters.';
    }
    if (!$errors) {
        $conn->begin_transaction();
        try {
            $lock = $conn->prepare('SELECT id FROM members WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $id);
            $lock->execute();
            $lock->get_result()->fetch_assoc();
            $check = $conn->prepare('SELECT id FROM progress WHERE member_id = ?');
            $check->bind_param('i', $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $statement = $conn->prepare('UPDATE progress SET ini_weight=?, curr_weight=?, ini_bodytype=?, curr_bodytype=? WHERE member_id=?');
            } else {
                $statement = $conn->prepare('INSERT INTO progress (ini_weight, curr_weight, ini_bodytype, curr_bodytype, member_id) VALUES (?, ?, ?, ?, ?)');
            }
            $statement->bind_param('iissi', $ini_weight, $curr_weight, $ini_bodytype, $curr_bodytype, $id);
            $statement->execute();
            $conn->commit();
            $_SESSION['success'] = 'User Progress Updated Successfully';
            header('Location: member-progress.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $errors[] = 'Unable to save progress. Try again.';
        }
    }
}

//Displaying existing values

$sql = "SELECT members.*,services.service_name, progress.ini_weight, progress.curr_weight, progress.ini_bodytype, progress.curr_bodytype
        FROM members
        LEFT JOIN progress ON members.id = progress.member_id
        LEFT JOIN services ON members.services_id = services.id
        WHERE members.id = $id";

$res = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($res)) {
    // Displaying values
    ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub - Trainer</title>
        <link rel="stylesheet" href="css/progress-update.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    </head>
    <body>

        <?php include 'includes/template.php';?>
        <div class="content">
            <h2><i class="fa-solid fa-edit" style="margin-right: 0.5rem"></i>Update Member's Progress</h2>
            <?php foreach ($errors as $error) { ?><p style="color:#EE4266"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php } ?>
            <div class="form-container">
                <form action="" method="post">
                    <table>
                    <tr>
                        <td><label for="name">Fullname: </label></td>
                        <td><b><?= fitness_escape($row['fullname']) ?></b></td>
                    </tr>
                    <tr>
                        <td><label for="service">Service Taken: </label></td>
                        <td><b><?= fitness_escape($row['service_name']) ?></b></td>
                    </tr>
                    <tr>
                        <td><label for="ini_weight">Initial Weight(in Kg): </label></td>
                        <td><input type="number" name="ini_weight" min="1" max="1000" required value="<?= htmlspecialchars((string)($ini_weight ?? $row['ini_weight'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="curr_weight">Current Weight(in Kg)</label></td>
                        <td><input type="number" name="curr_weight" min="1" max="1000" required value="<?= htmlspecialchars((string)($curr_weight ?? $row['curr_weight'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="ini_bodytype">Initial Body Type:</label></td>
                        <td><input type="text" name="ini_bodytype" value="<?= htmlspecialchars($ini_bodytype ?? $row['ini_bodytype'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="curr_bodytype">Current Body Type:</label></td>
                        <td><input type="text" name="curr_bodytype" value="<?= htmlspecialchars($curr_bodytype ?? $row['curr_bodytype'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
                    </tr>
            </table>
            <button>Update</button>
        </form>
    </div>
</div>

<?php } include 'includes/footer.php'; ?>