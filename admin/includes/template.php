<?php
require_once __DIR__ . '/../../includes/auth.php';
fitness_require_role('admin', 'index.php');
?>
<link rel="stylesheet" href="css/template.css">
<div class="container">
        <div class="sidebar">
            <div class="logo"><a href="dashboard.php" aria-label="Go to admin dashboard"><img src="../images/logo1.png" alt="Fitness Hub"></a></div>
            <div class="options">
                <ul>
                    <li><a href="dashboard.php" class="pages"><span class="icon"><i class="fa-solid fa-house"></i></span>Dashboard</a></li>
                    <li><a href="#" class="pages member-inner"><span class="icon"><i class="fa-solid fa-users"></i></span>Manage Members</a>
                        <ul class="member-option inner-option">
                            <li><a href="member-list.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Members List</a></li>
                            <li><a href="member-registration.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Member Entry Form </a></li>
                            <li><a href="member-status.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Members Status </a></li>
                        </ul>
                    </li>
                    <li><a href="#" class="pages equip-inner"><span class="icon"><i class="fa-solid fa-dumbbell"></i></span>Gym Equipments</a>
                        <ul class="equip-option inner-option">
                            <li><a href="equipment-list.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Equipments List</a></li>
                            <li><a href="equipment-add.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Add Equipments</a></li>
                        </ul>
                    </li>
                    <li><a href="member-progress.php" class="pages"><span class="icon"><i class="fa-solid fa-chart-simple"></i></span>Member's Progress</a></li>
                    <li><a href="schedules.php" class="pages"><span class="icon"><i class="fa-solid fa-calendar"></i></span>Class Schedules</a></li>
                    <li><a href="payment.php" class="pages"><span class="icon"><i class="fa-solid fa-money-check-dollar"></i></span>Payments</a></li>
                    <li><a href="announcement-add.php" class="pages"><span class="icon"><i class="fa-solid fa-bullhorn"></i></span>Announcement</a></li>
                    <li><a href="staff-manage.php" class="pages"><span class="icon"><i class="fa-solid fa-briefcase"></i></span>Staff Management</a></li>
                    <li><a href="#" class="pages report-inner"><span class="icon"><i class="fa-solid fa-file"></i></span>Reports</a>
                        <ul class="report-option inner-option">
                            <li><a href="member-progress-report.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Member's Progress Report</a></li>
                            <li><a href="membership-report.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Membership Report</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="section">
            <div class="header">
                <ul>
                    <li class="dropdown"><a href="#"><span class="icon"><i class="fa-solid fa-user"></i></span>Welcome Admin</a></li>
                    <li><a href="logout.php"><span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span>Logout</a></li>
                </ul>
            </div>

