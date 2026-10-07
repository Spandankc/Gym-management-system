<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('member', 'login.php');
include '../dbcon.php';
include 'includes/authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = trim(is_string($_POST['task'] ?? null) ? $_POST['task'] : '');
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
    if ($task === '' || strlen($task) > 30 || !in_array($status, ['In Progress', 'Pending'], true)) {
        $_SESSION['error'] = 'Enter a task of at most 30 characters and choose a valid status.';
    } else {
        $user_id = (int) $_SESSION['uid'];
        $statement = $conn->prepare('INSERT INTO todo (task_status, task_desc, user_id) VALUES (?, ?, ?)');
        $statement->bind_param('ssi', $status, $task, $user_id);
        $statement->execute();
        $_SESSION['success'] = 'Task Added Successfully';
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
   <?php include 'includes/boilerplate.php';?>

<div class="content">
    <h2>To-Do Lists</h2>
    <div class="form-container">
    <form action="" method="post">
        <div class="input">
            <label for="task">Please Enter your Task: </label>
            <input type="text" name="task" maxlength="30" required>
        </div>
        <div class="input">
            <label for="task">Please Select a Status: </label>
            <select name="status">
                <option value="In Progress">In Progress</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
        <input type="hidden" name="id" value="<?=$_SESSION['uid']?>">
        <button>Add To List</button>
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