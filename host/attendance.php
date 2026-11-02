<?php
require_once '../config.php';
checkRole('host');

$hostID = $_SESSION['user_id'];
$selectedEventID = intval($_GET['event_id'] ?? 0);
$message = '';

if (isset($_POST['save_attendance']) && $selectedEventID) {
    $stmt = $conn->prepare("DELETE FROM attendance WHERE EventID=?");
    $stmt->bind_param("i", $selectedEventID);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT StudentID FROM registrations WHERE EventID=?");
    $stmt->bind_param("i", $selectedEventID);
    $stmt->execute();
    $registered = $stmt->get_result();

    $stmt = $conn->prepare("INSERT INTO attendance (EventID,StudentID,Attended) VALUES (?,?,?)");
    while ($row = $registered->fetch_assoc()) {
        $sid = $row['StudentID'];
        $status = isset($_POST['attended'][$sid]) ? 1 : 0;
        $stmt->bind_param("iii", $selectedEventID, $sid, $status);
        $stmt->execute();
    }
    $message = "Attendance saved successfully";
}

if (isset($_POST['issue_certificates']) && $selectedEventID) {
    $stmt = $conn->prepare("
        INSERT IGNORE INTO certificates (EventID,StudentID)
        SELECT EventID,StudentID FROM attendance
        WHERE EventID=? AND Attended=1
    ");
    $stmt->bind_param("i", $selectedEventID);
    $stmt->execute();
    $message = "Certificates issued to attended students";
}

$stmt = $conn->prepare("
    SELECT EventID,Title,TimeToStart
    FROM events
    WHERE HostID=? AND EventDate=CURDATE()
    ORDER BY TimeToStart
");
$stmt->bind_param("i", $hostID);
$stmt->execute();
$todayEvents = $stmt->get_result();

$studentsList = null;
if ($selectedEventID) {
    $stmt = $conn->prepare("
        SELECT s.StudentID, s.Name, a.Attended, c.CertificateID
        FROM registrations r
        JOIN events e ON r.EventID = e.EventID AND e.HostID = ?
        JOIN students s ON r.StudentID = s.StudentID
        LEFT JOIN attendance a ON a.StudentID = s.StudentID AND a.EventID = ?
        LEFT JOIN certificates c ON c.StudentID = s.StudentID AND c.EventID = ?
        WHERE r.EventID = ?
        ORDER BY s.Name
    ");
    $stmt->bind_param("iiii", $hostID, $selectedEventID, $selectedEventID, $selectedEventID);
    $stmt->execute();
    $studentsList = $stmt->get_result();
}

$pageTitle = 'Attendance & Certificates - IU Events';
include 'includes/header.php';
?>

<div class="scroll-main">
    <div class="page-header bordered">
        <div>
            <h1 class="page-title">Attendance & <span class="title-accent">Certificates</span></h1>
            <p class="page-sub">Take attendance and issue certificates for today's events</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-obsidian mb-4"><i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4" style="background: var(--obsidian-card); border: 1px solid var(--obsidian-border);">
        <form method="GET">
            <label class="form-label text-white-50 small mb-2 d-block">Select Today's Event</label>
            <select class="form-select app-form-select" name="event_id" onchange="this.form.submit()" style="max-width: 400px; background: rgba(0,0,0,0.3); color: white; border: 1px solid rgba(255,255,255,0.1);">
                <option value="">Select Event</option>
                <?php while ($e = $todayEvents->fetch_assoc()): ?>
                    <option value="<?php echo $e['EventID'] ?>" <?php if ($selectedEventID == $e['EventID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($e['Title']) . ' (' . date('h:i A', strtotime($e['TimeToStart'])) . ')' ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedEventID && $studentsList && $studentsList->num_rows > 0): ?>
        <form method="POST">
            <div class="card card-custom border-0 overflow-hidden mb-4" style="background: var(--obsidian-card);">
                <div class="table-container p-0">
                    <table class="table-modern mb-0 w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="text-center">Attended</th>
                                <th class="text-center">Certificate Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($s = $studentsList->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-white-50 small"><?php echo $i++ ?></td>
                                    <td class="fw-bold text-white"><?php echo htmlspecialchars($s['Name']) ?></td>
                                    <td class="text-center">
                                        <label class="switch">
                                            <input type="checkbox" name="attended[<?php echo $s['StudentID'] ?>]" <?php if ($s['Attended'] == 1) echo 'checked'; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($s['CertificateID']): ?>
                                            <span class="app-badge app-badge-success">Issued</span>
                                        <?php else: ?>
                                            <span class="app-badge app-badge-muted">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4" style="border-top: 1px solid var(--obsidian-border);">
                    <button name="save_attendance" class="btn btn-gold px-5">
                        <i class="bi bi-save me-2"></i> Save Attendance
                    </button>
                </div>
            </div>
        </form>

        <form method="POST">
            <div class="card card-custom p-4" style="background: rgba(52, 211, 153, 0.05); border: 1px solid rgba(52, 211, 153, 0.2);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-light mb-1"><i class="bi bi-award text-success me-2"></i>Issue Certificates</h5>
                        <p class="text-white-50 small mb-0">Give certificates to everyone marked as attended.</p>
                    </div>
                    <button name="issue_certificates" class="btn btn-gold">
                        <i class="bi bi-award me-1"></i> Issue to Attendees
                    </button>
                </div>
            </div>
        </form>

    <?php elseif ($selectedEventID): ?>
        <div class="app-empty-state">
            <i class="bi bi-people"></i>
            <h4>No Registered Students</h4>
            <p>There are no students registered for this event.</p>
        </div>
    <?php else: ?>
        <div class="app-empty-state">
            <i class="bi bi-arrow-up-circle"></i>
            <h4>Select an Event</h4>
            <p>Choose an event from the dropdown to start taking attendance.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>