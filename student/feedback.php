<?php
require_once '../config.php';
checkRole('student');
$pageCss = ['feedback.css'];

$studentID = $_SESSION['user_id'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $eventID = intval($_POST['event_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating.";
    } else {
        $stmt = $conn->prepare("SELECT FeedbackID FROM feedback WHERE EventID = ? AND StudentID = ?");
        $stmt->bind_param("ii", $eventID, $studentID);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "You have already submitted feedback for this event.";
        } else {
            $stmt = $conn->prepare("INSERT INTO feedback (EventID, StudentID, Rating, Comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $eventID, $studentID, $rating, $comment);
            if ($stmt->execute()) {
                $message = "Feedback submitted successfully!";
            } else {
                $error = "Failed to submit feedback. Please try again.";
            }
        }
    }
}

$stmt = $conn->prepare("
    SELECT e.*, h.Name AS HallName, f.FeedbackID, f.Rating, f.Comment AS StudentComment
    FROM attendance a
    JOIN events e ON a.EventID = e.EventID
    LEFT JOIN halls h ON e.HallID = h.HallID
    LEFT JOIN feedback f ON e.EventID = f.EventID AND f.StudentID = ?
    WHERE a.StudentID = ? AND e.EventDate <= CURDATE()
    ORDER BY e.EventDate DESC, e.TimeToStart DESC;
");
$stmt->bind_param("ii", $studentID, $studentID);
$stmt->execute();
$eventsList = $stmt->get_result();

$pendingFeedback = [];
$submittedFeedback = [];
while ($row = $eventsList->fetch_assoc()) {
    if ($row['FeedbackID']) {
        $submittedFeedback[] = $row;
    } else {
        $pendingFeedback[] = $row;
    }
}

function renderFeedbackTable($events, $isPending) {
    if (empty($events)) return;
    ?>
    <h5 class="fw-bold mb-3 text-gold"><i class="bi bi-<?php echo $isPending ? 'hourglass-split' : 'check2-all'; ?> me-2"></i><?php echo $isPending ? 'Pending Feedback' : 'Submitted Feedback'; ?></h5>
    <div class="card card-custom border-0 overflow-hidden mb-5">
        <div class="table-container p-0">
            <table class="table-modern mb-0 w-100">
                <thead>
                    <tr>
                        <th>Event Details</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th width="40%" class="pe-4">Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <div class="event-title mb-2"><?php echo htmlspecialchars($event['Title']); ?></div>
                                <span class="category-badge me-1"><?php echo htmlspecialchars($event['Category']); ?></span>
                                <?php if ($event['IsCertified']): ?>
                                    <span class="badge" style="background: rgba(212,175,55,0.1); color: #D4AF37; border: 1px solid rgba(212,175,55,0.25); font-weight: 600;">
                                        <i class="bi bi-award-fill me-1"></i>Certified
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="date-wrapper text-white text-nowrap">
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
                            <td class="pe-4">
                                <?php if (!$isPending): ?>
                                    <div class="rated-stars mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?php echo ($i <= $event['Rating']) ? 'bi-star-fill' : 'bi-star empty'; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2 text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Submitted</span>
                                    </div>
                                    <?php if (!empty($event['StudentComment'])): ?>
                                        <div class="submitted-comment">"<?php echo nl2br(htmlspecialchars($event['StudentComment'])); ?>"</div>
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
                                        
                                        <textarea name="comment" class="comment-input" rows="2" placeholder="Share your experience (optional)..."></textarea>
                                        
                                        <div class="feedback-submit-row mt-1">
                                            <button type="submit" name="submit_feedback" class="btn btn-sm btn-gold px-4">Submit</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

$pageTitle = 'Rate Events - IU Events';
include 'includes/header.php';
?>


    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-light">Rate Events</h2>
        <p class="text-white-50">Provide feedback on your completed sessions</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success bg-success text-white border-0"><i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger bg-danger text-white border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($pendingFeedback) || !empty($submittedFeedback)): ?>
        <?php renderFeedbackTable($pendingFeedback, true); ?>
        <?php renderFeedbackTable($submittedFeedback, false); ?>
    <?php else: ?>
        <div class="app-empty-state text-center py-5">
            <i class="bi bi-calendar-check display-1 text-white-50 opacity-25"></i>
            <h4 class="mt-4 text-white">No Past Events</h4>
            <p class="text-white-50">You haven't attended any events yet.</p>
        </div>
    <?php endif; ?>


<?php include 'includes/footer.php'; ?>