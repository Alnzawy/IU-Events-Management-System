<?php
/* host dashbosrd – shows 4 cards */
require_once '../config.php'; 
checkRole('host'); 
$hostID = $_SESSION['user_id']; 

// Get host name
$stmt = $conn->prepare("select Name from hosts where HostID = ?");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$hostName = $stmt->get_result()->fetch_assoc()['Name'];

// Count today's events
$stmt = $conn->prepare("select COUNT(*) as total from events where HostID = ? AND EventDate = CURDATE()");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$todayEvents = $stmt->get_result()->fetch_assoc()['total'];

// Count upcoming events
$stmt = $conn->prepare("select COUNT(*) as total from events where HostID = ? AND EventDate >= CURDATE()");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$upcomingEvents = $stmt->get_result()->fetch_assoc()['total'];

// Count previous events
$stmt = $conn->prepare("select COUNT(*) as total from events where HostID = ? AND EventDate < CURDATE()");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$previousEvents = $stmt->get_result()->fetch_assoc()['total'];

// Count total registrations for upcoming events
$stmt = $conn->prepare("
    select COUNT(*) as total 
    from registrations r 
    join events e on r.EventID = e.EventID 
    where e.HostID = ? and e.EventDate >= CURDATE()
");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$totalRegistrations = $stmt->get_result()->fetch_assoc()['total'];

// Get upcoming events
$stmt = $conn->prepare("
    SELECT e.*, h.Name AS HallName, h.Capacity,
    (SELECT COUNT(*) FROM registrations r WHERE r.EventID = e.EventID) AS RegCount
    FROM events e
    LEFT JOIN halls h ON e.HallID = h.HallID
    WHERE e.HostID = ? AND e.EventDate >= CURDATE()
    ORDER BY e.EventDate ASC, e.TimeToStart ASC;
");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$eventsList = $stmt->get_result();

$pageTitle = 'Host Dashboard - IU Events';
include 'includes/header.php';
?>

<div class="scroll-main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Host <span class="title-accent">Dashboard</span></h1>
            <p class="page-sub">Welcome back, <?=htmlspecialchars($hostName)?></p>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-icon ic-today"><i class="bi bi-calendar-check"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=$todayEvents?></div>
                <div class="kpi-label">Today's Events</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-upcoming"><i class="bi bi-calendar-event"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=$upcomingEvents?></div>
                <div class="kpi-label">Upcoming Events</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-prev"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=$previousEvents?></div>
                <div class="kpi-label">Previous Events</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon ic-users"><i class="bi bi-people"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=$totalRegistrations?></div>
                <div class="kpi-label">Registrations</div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="mb-4">
            <h4 class="fw-bold mb-1 text-light">Upcoming Events</h4>
            <p class="text-white-50 small">Your assigned future sessions</p>
        </div>
        
        <?php if ($eventsList->num_rows > 0): ?>
            <div class="card card-custom border-0 overflow-hidden">
                <div class="table-container p-0">
                    <table class="table-modern mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Category</th>
                                <th>Hall</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($event = $eventsList->fetch_assoc()):
                                $regCount = $event['RegCount'];
                                $capacity = $event['Capacity'] ?? 0;
                                $occupancy = $capacity > 0 ? ($regCount / $capacity) * 100 : 0;
                                $statusColor = $occupancy >= 100 ? '#ef4444' : ($occupancy >= 80 ? '#f59e0b' : '#10b981');
                            ?>
                                <tr>
                                    <td>
                                        <div class="event-title"><?php echo htmlspecialchars($event['Title']); ?></div>
                                        <small class="text-white-50 text-truncate d-block" style="max-width:250px"><?php echo htmlspecialchars($event['Description']); ?></small>
                                    </td>
                                    <td>
                                        <div class="date-wrapper text-white">
                                            <i class="bi bi-calendar3 date-icon"></i>
                                            <div>
                                                <div class="small fw-bold"><?php echo date('Y-m-d', strtotime($event['EventDate'])); ?></div>
                                                <small class="time-text">
                                                    <?php echo date('h:i A', strtotime($event['TimeToStart']));
                                                    if ($event['TimeToEnd']) echo ' - ' . date('h:i A', strtotime($event['TimeToEnd'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="category-badge"><?php echo htmlspecialchars($event['Category']); ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center text-white-50">
                                            <i class="bi bi-geo-alt me-2 text-gold"></i>
                                            <?php echo htmlspecialchars($event['HallName'] ?? 'Not Assigned Yet'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people-fill me-2" style="color:<?php echo $statusColor; ?>"></i>
                                            <span class="fw-bold" style="color:<?php echo $statusColor; ?>"><?php echo $regCount; ?></span>
                                            <small class="text-white-50 ms-1">/ <?php echo $capacity; ?></small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="app-empty-state">
                <i class="bi bi-calendar-x"></i>
                <h4>No Upcoming Events</h4>
                <p>You don't have any events scheduled at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>