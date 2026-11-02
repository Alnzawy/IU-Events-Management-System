<?php
require_once '../config.php';
checkRole('host');
$pageCss = ['feedback.css'];


$hostID = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $eventID = intval($_POST['event_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating.";
    } else {
        $stmt = $conn->prepare("SELECT FeedbackID FROM host_feedback WHERE EventID = ? AND HostID = ?");
        $stmt->bind_param("ii", $eventID, $hostID);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "You have already submitted feedback for this event.";
        } else {
            $stmt = $conn->prepare("INSERT INTO host_feedback (EventID, HostID, Rating, Comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $eventID, $hostID, $rating, $comment);
            if ($stmt->execute()) {
                $message = "Feedback submitted successfully!";
            } else {
                $error = "Failed to submit feedback. Please try again.";
            }
        }
    }
}

$stmt = $conn->prepare("
    SELECT e.*, h.Name AS HallName, f.FeedbackID, f.Rating, f.Comment AS HostComment
    FROM events e
    LEFT JOIN halls h ON e.HallID = h.HallID
    LEFT JOIN host_feedback f ON e.EventID = f.EventID AND f.HostID = ?
    WHERE e.HostID = ? AND e.EventDate < CURDATE()
    ORDER BY e.EventDate DESC, e.TimeToStart DESC;
");
$stmt->bind_param("ii", $hostID, $hostID);
$stmt->execute();
$eventsList = $stmt->get_result();

$pageTitle = 'Rate Events - IU Events';
include 'includes/header.php';
?>



<div class="scroll-main">
    <div class="page-header bordered">
        <div>
            <h1 class="page-title">Rate <span class="title-accent">Events</span></h1>
            <p class="page-sub">Provide feedback on your completed sessions</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-obsidian mb-4"><i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-obsidian error mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($eventsList->num_rows > 0): ?>
        <div class="card card-custom border-0 overflow-hidden" style="background: var(--obsidian-card);">
            <div class="table-container p-0">
                <table class="table-modern mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Date</th>
                            <th>Hall</th>
                            <th width="40%">Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($event = $eventsList->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="event-title"><?php echo htmlspecialchars($event['Title']); ?></div>
                                    <span class="category-badge mt-2 d-inline-block"><?php echo htmlspecialchars($event['Category']); ?></span>
                                </td>
                                <td>
                                    <div class="date-wrapper text-white">
                                        <i class="bi bi-calendar3 date-icon"></i>
                                        <div>
                                            <div class="small fw-bold"><?php echo date('M d, Y', strtotime($event['EventDate'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-white-50">
                                        <i class="bi bi-geo-alt me-2 text-gold"></i>
                                        <?php echo htmlspecialchars($event['HallName'] ?? 'Not Assigned'); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($event['FeedbackID']): ?>
                                        <div class="rated-stars mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?php echo ($i <= $event['Rating']) ? 'bi-star-fill' : 'bi-star empty'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2 text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Submitted</span>
                                        </div>
                                        <?php if (!empty($event['HostComment'])): ?>
                                            <div class="submitted-comment">"<?php echo nl2br(htmlspecialchars($event['HostComment'])); ?>"</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form method="POST" class="feedback-form-cell">
                                            <input type="hidden" name="event_id" value="<?php echo $event['EventID']; ?>">

                                            <!-- Stars row -->
                                            <div class="feedback-star-row">
                                                <div class="star-rating">
                                                    <input type="radio" id="star5_<?php echo $event['EventID']; ?>" name="rating" value="5" required />
                                                    <label for="star5_<?php echo $event['EventID']; ?>" title="5 stars"><i class="bi bi-star-fill"></i></label>
                                                    <input type="radio" id="star4_<?php echo $event['EventID']; ?>" name="rating" value="4" />
                                                    <label for="star4_<?php echo $event['EventID']; ?>" title="4 stars"><i class="bi bi-star-fill"></i></label>
                                                    <input type="radio" id="star3_<?php echo $event['EventID']; ?>" name="rating" value="3" />
                                                    <label for="star3_<?php echo $event['EventID']; ?>" title="3 stars"><i class="bi bi-star-fill"></i></label>
                                                    <input type="radio" id="star2_<?php echo $event['EventID']; ?>" name="rating" value="2" />
                                                    <label for="star2_<?php echo $event['EventID']; ?>" title="2 stars"><i class="bi bi-star-fill"></i></label>
                                                    <input type="radio" id="star1_<?php echo $event['EventID']; ?>" name="rating" value="1" />
                                                    <label for="star1_<?php echo $event['EventID']; ?>" title="1 star"><i class="bi bi-star-fill"></i></label>
                                                </div>
                                            </div>

                                            <!-- Comment -->
                                            <textarea name="comment" class="comment-input" rows="2" placeholder="Share your experience (optional)..."></textarea>

                                            <!-- Submit -->
                                            <div class="feedback-submit-row">
                                                <button type="submit" name="submit_feedback" class="btn btn-sm btn-gold">Submit</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="app-empty-state">
            <i class="bi bi-calendar-check"></i>
            <h4>No Past Events</h4>
            <p>You haven't hosted any events yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>