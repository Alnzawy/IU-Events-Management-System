<?php
require_once '../config.php';
require_once '../includes/GeminiEngine.php';
require_once 'includes/components.php';
checkRole('student');

$studentID = $_SESSION['user_id'];
$message = $messageType = '';

// Interest pillars → matching event categories
$interestMapping = [
    "AI & Data Intelligence"     => ["Seminar", "Workshop", "Lecture"],
    "Cybersecurity & Systems"    => ["Seminar", "Lecture", "Training", "Workshop"],
    "UX/UI & Creative Media"     => ["Workshop", "Seminar", "Showcase", "Lecture"],
    "Game Dev & Virtual Reality" => ["Workshop", "Showcase", "Seminar"],
    "Startups & Innovation"      => ["Competition", "Workshop", "Lecture", "Panel"],
    "Career & Leadership"        => ["Training", "Seminar", "Orientation", "Workshop"]
];

$categoryImages = [
    'Orientation' => 'assets/images/orientation.jpg',
    'Seminar' => 'assets/images/seminar.jpg',
    'Workshop' => 'assets/images/workshop.jpg',
    'Lecture' => 'assets/images/lecture.jpg',
    'Training' => 'assets/images/training.jpg',
    'Competition' => 'assets/images/competition.jpg',
    'Panel' => 'assets/images/panel.jpg',
    'Showcase' => 'assets/images/showcase.jpg'
];

// --- Handle Registration ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $eventID = intval($_POST['event_id']);
    
    $check = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM registrations WHERE StudentID = ? AND EventID = ?) as IsRegistered,
            (SELECT COUNT(*) FROM registrations WHERE EventID = ?) as RegCount,
            (SELECT Capacity FROM halls WHERE HallID = e.HallID) as Capacity,
            (SELECT COUNT(*) FROM registrations r JOIN events e2 ON r.EventID = e2.EventID 
             WHERE r.StudentID = ? AND e2.EventDate = e.EventDate AND e2.TimeToStart = e.TimeToStart) as Collision
        FROM events e WHERE e.EventID = ?
    ");
    $check->bind_param("iiiii", $studentID, $eventID, $eventID, $studentID, $eventID);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res['IsRegistered']) {
        $message = "Already registered."; $messageType = "warning";
    } elseif ($res['Collision']) {
        $message = "Schedule conflict detected."; $messageType = "danger";
    } elseif ($res['RegCount'] >= $res['Capacity']) {
        $message = "Event is full."; $messageType = "danger";
    } else {
        $reg = $conn->prepare("INSERT INTO registrations (StudentID, EventID) VALUES (?, ?)");
        $reg->bind_param("ii", $studentID, $eventID);
        if ($reg->execute()) {
            $message = "Registered successfully!"; $messageType = "success";
        }
    }
}

// Fetch Student Interests
$stmt = $conn->prepare("SELECT Interests FROM students WHERE StudentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$interestsStr = $stmt->get_result()->fetch_assoc()['Interests'] ?? '';
$studentInterests = empty($interestsStr) ? [] : explode(',', $interestsStr);

// Fetch Available Events
$sql = "
    SELECT e.*, h.Name as HallName, h.Capacity,
    (SELECT COUNT(*) FROM registrations r WHERE r.EventID = e.EventID) as RegCount,
    e.IsCertified
    FROM events e 
    JOIN halls h ON e.HallID = h.HallID
    WHERE e.EventDate >= CURDATE()
    AND e.EventID NOT IN (SELECT EventID FROM registrations WHERE StudentID = ?)
    AND (e.EventDate, e.TimeToStart) NOT IN (
        SELECT e2.EventDate, e2.TimeToStart 
        FROM registrations r 
        JOIN events e2 ON r.EventID = e2.EventID 
        WHERE r.StudentID = ?
    )
    HAVING RegCount < Capacity
    ORDER BY e.EventDate ASC, e.TimeToStart ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $studentID, $studentID);
$stmt->execute();
$dbResult = $stmt->get_result();

$allAvailable = [];
while ($row = $dbResult->fetch_assoc()) {
    $category = $row['Category'];
    $imagePath = isset($categoryImages[$category]) ? '../' . $categoryImages[$category] : '../assets/images/event1.jpg';
    
    $allAvailable[] = [
        'id'          => $row['EventID'],
        'title'       => $row['Title'],
        'date'        => $row['EventDate'],
        'time'        => date('h:i A', strtotime($row['TimeToStart'])),
        'hall'        => $row['HallName'],
        'category'    => $category,
        'image'       => $imagePath,
        'description' => $row['Description'],
        'is_certified'=> $row['IsCertified'],
        'capacity'    => $row['Capacity'],
        'reg_count'   => $row['RegCount'],
        'seats_left'  => max(0, $row['Capacity'] - $row['RegCount'])
    ];
}

