<!-- Host Sidebar -->
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <div class="d-flex align-items-center mb-4 px-2">
        <i class="bi bi-calendar-event fs-4 me-2 text-emerald"></i>
        <h4 class="mb-0 fw-bold">Host Portal</h4>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="feedback.php"
                class="nav-link <?php echo ($current_page == 'feedback.php') ? 'active' : ''; ?>">
                <i class="bi bi-star-half"></i>
                Rate Events
            </a>
        </li>
        <li class="nav-item">
            <a href="attendance.php"
                class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>">
                <i class="bi bi-clipboard-check"></i>
                Attendance & Certificates
            </a>
        </li>
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                Profile
            </a>
        </li>
    </ul>

    <div class="mt-auto px-2">
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="../login.php?logout=1" class="sidebar-logout">
            <i class="bi bi-box-arrow-left"></i>
            <strong>Logout</strong>
        </a>
    </div>
</div>