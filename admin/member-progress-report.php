<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

//Display Information
$sql="SELECT members.*, services.service_name
    FROM members
    LEFT JOIN services ON members.services_id=services.id";
$res=mysqli_query($conn,$sql);
$sn=1;

//Searching Users
if(!empty($_POST)){
    $search=mysqli_real_escape_string($conn, trim($_POST['search'] ?? ''));
    if(!empty($search)){
        $sql="SELECT members.*, services.service_name
        FROM members
        LEFT JOIN services on members.services_id=services.id WHERE CONCAT_WS(' ', members.fullname, services.service_name, members.status) LIKE '%$search%'";
        $res=mysqli_query($conn,$sql);
    }else{
        $sql="SELECT members.*, services.service_name
        FROM members
        LEFT JOIN services on members.services_id=services.id";
        $res=mysqli_query($conn,$sql);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub - Admin</title>
        <link rel="stylesheet" href="css/member-progress-report.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
        <script>
        // JavaScript function to submit form only when input field is cleared
        function submitForm() {
            var searchValue = document.getElementById("searchField").value.trim();
            if (searchValue === '') {
                document.getElementById("searchForm").submit();
            }
        }
    </script>
    </head>
    <body>

        <?php include 'includes/template.php';?>
        <div class="content">
            <div class="heading">
            <h2><i class="fa-solid fa-chart-simple" style="margin-right: 0.5rem"></i>Member's Progress Report</h2>
            <form action="member-progress-report.php" method="post" id="searchForm">
                <input type="text" id="searchField" placeholder="Search" name="search"
                    value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>"
                    oninput="submitForm()">
                <button type="submit"><i class="fa-solid fa-search"></i></button>
            </form>
            </div>
    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Fullname</th>
                <th>Service</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user=mysqli_fetch_assoc($res)){ ?>
            <tr>
                <td><?=$sn++?></td>
                <td><?= fitness_escape($user['fullname']) ?></td>
                <td><?= fitness_escape($user['service_name']) ?></td>
                <td><a href="view-progress-report.php?id=<?=$user['id']?>"><i class="fa-solid fa-file" style="margin-right:0.5rem;"></i>View Progress Report</a></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>