    <div class="container">
    <div class="sidebar">
            <div class="logo"><a href="dashboard.php" aria-label="Go to member dashboard"><img src="../images/logo1.png" alt="Fitness Hub"></a></div>

            <div class="options">
                <ul>
                    <li><a href="dashboard.php" class="pages"><span class="icon"><i class="fa-solid fa-house"></i></span>Dashboard</a></li>
                    <li><a href="schedules.php" class="pages"><span class="icon"><i class="fa-solid fa-calendar"></i></span>Class Schedules</a></li>
                    <li><a href="todo.php" class="pages"><span class="icon"><i class="fa-solid fa-pencil"></i></span>To-Do</a></li>
                    <li><a href="reminder.php" class="pages"><span class="icon"><i class="fa-solid fa-clock"></i></span>Reminders</a></li>
                    <li><a href="announcement.php" class="pages"><span class="icon"><i class="fa-solid fa-bullhorn"></i></span>Announcements</a></li>
                    <li><a href="reports.php" class="pages"><span class="icon"><i class="fa-solid fa-file"></i></span>Reports</a></li>
                </ul>
            </div>
        </div>
        <div class="section">
            <div class="header">
                <ul>
                    <li class="dropdown"><a href="#"><span class="icon"><i class="fa-solid fa-user"></i></span>Welcome <?=fitness_escape($_SESSION['user'])?></a></li>
                    <li><a href="logout.php"><span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span>Logout</a></li>
                </ul>
            </div>
