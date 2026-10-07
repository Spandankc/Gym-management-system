<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('member', 'login.php');
include '../dbcon.php';
include 'includes/authentication.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT) ?: 0;
$user_id = (int) $_SESSION['uid'];
$statement = $conn->prepare('SELECT * FROM todo WHERE id = ? AND user_id = ?');
$statement->bind_param('ii', $id, $user_id);
$statement->execute();
$value = $statement->get_result()->fetch_assoc();
if (!$value) {
    http_response_code(404);
    exit('Task not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = trim(is_string($_POST['task'] ?? null) ? $_POST['task'] : '');
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    if ($task === '' || strlen($task) > 30 || !in_array($status, ['In Progress', 'Pending'], true)) {
        $_SESSION['error'] = 'Enter a task of at most 30 characters and choose a valid status.';
    } else {
        $update = $conn->prepare('UPDATE todo SET task_status = ?, task_desc = ? WHERE id = ? AND user_id = ?');
        $update->bind_param('ssii', $status, $task, $id, $user_id);
        $update->execute();
        $_SESSION['success'] = 'Task Updated Successfully';
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub</title>
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="./css/boilerplate.css">
        <link rel="stylesheet" href="css/todo.css">
    </head>
    <body>
      <?php  include 'includes/boilerplate.php';?>
        <div class="content">
            <h2>Update Task</h2>
            <div class="form-container">
    <form action="" method="post">
        <div class="input">
            <label for="task">Please Enter your Task: </label>
            <input type="text" name="task" maxlength="30" required value="<?=fitness_escape($value['task_desc'])?>">
        </div>
        <div class="input">
            <label for="task">Please Select a Status: </label>
            <select name="status">
                <option value="In Progress" <?php if($value['task_status']=="In Progress"){echo "selected";}?>>In Progress</option>
                <option value="Pending" <?php if($value['task_status']=="Pending"){echo "selected";}?>>Pending</option>
            </select>
        </div>
        <input type="hidden" name="id" value="<?=$_SESSION['uid']?>">
        <button>Update</button>
        <?php if (isset($_SESSION['error'])) { echo fitness_escape($_SESSION['error']); unset($_SESSION['error']); } ?>
        <?php
            if(isset($_SESSION['success'])){
                echo fitness_escape($_SESSION['success']);
                unset($_SESSION['success']);
            }
        ?>
    </form>
    </div>
</div>


<?php include 'includes/footer.php'?>