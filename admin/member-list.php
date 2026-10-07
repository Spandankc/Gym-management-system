<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';
if (isset($_POST['assign_trainer'])) {
    $member_id = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);
    $trainer_id = filter_var($_POST['trainer_id'] ?? null, FILTER_VALIDATE_INT);
    if ($member_id && $trainer_id) {
        $check = $conn->prepare("SELECT id FROM staffs WHERE id = ? AND designation = 'Trainer'");
        $check->bind_param('i', $trainer_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $assign = $conn->prepare('UPDATE members SET trainer_id = ? WHERE id = ?');
            $assign->bind_param('ii', $trainer_id, $member_id);
            $assign->execute();
            $_SESSION['success'] = 'Trainer assigned successfully!';
        } else $_SESSION['error'] = 'Select a valid trainer.';
    } else $_SESSION['error'] = 'Select a member and trainer.';
    header('Location: member-list.php');
    exit;
}
$search = trim($_POST['search'] ?? '');
$pattern = '%' . $search . '%';
$list = $conn->prepare('SELECT members.*, services.service_name, services.cost, staffs.fullname AS trainer_name FROM members LEFT JOIN services ON members.services_id = services.id LEFT JOIN staffs ON members.trainer_id = staffs.id WHERE CONCAT_WS(" ", members.fullname, services.service_name, members.status) LIKE ?');
$list->bind_param('s', $pattern);
$list->execute();
$res = $list->get_result();
$sn = 1;
$trainers_res = $conn->query("SELECT id, fullname FROM staffs WHERE designation = 'Trainer' ORDER BY fullname");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Admin</title>
    <link rel="stylesheet" href="css/member-list.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    <script>
        // JavaScript function to submit form only when input field is cleared
        function submitForm() {
            var searchValue = document.getElementById("searchField").value.trim();
            if (searchValue === '') {
                document.getElementById("searchForm").submit();
            }
        }

        // JavaScript function for delete confirmation
        function confirmDelete(event) {
            if (!confirm("Are you sure you want to delete this member?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
<?php include 'includes/template.php';?>

<div class="content">
    <div class="heading"><h2>Registered Members List</h2>
    <form action="member-list.php" method="post" id="searchForm">
        <input type="text" id="searchField" placeholder="Search" name="search" value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>" oninput="submitForm()">
        <button type="submit"><i class="fa-solid fa-search"></i></button>
    </form></div>
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="message"><h3><?=$_SESSION['success'];  unset($_SESSION['success'])?></h3></div>
    <?php } ?>
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="message"><h3><?=$_SESSION['error'];  unset($_SESSION['error'])?></h3></div>
    <?php } ?>
    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Contact</th>
                <th>D.O.R</th>
                <th>Address</th>
                <th>Service</th>
                <th>Duration</th>
                <th>Trainer</th>
                <th colspan="3">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (($res->num_rows) == 0) { ?>
            <tr>
                <td colspan="12">NO RECORD FOUND</td>
            </tr>
            <?php } else { while ($row = mysqli_fetch_assoc($res)) { ?>
            <tr>
                <td><?=$sn++?></td>
                <td><?= fitness_escape($row['fullname']) ?></td>
                <td><?= fitness_escape($row['gender']) ?></td>
                <td><?= fitness_escape($row['contact']) ?></td>
                <td><?=$row['dor']?></td>
                <td><?= fitness_escape($row['address']) ?></td>
                <td><?= fitness_escape($row['service_name']) ?></td>
                <td><?=$row['plan']?> Months</td>
                <td><?=$row['trainer_name'] ?? 'Not Assigned'?></td>
                <td>
                    <form action="member-list.php" method="post">
                        <input type="hidden" name="member_id" value="<?=$row['id']?>">
                        <select name="trainer_id" onchange="this.form.submit()">
                            <option value="">Assign Trainer</option>
                            <?php
                            // Reset trainer result pointer
                            mysqli_data_seek($trainers_res, 0);
                            while ($trainer = mysqli_fetch_assoc($trainers_res)) { ?>
                                <option value="<?=$trainer['id']?>" <?= (int)$row['trainer_id'] === (int)$trainer['id'] ? 'selected' : '' ?>><?= fitness_escape($trainer['fullname']) ?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="assign_trainer" value="1">
                    </form>
                </td>
                <td>
                    <a href="update-member.php?id=<?=$row['id']?>" title="Update Member's Info" class="update"><i class="fa-solid fa-edit"></i></a>
                </td>
                <td>
                    <a href="member-delete.php?id=<?=$row['id']?>" title="Delete Member" class="delete" onclick="confirmDelete(event)"><i class="fa-solid fa-trash"></i></a>
                </td>

            </tr>
            <?php }} ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'?>
</body>
</html>