// AI Recommendation Logic
$aiResults = [];
$isAiPowered = false;

if (!empty(GEMINI_API_KEY) && !empty($studentInterests) && !empty($allAvailable)) {
    $engine = new GeminiEngine(GEMINI_API_KEY);
    $aiResults = $engine->getRecommendations($studentInterests, $allAvailable);
    if (!empty($aiResults)) {
        $isAiPowered = true;
    }
}

$suggestedPool = [];
$discoveryPool = [];
$suggestedIds = [];
$reasonsMap = [];

if ($isAiPowered) {
    foreach ($aiResults as $res) {
        if (isset($res['id'])) {
            $suggestedIds[] = intval($res['id']);
            $reasonsMap[intval($res['id'])] = $res['reason'] ?? 'Matches your interests.';
        }
    }
}

foreach ($allAvailable as $event) {
    if (in_array($event['id'], $suggestedIds)) {
        $event['ai_reason'] = $reasonsMap[$event['id']] ?? '';
        $suggestedPool[] = $event;
    } else {
        $discoveryPool[] = $event;
    }
}

$pageTitle = 'Smart Explorer - IU Events';
include 'includes/header.php';
?>

<!-- Header -->
<div class="page-header">
    <h1 class="display-6 fw-bold text-white mb-2">Smart Explorer</h1>
    <p class="text-white-50">Personalize your feed and discover activities matched to your profile.</p>
</div>

<?php if ($message): ?>
    <?php 
        $isSuccess = ($messageType === 'success');
        $bgColor = $isSuccess ? 'rgba(16, 185, 129, 0.15)' : ($messageType === 'danger' ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)');
        $borderColor = $isSuccess ? 'rgba(16, 185, 129, 0.3)' : ($messageType === 'danger' ? 'rgba(239, 68, 68, 0.3)' : 'rgba(245, 158, 11, 0.3)');
        $iconColor = $isSuccess ? '#10b981' : ($messageType === 'danger' ? '#ef4444' : '#f59e0b');
        $iconClass = $isSuccess ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    ?>
    <div class="alert alert-dismissible fade show mb-5 d-flex align-items-center justify-content-between shadow-lg" role="alert" 
         style="background: <?php echo $bgColor; ?>; border: 1px solid <?php echo $borderColor; ?>; border-radius: 16px; backdrop-filter: blur(10px);">
        <div class="d-flex align-items-center">
            <i class="bi <?php echo $iconClass; ?> me-3 fs-4" style="color: <?php echo $iconColor; ?>;"></i>
            <span class="fw-medium text-white" style="letter-spacing: 0.3px; font-size: 0.95rem;"><?php echo $message; ?></span>
        </div>
        <button type="button" class="btn-close btn-close-white opacity-75 m-0 p-2" data-bs-dismiss="alert" aria-label="Close" style="position: static;"></button>
    </div>
<?php endif; ?>

<!-- SECTION 1: AI RECOMMENDED (UNIFIED GRID) -->
<?php if (!empty($suggestedPool)): ?>
<div class="grid-label">
    <i class="bi bi-stars"></i> 
    AI RECOMMENDED FOR YOU 
    <?php if ($isAiPowered): ?>
        <span class="ms-2 badge rounded-pill bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25" style="letter-spacing: 0; font-size: 0.6rem; text-transform: none;">
            ✨ Driven by Gemini AI
        </span>
    <?php endif; ?>
    <div class="line"></div>
</div>
<div class="row g-4 mb-5 pb-5">
    <?php foreach ($suggestedPool as $event): ?>
        <?php renderEventCard($event, true); ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- SECTION 2: DISCOVERY FEED (STANDARD CARDS) -->
