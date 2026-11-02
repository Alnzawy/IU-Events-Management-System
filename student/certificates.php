<?php
require_once '../config.php';
checkRole('student');

$studentID = $_SESSION['user_id'];

// Fetch events where a certificate has been officially issued
$stmt = $conn->prepare("
    SELECT e.EventID, e.Title, e.EventDate, e.Category, hst.Name as HostName
    FROM certificates c
    JOIN events e ON c.EventID = e.EventID
    LEFT JOIN hosts hst ON e.HostID = hst.HostID
    WHERE c.StudentID = ?
    ORDER BY e.EventDate DESC
");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$attendedEvents = $stmt->get_result();

$pageTitle = 'My Certificates - Student Portal';
include 'includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1 text-light">My Certificates</h2>
    <p class="text-white-50">View certificates for completed events</p>
</div>

<?php if ($attendedEvents->num_rows > 0): ?>
    <div class="card card-custom border-0 overflow-hidden">
        <div class="table-container p-0">
            <table class="table-modern mb-0 w-100">
                <thead>
                    <tr>
                        <th>Event Details</th>
                        <th>Date</th>
                        <th>Host / Instructor</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $attendedEvents->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="event-title mb-2"><?php echo htmlspecialchars($row['Title']); ?></div>
                                <span class="category-badge me-1"><?php echo htmlspecialchars($row['Category']); ?></span>
                                <span class="badge" style="background: rgba(212,175,55,0.1); color: #D4AF37; border: 1px solid rgba(212,175,55,0.25); font-weight: 600;">
                                    <i class="bi bi-award-fill me-1"></i>Certified
                                </span>
                            </td>
                            <td>
                                <div class="date-wrapper text-white">
                                    <i class="bi bi-calendar3 date-icon"></i>
                                    <div>
                                        <div class="small fw-bold">
                                            <?php echo date('M d, Y', strtotime($row['EventDate'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-white-50">
                                    <i class="bi bi-person me-2 text-gold"></i>
                                    <?php echo htmlspecialchars($row['HostName'] ?? 'IU Events Team'); ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-gold" onclick="showCertificate(<?php echo $row['EventID']; ?>)">
                                    <i class="bi bi-award me-1"></i> View Certificate
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card card-custom p-5 text-center border-0">
        <i class="bi bi-award display-1 mb-4 opacity-25 text-white"></i>
        <h4 class="text-light">No Certificates Earned Yet</h4>
        <p class="text-white-50">Complete events and verify your attendance to start earning certificates.</p>
    </div>
<?php endif; ?>

<!-- Certificate Modal -->
<div class="modal fade" id="certModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content bg-dark border-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-white"><i class="bi bi-award text-warning me-2"></i>Event Certificate</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="height: 80vh;">
        <iframe id="certIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
function showCertificate(eventId) {
    document.getElementById('certIframe').src = 'certificate_view.php?id=' + eventId;
    var certModal = new bootstrap.Modal(document.getElementById('certModal'));
    certModal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
