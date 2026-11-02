<?php
require_once '../config.php';
checkRole('student');
require_once 'includes/components.php';
$studentID = $_SESSION['user_id'];

// --- CANCEL REGISTRATION ---
if (isset($_POST['cancel_event'])) {
    $cancelID = intval($_POST['event_id']);
    $stmt = $conn->prepare("DELETE FROM registrations WHERE StudentID = ? AND EventID = ?");
    $stmt->bind_param("ii", $studentID, $cancelID);
    $stmt->execute();
    $_SESSION['flash_success'] = "Registration cancelled successfully.";
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT Name FROM students WHERE StudentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$studentName = $stmt->get_result()->fetch_assoc()['Name'] ?? 'Student';

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE StudentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$totalRegs = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE StudentID = ? AND Attended = 1");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$totalAttended = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE StudentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$totalCerts = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total FROM registrations r
    JOIN events e ON r.EventID = e.EventID
    WHERE r.StudentID = ? AND e.EventDate >= CURDATE()
");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$upcomingRegs = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT e.EventID, e.Title, e.EventDate, e.TimeToStart, e.Category, e.IsCertified,
           h.Name as HallName, h.Capacity,
           IFNULL(a.Attended, -1) as IsAttended,
           c.CertificateID,
           (SELECT COUNT(*) FROM registrations r2 WHERE r2.EventID = e.EventID) as RegCount
    FROM registrations r
    JOIN events e ON r.EventID = e.EventID
    LEFT JOIN halls h ON e.HallID = h.HallID
    LEFT JOIN attendance a ON a.EventID = e.EventID AND a.StudentID = ?
    LEFT JOIN certificates c ON c.EventID = e.EventID AND c.StudentID = ?
    WHERE r.StudentID = ?
    ORDER BY e.EventDate DESC
");
$stmt->bind_param("iii", $studentID, $studentID, $studentID);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
$upcomingEvents = [];
$attendedEvents = [];
$missedEvents = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
    $isAttended = $row['IsAttended'] == 1;
    $isUpcoming = strtotime($row['EventDate']) >= strtotime('today');
    if ($isAttended) {
        $attendedEvents[] = $row;
    } elseif ($isUpcoming) {
        $upcomingEvents[] = $row;
    } else {
        $missedEvents[] = $row;
    }
}

$pageTitle = 'Student Dashboard - IU Events';
include 'includes/header.php';
?>

<!-- Header -->
<div class="mb-4">
    <h2 class="fw-bold mb-1 text-light">Dashboard</h2>
    <p class="text-white-50">Welcome back, <?php echo htmlspecialchars($studentName); ?> 👋</p>
</div>

<!-- Stats -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-white-50 small mb-1 uppercase tracking-wider">TOTAL REGISTERED</p>
                    <h2 class="fw-bold mb-0 text-white"><?php echo $totalRegs; ?></h2>
                </div>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-calendar-event"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-white-50 small mb-1 uppercase tracking-wider">UPCOMING</p>
                    <h2 class="fw-bold mb-0 text-white"><?php echo $upcomingRegs; ?></h2>
                </div>
                <div class="stat-icon stat-icon-gold"><i class="bi bi-hourglass-split"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-white-50 small mb-1 uppercase tracking-wider">ATTENDED</p>
                    <h2 class="fw-bold mb-0 text-white"><?php echo $totalAttended; ?></h2>
                </div>
                <div class="stat-icon stat-icon-emerald"><i class="bi bi-person-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-white-50 small mb-1 uppercase tracking-wider">CERTIFICATES</p>
                    <h2 class="fw-bold mb-0 text-white"><?php echo $totalCerts; ?></h2>
                </div>
                <div class="stat-icon stat-icon-violet"><i class="bi bi-award"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Sections -->
<?php if (!empty($events)): ?>

<div class="mb-3 mt-4">
    <h5 class="fw-bold text-light mb-0"><i class="bi bi-hourglass-split text-primary me-2"></i>Upcoming Events</h5>
</div>
<?php renderEventsTable($upcomingEvents, true); ?>

<div class="mb-3 mt-5">
    <h5 class="fw-bold text-light mb-0"><i class="bi bi-check-circle text-success me-2"></i>Attended Events</h5>
</div>
<?php renderEventsTable($attendedEvents, false); ?>

<div class="mb-3 mt-5">
    <h5 class="fw-bold text-light mb-0"><i class="bi bi-x-circle text-danger me-2"></i>Missed Events</h5>
</div>
<?php renderEventsTable($missedEvents, false); ?>

<?php else: ?>
<div class="card card-custom p-5 text-center">
    <i class="bi bi-calendar-x display-2 text-white mb-4" style="opacity:0.1;"></i>
    <h5 class="text-white">No Events Registered Yet</h5>
    <p class="text-white-50 mb-4">Discover and join upcoming events tailored to your interests.</p>
    <a href="events_all.php" class="btn btn-login rounded-pill px-5">Explore Events</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>