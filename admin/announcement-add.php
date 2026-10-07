<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

$message = '';
$date = '';
$errors = [];
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = isset($_POST['message']) && is_string($_POST['message']) ? trim($_POST['message']) : '';
    $date = isset($_POST['date']) && is_string($_POST['date']) ? trim($_POST['date']) : '';
    $messageLength = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
    if ($message === '' || $messageLength > 100) {
        $errors[] = 'Enter an announcement of 1 to 100 characters.';
    }
    $announcementDate = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
    if (!$announcementDate || $announcementDate->format('Y-m-d') !== $date || (int) $announcementDate->format('Y') < 1000) {
        $errors[] = 'Enter a valid announcement date.';
    }
    if (!$errors) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $statement = $conn->prepare('INSERT INTO announcements (message, date) VALUES (?, ?)');
            $statement->bind_param('ss', $message, $date);
            $statement->execute();
            $statement->close();
            $_SESSION['success'] = 'Announcement Added Successfully!';
            header('Location: announcement-add.php');
            exit;
        } catch (mysqli_sql_exception $exception) {
            $errors[] = 'Unable to publish the announcement. Please try again.';
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
        <link rel="stylesheet" href="css/announcement-add.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    </head>
    <body>
    <?php include 'includes/template.php'; ?>

<div class="content">
    <h2><i class="fa-solid fa-bullhorn" style="margin-right: 0.5rem"></i>Announcements</h2>
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="message"><h3><?= $escape($_SESSION['success']); unset($_SESSION['success']); ?></h3></div>
    <?php } ?>
    <?php foreach ($errors as $error) { ?>
        <div class="message" role="alert"><?= $escape($error) ?></div>
    <?php } ?>
    <hr>
    <a href="announcement-manage.php"><button class="manage">Manage Your Announcements</button></a>
    <div class="form-container">
        <form action="" method="post">
            <table>
                <thead>
                    <tr>
                        <th><h3>Make Announcement</h3></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <textarea name="message" placeholder="Enter announcement here..." maxlength="100" required><?= $escape($message) ?></textarea><br>
                            <label for="date">Date:&nbsp;&nbsp;</label><input id="date" type="date" name="date" required value="<?= $escape($date) ?>"><br>
                            <button class="publish" type="submit">Publish</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
