<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $conn->begin_transaction();
    try {
        $clear = $conn->prepare('UPDATE members SET trainer_id = NULL WHERE trainer_id = ?');
        $clear->bind_param('i', $id);
        $clear->execute();
        $clear_schedule = $conn->prepare('UPDATE class_schedules SET trainer_id = NULL WHERE trainer_id = ?');
        $clear_schedule->bind_param('i', $id);
        $clear_schedule->execute();
        $delete = $conn->prepare('DELETE FROM staffs WHERE id = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
        $conn->commit();
        $_SESSION['success'] = 'Staff Member Deleted';
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['error'] = 'Unable to delete this staff member.';
    }
}
header('Location: staff-manage.php');
exit;
