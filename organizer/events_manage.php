<?php
/**
 * events_manage.php - Primary management console for upcoming events.
 * Provides a tabular overview of all scheduled sessions with quick 
 * access to edit, delete, and occupancy monitoring.
 */
require_once '../config.php';
checkRole('organizer');

// ── 1. Action Handler: Deletion ──────────────────────────────────────
// Permanently removes an event from the database.
// Note: In production, consider cascading deletes or soft-deletes for data integrity.
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM events WHERE EventID = $id");
    $_SESSION['flash_success'] = "Event deleted successfully!";
    header("Location: events_manage.php");
    exit();
}

// ── 2. Data Retrieval ────────────────────────────────────────────────
// Fetches all upcoming events and joins with Halls/Hosts for a complete overview.
// Also calculates current registration count using a subquery.
$sql = "SELECT 
            e.EventID, e.Title, e.EventDate, e.TimeToStart, e.TimeToEnd, e.Category,
            h.Name AS HallName, hst.Name AS HostName, e.IsCertified,
            (SELECT COUNT(*) FROM registrations r WHERE r.EventID = e.EventID) AS RegisteredCount,
            h.Capacity
        FROM events e
        LEFT JOIN halls h ON e.HallID = h.HallID
        LEFT JOIN hosts hst ON e.HostID = hst.HostID
        WHERE e.EventDate >= CURDATE()
        ORDER BY e.EventDate ASC";

$result = $conn->query($sql);
$events_list = [];
$i = 1;

while ($row = $result->fetch_assoc()) {
    $reg = $row['RegisteredCount'];
    $cap = $row['Capacity'];
    
    // ── 3. Occupancy Status Logic ────────────────────────────────────
    // Dynamically color-codes the registration count based on capacity thresholds.
    // Green (Safe), Amber (Nearing Capacity), Red (Full).
    $occupancy = $cap > 0 ? ($reg / $cap) * 100 : 0;
    
    $status_color = '#10b981'; // Default: Green
    if ($occupancy >= 100) $status_color = '#ef4444'; // Full: Red
    else if ($occupancy >= 80) $status_color = '#f59e0b'; // Nearing: Amber

    $time_display = date('h:i A', strtotime($row['TimeToStart'])) . " - " . date('h:i A', strtotime($row['TimeToEnd']));

    $events_list[] = [
        'no' => $i++,
        'id' => $row['EventID'],
        'title' => htmlspecialchars($row['Title']),
        'date' => date('Y-m-d', strtotime($row['EventDate'])),
        'time' => $time_display,
        'category' => $row['Category'],
        'hall' => htmlspecialchars($row['HallName']),
        'host' => htmlspecialchars($row['HostName']),
        'reg' => $reg,
        'cap' => $cap,
        'color' => $status_color,
        'is_certified' => $row['IsCertified']
    ];
}

$pageTitle = 'Manage Events - Organizer Portal';
include 'includes/header.php';
?>

<div class="scroll-main">
    <div class="container-fluid p-0">
        <!-- Header & Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1 text-light">Manage Upcoming Events</h2>
                <p class="text-white-50 small">Overview of all active and upcoming program sessions</p>
            </div>
            <a href="event_wizard_helper.php" class="btn btn-login">
                <i class="bi bi-plus-lg me-2"></i>Add New Event
            </a>
        </div>

        <!-- Events List Table -->
        <div class="card card-custom overflow-hidden border-0">
            <div class="table-responsive">
                <table class="table-modern w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width: 3%;"></th>
                            <th style="width: 27%;">Title</th>
                            <th style="width: 20%;">Date & Time</th>
                            <th style="width: 10%;">Category</th>
                            <th style="width: 12%;">Hall</th>
                            <th style="width: 10%;">Host</th>
                            <th style="width: 10%;">Students</th>
                            <th style="width: 8%; text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($events_list)): ?>
                            <?php foreach ($events_list as $ev): ?>
                                <tr>
                                    <td class="text-white-50 small"><?php echo $ev['no']; ?></td>
                                    <td>
                                        <div class="event-title d-flex align-items-start" style="gap: 8px;">
                                            <span class="text-white fw-bold"><?php echo $ev['title']; ?></span>
                                            <?php if ($ev['is_certified']): ?>
                                                <i class="bi bi-award-fill text-warning flex-shrink-0" title="Certificated" style="margin-top: 2px;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-wrapper text-white">
                                            <i class="bi bi-calendar3 date-icon"></i>
                                            <div>
                                                <div class="small fw-bold"><?php echo $ev['date']; ?></div>
                                                <small class="text-warning" style="font-size: 0.75rem;">
                                                    <?php echo $ev['time']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="category-badge"><?php echo $ev['category']; ?></span></td>
                                    <td><span class="text-white-50 small"><?php echo $ev['hall']; ?></span></td>
                                    <td><span class="text-white small"><?php echo $ev['host']; ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-people-fill me-2" style="color: <?php echo $ev['color']; ?>;"></i>
                                            <span class="fw-bold" style="color: <?php echo $ev['color']; ?>;">
                                                <?php echo $ev['reg']; ?>
                                            </span>
                                            <small class="text-white-50 ms-1">/ <?php echo $ev['cap']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 px-3">
                                            <a href="event_wizard_helper.php?id=<?php echo $ev['id']; ?>" class="btn-glass d-inline-flex align-items-center justify-content-center" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?delete=<?php echo $ev['id']; ?>" class="btn-glass btn-glass-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this event?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <p class="text-white-50">No upcoming events found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>