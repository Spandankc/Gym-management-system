<?php
require_once __DIR__ . '/auth.php';

if (!isset($fitnessScheduleRole) || !in_array($fitnessScheduleRole, ['admin', 'trainer', 'member'], true)) {
    http_response_code(404);
    exit;
}

fitness_require_role($fitnessScheduleRole, 'index.php');
require_once __DIR__ . '/../dbcon.php';

$scheduleTimezone = new DateTimeZone('Asia/Kathmandu');
$scheduleUid = (int) ($_SESSION['uid'] ?? 0);
$scheduleCanEdit = $fitnessScheduleRole !== 'member';
$scheduleErrors = [];
$scheduleSuccess = $_SESSION['schedule_success'] ?? '';
unset($_SESSION['schedule_success']);

if (!isset($_SESSION['schedule_csrf'])) {
    $_SESSION['schedule_csrf'] = bin2hex(random_bytes(32));
}

function fitness_schedule_text($value): string
{
    return is_string($value) ? trim($value) : '';
}

function fitness_schedule_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function fitness_schedule_owned(array $schedule, string $role, int $uid): bool
{
    return $role === 'admin' || ($role === 'trainer' && (int) $schedule['trainer_id'] === $uid);
}

function fitness_schedule_find(mysqli $conn, int $id): ?array
{
    $statement = $conn->prepare('SELECT * FROM class_schedules WHERE id = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $schedule = $statement->get_result()->fetch_assoc();
    $statement->close();
    return $schedule ?: null;
}

$scheduleForm = [
    'id' => 0,
    'title' => '',
    'trainer_id' => $fitnessScheduleRole === 'trainer' ? $scheduleUid : '',
    'starts_at' => '',
    'duration_minutes' => 60,
    'location' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleToken = $_POST['csrf_token'] ?? '';
    if (!$scheduleCanEdit) {
        http_response_code(403);
        $scheduleErrors[] = 'Members can view class schedules.';
    } elseif (!is_string($scheduleToken) || !hash_equals($_SESSION['schedule_csrf'], $scheduleToken)) {
        http_response_code(400);
        $scheduleErrors[] = 'Your form expired. Please try again.';
    } else {
        $scheduleAction = fitness_schedule_text($_POST['action'] ?? '');
        $scheduleId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $existingSchedule = $scheduleId ? fitness_schedule_find($conn, $scheduleId) : null;

        if ($scheduleId === false || !in_array($scheduleAction, ['save', 'delete'], true)) {
            $scheduleErrors[] = 'Please choose a valid class schedule.';
        } elseif ($scheduleId && !$existingSchedule) {
            http_response_code(404);
            $scheduleErrors[] = 'That class schedule was not found.';
        } elseif ($existingSchedule && !fitness_schedule_owned($existingSchedule, $fitnessScheduleRole, $scheduleUid)) {
            http_response_code(403);
            $scheduleErrors[] = 'You can manage only your own classes.';
        } elseif ($scheduleAction === 'delete') {
            if (!$existingSchedule) {
                $scheduleErrors[] = 'Please choose a class to delete.';
            } else {
                $deleteSql = $fitnessScheduleRole === 'admin'
                    ? 'DELETE FROM class_schedules WHERE id = ?'
                    : 'DELETE FROM class_schedules WHERE id = ? AND trainer_id = ?';
                $statement = $conn->prepare($deleteSql);
                if ($fitnessScheduleRole === 'admin') {
                    $statement->bind_param('i', $scheduleId);
                } else {
                    $statement->bind_param('ii', $scheduleId, $scheduleUid);
                }
                $statement->execute();
                $statement->close();
                $_SESSION['schedule_success'] = 'Class deleted successfully.';
                header('Location: schedules.php');
                exit;
            }
        } else {
            $scheduleForm = [
                'id' => $scheduleId,
                'title' => fitness_schedule_text($_POST['title'] ?? ''),
                'trainer_id' => $fitnessScheduleRole === 'trainer' ? $scheduleUid : fitness_schedule_text($_POST['trainer_id'] ?? ''),
                'starts_at' => is_string($_POST['starts_at'] ?? null) ? trim($_POST['starts_at'], " \t\n\r\v") : '',
                'duration_minutes' => fitness_schedule_text($_POST['duration_minutes'] ?? ''),
                'location' => fitness_schedule_text($_POST['location'] ?? ''),
                'description' => fitness_schedule_text($_POST['description'] ?? ''),
            ];

            if ($scheduleForm['title'] === '' || fitness_schedule_length($scheduleForm['title']) > 100) {
                $scheduleErrors[] = 'Enter a class name of up to 100 characters.';
            }
            if ($scheduleForm['location'] === '' || fitness_schedule_length($scheduleForm['location']) > 100) {
                $scheduleErrors[] = 'Enter a location of up to 100 characters.';
            }
            if (fitness_schedule_length($scheduleForm['description']) > 5000) {
                $scheduleErrors[] = 'Keep the description within 5,000 characters.';
            }

            $scheduleDateShape = preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}$/D', $scheduleForm['starts_at']);
            $scheduleStarts = $scheduleDateShape ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $scheduleForm['starts_at'], $scheduleTimezone) : false;
            if (!$scheduleStarts || $scheduleStarts->format('Y-m-d\TH:i') !== $scheduleForm['starts_at']) {
                $scheduleErrors[] = 'Choose a valid class date and time.';
            }
            $scheduleDuration = filter_var($scheduleForm['duration_minutes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1440]]);
            if ($scheduleDuration === false) {
                $scheduleErrors[] = 'Duration must be between 1 and 1,440 minutes.';
            }

            $scheduleTrainer = null;
            if ($fitnessScheduleRole === 'trainer') {
                $scheduleTrainer = $scheduleUid;
            } elseif ($scheduleForm['trainer_id'] !== '') {
                $scheduleTrainer = filter_var($scheduleForm['trainer_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($scheduleTrainer === false) {
                    $scheduleErrors[] = 'Choose a valid trainer.';
                } else {
                    $statement = $conn->prepare("SELECT id FROM staffs WHERE id = ? AND LOWER(TRIM(designation)) = 'trainer'");
                    $statement->bind_param('i', $scheduleTrainer);
                    $statement->execute();
                    if (!$statement->get_result()->fetch_assoc()) {
                        $scheduleErrors[] = 'Choose a valid trainer.';
                    }
                    $statement->close();
                }
            }

            if (!$scheduleErrors) {
                $scheduleTitle = $scheduleForm['title'];
                $scheduleStartsSql = $scheduleStarts->format('Y-m-d H:i:s');
                $scheduleLocation = $scheduleForm['location'];
                $scheduleDescription = $scheduleForm['description'];
                if ($scheduleId) {
                    $updateSql = 'UPDATE class_schedules SET title = ?, trainer_id = ?, starts_at = ?, duration_minutes = ?, location = ?, description = ? WHERE id = ?';
                    if ($fitnessScheduleRole === 'trainer') {
                        $updateSql .= ' AND trainer_id = ?';
                    }
                    $statement = $conn->prepare($updateSql);
                    if ($fitnessScheduleRole === 'admin') {
                        $statement->bind_param('sisissi', $scheduleTitle, $scheduleTrainer, $scheduleStartsSql, $scheduleDuration, $scheduleLocation, $scheduleDescription, $scheduleId);
                    } else {
                        $statement->bind_param('sisissii', $scheduleTitle, $scheduleTrainer, $scheduleStartsSql, $scheduleDuration, $scheduleLocation, $scheduleDescription, $scheduleId, $scheduleUid);
                    }
                } else {
                    $statement = $conn->prepare('INSERT INTO class_schedules (title, trainer_id, starts_at, duration_minutes, location, description) VALUES (?, ?, ?, ?, ?, ?)');
                    $statement->bind_param('sisiss', $scheduleTitle, $scheduleTrainer, $scheduleStartsSql, $scheduleDuration, $scheduleLocation, $scheduleDescription);
                }
                $statement->execute();
                $statement->close();
                $_SESSION['schedule_success'] = $scheduleId ? 'Class updated successfully.' : 'Class added successfully.';
                header('Location: schedules.php');
                exit;
            }
        }
    }
} elseif (isset($_GET['edit']) && $scheduleCanEdit) {
    $scheduleId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $existingSchedule = $scheduleId ? fitness_schedule_find($conn, $scheduleId) : null;
    if (!$existingSchedule) {
        http_response_code(404);
        $scheduleErrors[] = 'That class schedule was not found.';
    } elseif (!fitness_schedule_owned($existingSchedule, $fitnessScheduleRole, $scheduleUid)) {
        http_response_code(403);
        $scheduleErrors[] = 'You can manage only your own classes.';
    } else {
        $scheduleForm = $existingSchedule;
        $scheduleForm['starts_at'] = (new DateTimeImmutable($existingSchedule['starts_at'], $scheduleTimezone))->format('Y-m-d\TH:i');
    }
}

$scheduleTrainers = [];
if ($fitnessScheduleRole === 'admin') {
    $result = $conn->query("SELECT id, fullname FROM staffs WHERE LOWER(TRIM(designation)) = 'trainer' ORDER BY fullname");
    $scheduleTrainers = $result->fetch_all(MYSQLI_ASSOC);
}

$scheduleSql = 'SELECT class_schedules.*, staffs.fullname AS trainer_name FROM class_schedules LEFT JOIN staffs ON class_schedules.trainer_id = staffs.id';
if ($fitnessScheduleRole === 'member') {
    $scheduleSql .= ' WHERE starts_at >= ? ORDER BY starts_at, class_schedules.id';
    $statement = $conn->prepare($scheduleSql);
    $scheduleNow = (new DateTimeImmutable('now', $scheduleTimezone))->format('Y-m-d H:i:s');
    $statement->bind_param('s', $scheduleNow);
    $statement->execute();
    $scheduleRows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
} else {
    $scheduleRows = $conn->query($scheduleSql . ' ORDER BY starts_at, class_schedules.id')->fetch_all(MYSQLI_ASSOC);
}

$scheduleDirectory = $fitnessScheduleRole === 'trainer' ? 'staffs' : ($fitnessScheduleRole === 'member' ? 'customer' : 'admin');
$scheduleTemplate = $fitnessScheduleRole === 'member' ? 'boilerplate.php' : 'template.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedules | Fitness Hub</title>
    <link rel="stylesheet" href="<?= $fitnessScheduleRole === 'member' ? 'css/boilerplate.css' : 'css/template.css' ?>">
    <link rel="stylesheet" href="../customer/css/schedules.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body class="fitness-schedules">
<?php include __DIR__ . '/../' . $scheduleDirectory . '/includes/' . $scheduleTemplate; ?>
<main class="content schedule-content">
    <h1>Class Schedules</h1>
    <p class="schedule-intro"><?= $scheduleCanEdit ? 'View scheduled classes and manage ' . ($fitnessScheduleRole === 'admin' ? 'the gym timetable.' : 'your own classes.') : 'View upcoming gym classes.' ?></p>
    <p class="schedule-timezone">Times are shown in Nepal time (Asia/Kathmandu).</p>

    <?php if ($scheduleSuccess !== ''): ?>
        <div class="message" role="status"><?= fitness_escape($scheduleSuccess) ?></div>
    <?php endif; ?>
    <?php if ($scheduleErrors): ?>
        <div class="error" role="alert"><ul>
        <?php foreach ($scheduleErrors as $error): ?><li><?= fitness_escape($error) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <?php if ($scheduleCanEdit): ?>
    <form method="post" action="schedules.php" class="schedule-form">
        <h2><?= $scheduleForm['id'] ? 'Edit Class' : 'Add Class' ?></h2>
        <input type="hidden" name="csrf_token" value="<?= fitness_escape($_SESSION['schedule_csrf']) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $scheduleForm['id'] ?>">
        <div class="schedule-fields">
            <label>Class name
                <input name="title" required maxlength="100" value="<?= fitness_escape($scheduleForm['title']) ?>">
            </label>
            <?php if ($fitnessScheduleRole === 'admin'): ?>
            <label>Trainer
                <select name="trainer_id">
                    <option value="">Unassigned</option>
                    <?php foreach ($scheduleTrainers as $trainer): ?>
                    <option value="<?= (int) $trainer['id'] ?>" <?= (string) $scheduleForm['trainer_id'] === (string) $trainer['id'] ? 'selected' : '' ?>><?= fitness_escape($trainer['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php endif; ?>
            <label>Date and time
                <input type="datetime-local" name="starts_at" required value="<?= fitness_escape($scheduleForm['starts_at']) ?>">
            </label>
            <label>Duration (minutes)
                <input type="number" name="duration_minutes" min="1" max="1440" required value="<?= fitness_escape($scheduleForm['duration_minutes']) ?>">
            </label>
            <label>Location
                <input name="location" required maxlength="100" value="<?= fitness_escape($scheduleForm['location']) ?>">
            </label>
            <label class="schedule-wide">Description (optional)
                <textarea name="description" maxlength="5000"><?= fitness_escape($scheduleForm['description']) ?></textarea>
            </label>
        </div>
        <div class="schedule-form-actions">
            <button class="schedule-button" type="submit"><?= $scheduleForm['id'] ? 'Save Changes' : 'Add Class' ?></button>
            <?php if ($scheduleForm['id']): ?><a href="schedules.php">Cancel</a><?php endif; ?>
        </div>
    </form>
    <?php endif; ?>

    <h2><?= $fitnessScheduleRole === 'member' ? 'Upcoming Classes' : 'Scheduled Classes' ?></h2>
    <div class="schedule-table-wrap">
    <table class="schedule-table">
        <thead><tr><th>Class</th><th>Trainer</th><th>Date and Time</th><th>Duration</th><th>Location</th><?php if ($scheduleCanEdit): ?><th>Actions</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if (!$scheduleRows): ?>
            <tr><td colspan="<?= $scheduleCanEdit ? 6 : 5 ?>">No <?= $fitnessScheduleRole === 'member' ? 'upcoming ' : '' ?>classes scheduled yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($scheduleRows as $schedule): ?>
            <?php $starts = new DateTimeImmutable($schedule['starts_at'], $scheduleTimezone); ?>
            <tr>
                <td><strong><?= fitness_escape($schedule['title']) ?></strong><?php if ($schedule['description'] !== ''): ?><p class="schedule-description"><?= fitness_escape($schedule['description']) ?></p><?php endif; ?></td>
                <td><?= fitness_escape($schedule['trainer_name'] ?? 'Unassigned') ?></td>
                <td><time datetime="<?= fitness_escape($starts->format(DateTimeInterface::ATOM)) ?>"><?= fitness_escape($starts->format('d M Y, g:i a')) ?></time></td>
                <td><?= (int) $schedule['duration_minutes'] ?> minutes</td>
                <td><?= fitness_escape($schedule['location']) ?></td>
                <?php if ($scheduleCanEdit): ?>
                <td>
                    <?php if (fitness_schedule_owned($schedule, $fitnessScheduleRole, $scheduleUid)): ?>
                    <div class="schedule-row-actions">
                        <a href="schedules.php?edit=<?= (int) $schedule['id'] ?>">Edit</a>
                        <form method="post" action="schedules.php" onsubmit="return confirm('Delete this class?');">
                            <input type="hidden" name="csrf_token" value="<?= fitness_escape($_SESSION['schedule_csrf']) ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $schedule['id'] ?>">
                            <button class="schedule-button schedule-delete" type="submit">Delete</button>
                        </form>
                    </div>
                    <?php else: ?>Other trainer's class<?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</main>
<?php include __DIR__ . '/../' . $scheduleDirectory . '/includes/footer.php'; ?>
