<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('member', 'login.php');
include '../dbcon.php';
include 'includes/authentication.php';

$uid = (int) $_SESSION['uid'];
// A member can complete only their own task.
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT) ?: 0;
    $complete = $conn->prepare('DELETE FROM todo WHERE id = ? AND user_id = ?');
    $complete->bind_param('ii', $id, $uid);
    $complete->execute();
    if ($complete->affected_rows) { $_SESSION['success'] = 'Task Completed Successfully'; }
    header('Location: dashboard.php');
    exit;
}
$todo = $conn->prepare('SELECT * FROM todo WHERE user_id = ? ORDER BY id');
$todo->bind_param('i', $uid);
$todo->execute();
$result_todo = $todo->get_result();
$profile = $conn->prepare('SELECT members.plan_link, staffs.fullname AS trainer_name FROM members LEFT JOIN staffs ON members.trainer_id = staffs.id WHERE members.id = ?');
$profile->bind_param('i', $uid);
$profile->execute();
$member = $profile->get_result()->fetch_assoc();
if (!$member) {
    unset($_SESSION['is_login'], $_SESSION['role'], $_SESSION['uid'], $_SESSION['user']);
    $_SESSION['error'] = 'Your member account could not be found. Please log in again.';
    header('Location: login.php');
    exit;
}
$plan_link = $member['plan_link'];
$trainer_name = $member['trainer_name'] ?: 'Trainer Not Assigned Yet';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub</title>
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/boilerplate.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <?php include 'includes/boilerplate.php'; ?>
    <div class="content">
        <?php if (isset($_SESSION['success'])): ?><p role="status"><?=fitness_escape($_SESSION['success'])?></p><?php unset($_SESSION['success']); endif; ?>
        <div class="top">
            <div class="to-do">
                <h2>My To-Do-List</h2>
                <p></p>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Status</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result_todo as $task) { ?>
                            <tr>
                                <td><?= fitness_escape($task['task_desc']) ?></td>
                                <td><?= fitness_escape($task['task_status']) ?></td>
                                <td>
                                    <a href="update-todo.php?id=<?= $task['id'] ?>" class="update" title="Update"><span><i
                                                class="fa-solid fa-pen-to-square"></i></span></a>
                                </td>
                                <td>
                                    <a href="dashboard.php?id=<?= $task['id'] ?>" class="complete" title="Complete"><span><i
                                                class="fa-solid fa-square-check"></i></span></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="announcement">
                <h2>Gym Announcement <span class="hide-show"><i class="fa-solid fa-chevron-down icon"></i></span></h2>
                <div class="announce-list">
                    <?php
                    $sql_announcements = "SELECT * FROM announcements";
                    $result_announcements = mysqli_query($conn, $sql_announcements);
                    while ($row = mysqli_fetch_assoc($result_announcements)) {
                        ?>
                        <div class="announce">
                            <span class="ann"><i class="fa-solid fa-bullhorn"></i></span>
                            <div class="message">
                                <h3><?= fitness_escape($row['message']) ?></h3>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="trainer-info">
            <h2>Assigned Trainer: <?= fitness_escape($trainer_name) ?></h2>
        </div>
        <div class="bottom">
            <h1>Workout Routine</h1>
            <?php if ($plan_link) { ?>
                <iframe class="plan" src="<?= fitness_escape($plan_link) ?>" width="100%" height="600px"></iframe>
                <div class="sheet"><a href="<?= fitness_escape($plan_link) ?>" target="_blank">&#8594;Open in Google Sheets</a></div>
            <?php } else { ?>
                <p>Training Plan is not added yet.</p>
            <?php } ?>
        </div>

    </div>

    <?php include 'includes/footer.php' ?>

</body>

</html>
