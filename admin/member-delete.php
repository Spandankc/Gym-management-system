<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $conn->begin_transaction();
    try {
        foreach (['DELETE FROM todo WHERE user_id = ?', 'DELETE FROM progress WHERE member_id = ?', 'DELETE FROM members WHERE id = ?'] as $sql) {
            $statement = $conn->prepare($sql);
            $statement->bind_param('i', $id);
            $statement->execute();
        }
        $conn->commit();
        $_SESSION['success'] = 'Member Deleted Successfully';
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['error'] = 'Unable to delete this member.';
    }
}
header('Location: member-list.php');
exit;
