<?php
/**
 * event_report.php - Detailed Performance Report for Individual Events.
 * Allows organizers to drill down into a specific past event to see 
 * retention funnels, student feedback, and host ratings.
 */
require_once '../config.php';
checkRole('organizer');

// ── 1. Fetch List of Past Events ─────────────────────────────────────
// Grouping events by Month/Year to provide a structured dropdown for the user.
$pastEventsResult = $conn->query("
    SELECT e.EventID, e.Title, DATE_FORMAT(e.EventDate, '%M %d, %Y') AS FormattedDate, 
           DATE_FORMAT(e.EventDate, '%M %Y') AS MonthYear, h.Name AS HallName
    FROM events e
    LEFT JOIN halls h ON e.HallID = h.HallID
    WHERE e.EventDate < CURDATE()
    ORDER BY e.EventDate DESC
");
$pastEvents = [];
while ($row = $pastEventsResult->fetch_assoc()) {
    $pastEvents[$row['MonthYear']][] = $row;
}

// ── 2. Handle Event Selection ────────────────────────────────────────
$selectedID = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
$event      = null;
$feedbacks  = [];
$ratingDist = [];
$totalReg = $totalAttended = $totalAbsent = $totalFeedback = 0;
$avgRating = $attendanceRate = $feedbackRate = $dropOffRate = 0;

if ($selectedID) {
    // Fetch core event metadata (Hall, Host, Capacity)
    $stmt = $conn->prepare("
        SELECT e.*, h.Name AS HallName, h.Capacity, hst.Name AS HostName
        FROM events e
        LEFT JOIN halls h ON e.HallID = h.HallID
        LEFT JOIN hosts hst ON e.HostID = hst.HostID
        WHERE e.EventID = ? AND e.EventDate < CURDATE()
    ");
    $stmt->bind_param("i", $selectedID);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if ($event) {
        // A. Registration Volume
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM registrations WHERE EventID = ?");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $totalReg = $stmt->get_result()->fetch_assoc()['cnt'];

        // B. Attendance Metrics (Attended vs Absent)
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM attendance WHERE EventID = ? AND Attended = 1");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $totalAttended = $stmt->get_result()->fetch_assoc()['cnt'];

        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM attendance WHERE EventID = ? AND Attended = 0");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $totalAbsent = $stmt->get_result()->fetch_assoc()['cnt'];

        // C. Student Feedback Aggregates
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt, ROUND(AVG(Rating), 1) AS avg FROM feedback WHERE EventID = ?");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $fbRow = $stmt->get_result()->fetch_assoc();
        $totalFeedback = $fbRow['cnt'];
        $avgRating     = $fbRow['avg'] ?? 0;

        // D. Rating Distribution (for the star bar chart)
        $stmt = $conn->prepare("SELECT Rating, COUNT(*) AS cnt FROM feedback WHERE EventID = ? GROUP BY Rating");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $distResult = $stmt->get_result();
        while ($r = $distResult->fetch_assoc()) $ratingDist[$r['Rating']] = $r['cnt'];

        // E. Individual Student Comments
        $stmt = $conn->prepare("
            SELECT f.Rating, f.Comment, s.Name AS StudentName
            FROM feedback f
            JOIN students s ON f.StudentID = s.StudentID
            WHERE f.EventID = ?
            ORDER BY f.Rating DESC
        ");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $fbResult = $stmt->get_result();
        while ($row = $fbResult->fetch_assoc()) $feedbacks[] = $row;

        // F. Host Feedback (Speaker's perspective)
        $hostFeedback = null;
        $stmt = $conn->prepare("
            SELECT hf.Rating, hf.Comment, h.Name AS HostName
            FROM host_feedback hf
            JOIN hosts h ON hf.HostID = h.HostID
            WHERE hf.EventID = ?
        ");
        $stmt->bind_param("i", $selectedID); $stmt->execute();
        $hfResult = $stmt->get_result();
        if ($hfResult->num_rows > 0) {
            $hostFeedback = $hfResult->fetch_assoc();
        }
    }
}

$pageTitle = 'Event Report - Organizer Portal';
include 'includes/header.php';
?>


<div class="scroll-main" id="reportContent">
    
    <!-- Top Selector Bar -->
    <div class="selector-bar">
        <h1 class="page-title">Event <span class="title-accent">Report</span></h1>
        
        <form method="GET" class="m-0">
            <select name="event_id" class="event-select" onchange="this.form.submit()">
                <option value="">— Select a past event to analyze —</option>
                <?php foreach ($pastEvents as $month => $eventsList): ?>
                    <option value="" disabled style="font-weight:bold; color:var(--gold-primary); background:#0B1121;">── <?=htmlspecialchars($month)?> ──</option>
                    <?php foreach ($eventsList as $pe): ?>
                        <option value="<?=$pe['EventID']?>" <?=($selectedID == $pe['EventID'])?'selected':''?>>
                            &nbsp;&nbsp;&nbsp;<?=htmlspecialchars($pe['Title'])?> (<?=$pe['FormattedDate']?>)
                        </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if($selectedID && $event): ?>
    <div class="report-grid">
        
        <!-- ── Left Panel: Primary Stats & Feedback ── -->
        <div class="left-panel">
            <!-- Premium Ticket Header: Event Branding -->
            <div class="event-header">
                <div class="badge-wrap">
                    <span class="badge-cat"><?=htmlspecialchars($event['Category'])?></span>
                    <?php if($event['IsCertified']): ?><span class="badge-cert"><i class="bi bi-award-fill me-1"></i>Certified Event</span><?php endif; ?>
                </div>
                
                <h3 class="ev-title"><?=htmlspecialchars($event['Title'])?></h3>
                
                <!-- Quick Meta Information -->
                <div class="ev-meta-grid">
                    <div class="ev-meta-item">
                        <div class="ev-meta-icon"><i class="bi bi-calendar3"></i></div>
                        <div class="ev-meta-text"><?=date('l, F j, Y', strtotime($event['EventDate']))?></div>
                    </div>
                    <div class="ev-meta-item">
                        <div class="ev-meta-icon"><i class="bi bi-clock"></i></div>
                        <div class="ev-meta-text"><?=date('H:i', strtotime($event['TimeToStart']))?> - <?=date('H:i', strtotime($event['TimeToEnd']))?></div>
                    </div>
                    <div class="ev-meta-item">
                        <div class="ev-meta-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="ev-meta-text"><?=htmlspecialchars($event['HallName'])?> <span style="opacity:0.5">(<?=$event['Capacity']?> cap)</span></div>
                    </div>
                    <div class="ev-meta-item">
                        <div class="ev-meta-icon"><i class="bi bi-mic-fill"></i></div>
                        <div class="ev-meta-text">Host: <?=htmlspecialchars($event['HostName'])?></div>
                    </div>
                </div>
            </div>

            <!-- Retention Funnel: Tracking Student Conversion -->
            <div class="a-card">
                <div class="card-title mb-4"><span><i class="bi bi-funnel-fill me-2" style="color:var(--text-muted);"></i>Retention Funnel</span></div>
                <div class="funnel-container">
                    <?php
                        $pAtt = $totalReg>0 ? round(($totalAttended/$totalReg)*100):0;
                        $pFb  = $totalReg>0 ? round(($totalFeedback/$totalReg)*100):0;
                    ?>
                    <div class="f-stage">
                        <div class="f-header">
                            <div class="f-label">Registered</div>
                            <div class="f-meta"><span><?=$totalReg?></span>students</div>
                        </div>
                        <div class="f-track"><div class="f-fill reg" style="width:100%;"></div></div>
                    </div>
                    
                    <div class="f-stage">
                        <div class="f-header">
                            <div class="f-label">Attended</div>
                            <div class="f-meta"><span><?=$totalAttended?></span>(<?=$pAtt?>% retention)</div>
                        </div>
                        <div class="f-track"><div class="f-fill att" style="width:<?=$pAtt?>%;"></div></div>
                    </div>
                    
                    <div class="f-stage">
                        <div class="f-header">
                            <div class="f-label">Feedback</div>
                            <div class="f-meta"><span><?=$totalFeedback?></span>(<?=$pFb?>% response rate)</div>
                        </div>
                        <div class="f-track"><div class="f-fill fbk" style="width:<?=$pFb?>%;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right Panel: Satisfaction Summary ── -->
        <div class="right-panel">
            <!-- Overall Satisfaction Distribution -->
            <div class="a-card" style="flex-shrink:0;">
                <div class="card-title d-flex justify-content-between mb-4">
                    <span><i class="bi bi-star-half me-2" style="color:var(--gold-primary);"></i>Rating</span>
                    <span style="font-size: 1.5rem; color: #fff; line-height:1;"><?=$avgRating?:'—'?> <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600">/5 Avg</span></span>
                </div>
                <?php if($totalFeedback > 0): ?>
                    <div class="rating-grid">
                    <?php for($s=5; $s>=1; $s--): 
                        $cnt = $ratingDist[$s]??0; $pct = round(($cnt/$totalFeedback)*100); ?>
                        <div class="rating-row">
                            <div class="stars-wrap">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="bi bi-star-fill r-star <?=$i<=$s?'filled':''?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="rate-trk"><div class="rate-fil" style="width:<?=$pct?>%;"></div></div>
                            <div class="rate-cnt"><?=$cnt?></div>
                        </div>
                    <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted mt-2 mb-2" style="font-size:0.85rem;">No ratings yet.</div>
                <?php endif; ?>
            </div>

            <!-- Host Feedback Section (Speaker Perspective) -->
            <div class="a-card" style="border-color: rgba(52, 211, 153, 0.3);">
                <div class="card-title mb-3">
                    <span><i class="bi bi-mic-fill me-2" style="color:#34D399;"></i>Host Feedback</span>
                </div>
                <?php if ($hostFeedback): ?>
                    <div class="fb-item" style="border-color: rgba(52, 211, 153, 0.2); background: rgba(52, 211, 153, 0.05);">
                        <div class="fb-head">
                            <span class="fb-name"><?=htmlspecialchars($hostFeedback['HostName'])?></span>
                            <span class="fb-stars" style="color:#34D399; background:rgba(52, 211, 153, 0.1);"><?=$hostFeedback['Rating']?> ★</span>
                        </div>
                        <p class="fb-txt" style="color: #F8FAFC;"><?=$hostFeedback['Comment'] ? '"'.htmlspecialchars($hostFeedback['Comment']).'"' : '<span style="font-style:italic;opacity:0.5;">No comment provided</span>'?></p>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted mt-2 mb-2" style="font-size:0.85rem;">The host has not rated this event yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Feedback List (Full Width) -->
    <div class="a-card mt-4" style="padding-bottom:0.75rem;">
        <div class="card-title mb-3 d-flex justify-content-between">
            <span><i class="bi bi-chat-left-text me-2" style="color:var(--text-muted);"></i>Student Feedback</span>
            <span style="color:var(--text-muted); font-size:0.8rem; font-weight:600; text-transform:none;"><?=$totalFeedback?> total</span>
        </div>
        <div class="fb-list">
            <?php if(empty($feedbacks)): ?>
                <div class="text-center text-muted mt-4" style="font-size:0.85rem;">No written feedback submitted.</div>
            <?php else: ?>
                <?php foreach($feedbacks as $fb): ?>
                <div class="fb-item">
                    <div class="fb-head">
                        <span class="fb-name"><?=htmlspecialchars($fb['StudentName'])?></span>
                        <span class="fb-stars"><?=$fb['Rating']?> ★</span>
                    </div>
                    <p class="fb-txt"><?=$fb['Comment'] ? '"'.htmlspecialchars($fb['Comment']).'"' : '<span style="font-style:italic;opacity:0.5;">No comment provided</span>'?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>




    <?php elseif($selectedID): ?>
        <div class="empty-main"><h4 class="text-white-50 mb-0">Event not found.</h4></div>
    <?php else: ?>
        <div class="empty-main">
            <i class="bi bi-layout-text-window mb-3" style="font-size:3.5rem; opacity:0.7; color:#F8FAFC;"></i>
            <h4 class="mb-0" style="font-weight:600;color:#F8FAFC;">Select an event to view its report</h4>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
