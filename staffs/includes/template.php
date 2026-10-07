<link rel="stylesheet" href="css/template.css">
<div class="container">
        <div class="sidebar">
            <div class="logo"><a href="dashboard.php" aria-label="Go to trainer dashboard"><img src="../images/logo1.png" alt="Fitness Hub"></a></div>
            <div class="options">
                <ul>
                    <li><a href="dashboard.php" class="pages"><span class="icon"><i class="fa-solid fa-house"></i></span>Dashboard</a></li>
                    <li><a href="#" class="pages member-inner"><span class="icon"><i class="fa-solid fa-users"></i></span>Manage Members</a>
                        <ul class="member-option inner-option">
                            <li><a href="member-progress.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Member's Progress</a></li>
                            <li><a href="member-status.php" class="inner-page pages"><i class="fa-solid fa-arrow-right"></i>Member's Status </a></li>
                        </ul>
                    </li>
                    <li><a href="schedules.php" class="pages"><span class="icon"><i class="fa-solid fa-calendar"></i></span>Class Schedules</a></li>
                    <li><a href="payment.php" class="pages"><span class="icon"><i class="fa-solid fa-money-check-dollar"></i></span>Payments</a></li>
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
                    <li class="dropdown"><a href="#"><span class="icon"><i class="fa-solid fa-user"></i></span>Welcome <?=$_SESSION['user']?></a></li>
                    <li><a href="logout.php"><span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span>Logout</a></li>
                </ul>
            </div>

