<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar">
    <div class="d-flex align-items-center mb-4 px-2">
        <i class="bi bi-mortarboard fs-4 me-2 text-emerald"></i>
        <h4 class="mb-0 fw-bold">Student Portal</h4>
    </div>

    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="events_all.php"
                class="nav-link <?php echo ($current_page == 'events_all.php') ? 'active' : ''; ?>">
                <i class="bi bi-search"></i>
                <span>Explore Events</span>
            </a>
        </li>



        <li class="nav-item">
            <a href="certificates.php"
                class="nav-link <?php echo ($current_page == 'certificates.php') ? 'active' : ''; ?>">
                <i class="bi bi-award"></i>
                <span>My Certificates</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="feedback.php" class="nav-link <?php echo ($current_page == 'feedback.php') ? 'active' : ''; ?>">
                <i class="bi bi-chat-dots"></i>
                <span>Feedback</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                <span>My Profile</span>
            </a>
        </li>
    </ul>

    <div class="mt-auto px-2">
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="../login.php?logout=1" class="sidebar-logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>
</div>