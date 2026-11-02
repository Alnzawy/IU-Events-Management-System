<?php
require_once 'config.php';
require_once 'includes/GeminiEngine.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}

if (empty(GEMINI_API_KEY)) {
    echo json_encode(['reply' => '⚠️ Gemini API key is not configured.']);
    exit;
}

// ── Fetch UPCOMING events only ──────────────────────────────────────────────
$today   = date('Y-m-d');
$rawResult = $conn->query("
    SELECT e.Title, e.Category, e.EventDate,
           DATE_FORMAT(e.EventDate, '%M %d %Y')   AS FormattedDate,
           DATE_FORMAT(e.TimeToStart, '%h:%i %p') AS TimeStart,
           h.Name AS Hall, h.Capacity, e.Description, e.IsCertified,
           (SELECT COUNT(*) FROM registrations r WHERE r.EventID = e.EventID) AS Registered
    FROM events e
    LEFT JOIN halls h ON e.HallID = h.HallID
    WHERE e.EventDate >= CURDATE()
    ORDER BY e.EventDate ASC, e.TimeToStart ASC
    LIMIT 40
");

$lines = [];
while ($row = $rawResult->fetch_assoc()) {
    $seatsLeft = max(0, $row['Capacity'] - $row['Registered']);
    $cert      = $row['IsCertified'] ? 'Cert' : 'No cert';
    $desc      = mb_substr($row['Description'] ?? '', 0, 80);
    $lines[]   = "{$row['Title']} | {$row['Category']} | {$row['FormattedDate']} {$row['TimeStart']} | {$row['Hall']} | {$seatsLeft} seats left | {$cert} | {$desc}";
}

// ── Call GeminiEngine ───────────────────────────────────────────────────────────────
$engine = new GeminiEngine(GEMINI_API_KEY);
$reply = $engine->chat($userMessage, $lines);

if (strpos($reply, 'Error:') === 0) {
    echo json_encode(['error' => $reply]);
} else {
    echo json_encode(['reply' => $reply]);
}
?>
