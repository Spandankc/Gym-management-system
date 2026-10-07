<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

$id = isset($_GET['id']) && is_string($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;
if ($id === false) {
    http_response_code(404);
    exit('Announcement not found.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $statement = $conn->prepare('DELETE FROM announcements WHERE id = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $deleted = $statement->affected_rows;
    $statement->close();
    if (!$deleted) {
        http_response_code(404);
        exit('Announcement not found.');
    }
    $_SESSION['success'] = 'Announcement Deleted';
    header('Location: announcement-manage.php');
    exit;
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Unable to delete announcement. Please try again.');
}
