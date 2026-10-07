<?php
require_once __DIR__ . '/../includes/auth.php';
fitness_require_role('admin', 'index.php');
require_once __DIR__ . '/../dbcon.php';

//Displaying Announcements
$sql="SELECT * FROM announcements";
$res=mysqli_query($conn,$sql);
$sn=1;

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fitness Hub - Admin</title>
        <link rel="stylesheet" href="css/announcement-manage.css">
        <script src="https://kit.fontawesome.com/426c1a4028.js" crossorigin="anonymous"></script>
    </head>
    <body>
    <?php include 'includes/template.php';?>

<div class="content">
    <h2><i class="fa-solid fa-bullhorn" style="margin-right: 0.5rem"></i>Manage Announcement</h2>
    <?php if(isset($_SESSION['success'])){ ?> <div class="message"><h3><?=$_SESSION['success'];  unset($_SESSION['success'])?></h3></div><?php } ?>
    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>Date</th>
                <th>Message</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($data=mysqli_fetch_assoc($res)){ ?>
            <tr>
                <td><?=$sn++?></td>
                <td><?=$data['date']?></td>
                <td><?=$data['message']?></td>
                <td><a href="announcement-delete.php?id=<?=$data['id']?>"><i class="fa-solid fa-trash"></i></a></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <p class="back"><a href="announcement-add.php">&larr;Go Back</a></p>
</div>

<?php include 'includes/footer.php'; ?>