<div class="grid-label"><i class="bi bi-compass"></i> Discover More Activities <div class="line"></div></div>
<div class="row g-4 mb-5">
    <?php if (!empty($discoveryPool)): ?>
        <?php foreach ($discoveryPool as $event): ?>
            <?php renderEventCard($event, false); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="crystal-card p-5">
                <i class="bi bi-magic fs-1 text-white-10 mb-4"></i>
                <h5 class="text-white">All Events Explored!</h5>
                <p class="text-white-50">There are no other available events at this time. Check back later!</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-5 py-3 mt-3">Go to Dashboard</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Registration Confirmation Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);">
            <!-- Header with image background effect -->
            <div class="modal-header border-0 position-relative p-4" style="background: linear-gradient(135deg, rgba(250,204,21,0.1), rgba(0,0,0,0.8));">
                <div class="position-relative z-index-1">
                    <span id="modalCatBadge" class="badge rounded-pill bg-gold text-dark fw-bold px-3 py-1 mb-2" style="font-size:0.7rem; letter-spacing: 1px;"></span>
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalTitle" style="line-height:1.3;"></h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 pt-3">
                <!-- Icon Grid for Details -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center p-2 rounded-3" style="background: rgba(255,255,255,0.03);">
                            <div class="rounded p-2 me-3" style="background: rgba(255,255,255,0.05); color: #FACC15;"><i class="bi bi-calendar3"></i></div>
                            <div><div class="text-white-50 small" style="font-size:0.7rem;">Date</div><div class="text-white fw-medium" id="modalDate" style="font-size:0.85rem;"></div></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center p-2 rounded-3" style="background: rgba(255,255,255,0.03);">
                            <div class="rounded p-2 me-3" style="background: rgba(255,255,255,0.05); color: #FACC15;"><i class="bi bi-clock"></i></div>
                            <div><div class="text-white-50 small" style="font-size:0.7rem;">Time</div><div class="text-white fw-medium" id="modalTime" style="font-size:0.85rem;"></div></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center p-2 rounded-3" style="background: rgba(255,255,255,0.03);">
                            <div class="rounded p-2 me-3" style="background: rgba(255,255,255,0.05); color: #FACC15;"><i class="bi bi-geo-alt-fill"></i></div>
                            <div><div class="text-white-50 small" style="font-size:0.7rem;">Location</div><div class="text-white fw-medium" id="modalHall" style="font-size:0.85rem;"></div></div>
                        </div>
                    </div>
                </div>

                <div id="modalCertRow" class="align-items-center p-3 rounded-3 mb-4" style="background: rgba(250,204,21,0.1); border: 1px solid rgba(250,204,21,0.2); display:none;">
                    <i class="bi bi-award-fill fs-3 me-3 text-gold"></i>
                    <div><div class="text-gold fw-bold" style="font-size:0.9rem;">Certificated Event</div><div class="text-white-50" style="font-size:0.75rem;">Attendees receive an official certificate</div></div>
                </div>

                <!-- Seats bar -->
                <div class="p-3 rounded-4 mb-4" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-white-50 fw-medium" style="font-size:0.8rem;"><i class="bi bi-people-fill me-2"></i><span id="modalRegCap"></span></span>
                        <span id="modalSeatsLeft" class="fw-bold" style="font-size:0.8rem;"></span>
                    </div>
                    <div style="height:8px; background:rgba(255,255,255,0.05); border-radius:10px; overflow:hidden;">
                        <div id="modalSeatsBar" style="height:100%; border-radius:10px; transition:width 0.5s ease-in-out;"></div>
                    </div>
                </div>

                <div id="modalDesc" class="text-white-50 mb-0" style="font-size:0.85rem; line-height:1.6; display:none; border-left: 3px solid rgba(255,255,255,0.1); padding-left: 1rem;"></div>
            </div>
            
            <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2 flex-nowrap">
                <button type="button" class="btn text-white-50 btn-lg rounded-pill" style="background: rgba(255,255,255,0.05); flex: 1; font-size: 0.95rem; font-weight: 600;" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="modalForm" style="flex: 2; margin-bottom: 0;">
                    <input type="hidden" name="event_id" id="modalEventId">
                    <button type="submit" name="register_event" class="btn btn-gold w-100 btn-lg rounded-pill fw-bold" style="font-size: 0.95rem; box-shadow: 0 4px 15px rgba(250,204,21,0.2);">
                        Confirm Registration
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openReg(btn) {
    const d = btn.dataset;
    document.getElementById('modalTitle').textContent    = d.title;
    document.getElementById('modalCatBadge').textContent = d.cat;
    document.getElementById('modalDate').textContent     = d.date;
    document.getElementById('modalTime').textContent     = d.time;
    document.getElementById('modalHall').textContent     = d.hall;
    document.getElementById('modalEventId').value        = d.id;

    // Certified badge
    document.getElementById('modalCertRow').style.display = d.certified == '1' ? 'flex' : 'none';

    // Seats
    const left = parseInt(d.left), cap = parseInt(d.cap), reg = parseInt(d.reg);
    const pct  = Math.min(100, Math.round((reg / Math.max(1, cap)) * 100));

    document.getElementById('modalRegCap').textContent = reg + ' / ' + cap + ' registered';

    const leftEl = document.getElementById('modalSeatsLeft');
    leftEl.textContent = left > 0 ? left + ' seats left' : 'Fully Booked';
    leftEl.style.color = left === 0 ? '#ef4444' : (left <= 5 ? '#f59e0b' : '#10b981');

    const bar = document.getElementById('modalSeatsBar');
    bar.style.width      = pct + '%';
    bar.style.background = pct >= 100 ? '#ef4444' : (pct >= 80 ? '#f59e0b' : '#10b981');

    // Description
    const descEl = document.getElementById('modalDesc');
    if (d.desc && d.desc.trim()) {
        descEl.textContent  = d.desc.length > 220 ? d.desc.substring(0, 220) + '...' : d.desc;
        descEl.style.display = 'block';
    } else {
        descEl.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('registerModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
