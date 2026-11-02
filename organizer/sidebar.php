<!-- ── SIDEBAR NAVIGATION ── -->
<!-- This component generates the primary navigation menu for the Organizer Portal.
     It uses dynamic highlighting to indicate the current active page. -->
<?php
// Detect the current filename to apply 'active' styling to the corresponding menu link.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white">
    <!-- Brand Branding -->
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">
            <i class="bi bi-calendar-event me-2"></i>Organizer Portal
        </span>
    </a>
    <hr style="border-color: rgba(255,255,255,0.1);">

    <!-- Main Navigation Links -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="events_manage.php"
                class="nav-link <?php echo ($current_page == 'events_manage.php') ? 'active' : ''; ?>">
                <i class="bi bi-calendar-week me-2"></i>
                Manage Events
            </a>
        </li>
        <li class="nav-item">
            <a href="event_report.php" class="nav-link <?php echo ($current_page == 'event_report.php') ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-line me-2"></i>
                Event Report
            </a>
        </li>
        <li class="nav-item">
            <a href="analytics.php" class="nav-link <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Analytics
            </a>
        </li>

        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-circle me-2"></i>
                Profile
            </a>
        </li>
    </ul>

    <!-- Footer Actions -->
    <div class="mt-auto px-2">
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="../login.php?logout=1" class="sidebar-logout">
            <i class="bi bi-box-arrow-left"></i>
            <strong>Logout</strong>
        </a>
    </div>
</div>