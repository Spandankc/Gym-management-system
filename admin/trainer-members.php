<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

$trainer_id = (int)($_GET['id'] ?? 0);
if ($trainer_id < 1) { http_response_code(404); exit('Trainer not found.'); }
{


    // Fetch Trainer's Information
    $trainer_sql = "SELECT fullname FROM staffs WHERE id = $trainer_id";
    $trainer_res = mysqli_query($conn, $trainer_sql);
    $trainer_row = mysqli_fetch_assoc($trainer_res);
    if (!$trainer_row) { http_response_code(404); exit('Trainer not found.'); }
    $trainer_name = $trainer_row['fullname'];

    // Fetch Members Assigned to the Trainer
    $members_sql = "SELECT members.*, services.service_name, services.cost
                    FROM members
                    LEFT JOIN services ON members.services_id = services.id
                    WHERE members.trainer_id = $trainer_id";
    $members_res = mysqli_query($conn, $members_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Hub - Trainer Members</title>
    <link rel="stylesheet" href="css/staff-manage.css">
    <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include 'includes/template.php'; ?>

<div class="content">
    <h2><i class="fa-solid fa-users" style="margin-right: 0.5rem"></i>Members Assigned</h2>

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
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; ?>
            <?php while($member = mysqli_fetch_assoc($members_res)) { ?>
            <tr>
                <td><?=$sn++?></td>
                <td><?= fitness_escape($member['fullname']) ?></td>
                <td><?= fitness_escape($member['gender']) ?></td>
                <td><?= fitness_escape($member['contact']) ?></td>
                <td><?=$member['dor']?></td>
                <td><?= fitness_escape($member['address']) ?></td>
                <td><?= fitness_escape($member['service_name']) ?></td>
                <td><?=$member['plan']?> Months</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="back"><a href="staff-manage.php">&#8592;Go Back</a></div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
