<?php
require_once '../config.php';
checkRole('student');

$studentID = $_SESSION['user_id'];
$eventID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$shouldDownload = isset($_GET['download']) && $_GET['download'] == 1;

// Verify attendance and check if a certificate was officially issued
$stmt = $conn->prepare("
    SELECT e.Title, e.EventDate, e.Category, s.Name as StudentName, hst.Name as HostName, hst.Signature as HostSignature
    FROM certificates c
    JOIN events e ON c.EventID = e.EventID
    JOIN students s ON c.StudentID = s.StudentID
    LEFT JOIN hosts hst ON e.HostID = hst.HostID
    WHERE c.StudentID = ? AND c.EventID = ?
");
$stmt->bind_param("ii", $studentID, $eventID);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Access Denied: This certificate has not been issued to you yet.");
}

// --- Dynamic Theme Logic ---
$category = $data['Category'];
$theme = [
    'primary' => '#d4af37', // Gold
    'secondary' => '#003366', // Deep Blue
    'accent' => '#fdfdfd',
    'border' => 'double',
    'badge' => 'bi-award'
];

if (in_array($category, ['Workshop', 'Training', 'Bootcamp'])) {
    $theme['primary'] = '#10b981'; // Emerald
    $theme['secondary'] = '#064e3b'; // Dark Green
    $theme['badge'] = 'bi-tools';
} elseif (in_array($category, ['Lecture', 'Seminar', 'Panel', 'Orientation'])) {
    $theme['primary'] = '#3b82f6'; // Bright Blue
    $theme['secondary'] = '#1e3a8a'; // Royal Blue
    $theme['badge'] = 'bi-book';
} elseif (in_array($category, ['Competition', 'Masterclass'])) {
    $theme['primary'] = '#ef4444'; // Red
    $theme['secondary'] = '#7f1d1d'; // Crimson
    $theme['badge'] = 'bi-trophy';
}

$certID = "CERT-" . str_pad($eventID, 4, '0', STR_PAD_LEFT) . "-" . str_pad($studentID, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($data['Title']); ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Pinyon+Script&display=swap" rel="stylesheet">
    
    <!-- html2pdf.js via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <link rel="stylesheet" href="../assets/css/common.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/certificates.css?v=<?php echo time(); ?>">

    <style>
        :root {
            --cert-primary: <?php echo $theme['primary']; ?>;
            --cert-secondary: <?php echo $theme['secondary']; ?>;
            --cert-accent: <?php echo $theme['accent']; ?>;
        }
    </style>
    <!-- Bootstrap Icons for the watermark/badge -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
</head>
<body class="cert-body">

    <div class="actions-bar">
        <button onclick="downloadPDF()" class="btn-download">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Download PDF
        </button>
    </div>

    <div class="cert-wrapper">
        <div id="certificate">
            <div class="border-outer"></div>
            <div class="border-inner"></div>
            <div class="cert-watermark"><i class="bi <?php echo $theme['badge']; ?>"></i></div>
            
            <div class="cert-content">
                <img src="../assets/images/university_logo.png" alt="University Logo" class="cert-logo">
                
                <i class="bi <?php echo $theme['badge']; ?> cert-category-icon"></i>

                <h1 class="cert-title">Certificate</h1>
                <p class="cert-subtitle">Of <?php echo ($category == 'Competition') ? 'Victory' : 'Achievement'; ?></p>
                
                <p class="cert-presented-to">This certificate is proudly presented to</p>
                <div class="cert-student-name"><?php echo htmlspecialchars($data['StudentName']); ?></div>
                
                <p class="cert-event-details">
                    for successful participation in the <strong><?php echo htmlspecialchars($category); ?></strong><br>
                    <strong>"<?php echo htmlspecialchars($data['Title']); ?>"</strong><br>
                    held on <?php echo date('F jS, Y', strtotime($data['EventDate'])); ?>
                </p>

                <div class="cert-footer">
                    <div class="cert-signature-box">
                        <span class="cert-cursive-signature"><?php 
                            $nameParts = explode(' ', ($data['HostName'] ?? 'Host'));
                            if (count($nameParts) > 1) {
                                echo htmlspecialchars($nameParts[0] . ' ' . end($nameParts)); 
                            } else {
                                echo htmlspecialchars($data['HostName'] ?? 'Event Host');
                            }
                        ?></span>
                        <div class="cert-signer-name"><?php echo htmlspecialchars($data['HostName'] ?? 'Event Coordinator'); ?></div>
                        <div class="cert-signer-role">Event Host</div>
                    </div>

                    <div class="cert-signature-box">
                        <span class="cert-cursive-signature">IU Events</span>
                        <div class="cert-signer-name">IU Events Team</div>
                        <div class="cert-signer-role">Office of Student Affairs</div>
                    </div>
                </div>

                <div class="cert-id"><?php echo $certID; ?></div>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('certificate');
            const opt = {
                margin:       0,
                filename:     'Certificate_<?php echo preg_replace('/[^A-Za-z0-9_-]/', '_', $data['Title']); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true,
                    letterRendering: true,
                    scrollY: -18,
                    scrollX: -18,
                    windowWidth: 1120,
                    windowHeight: 760
                },
                jsPDF:        { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'landscape'
                }
            };

            // Use html2pdf with the specific options, and intercept to delete any extra blank pages
            html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
                const totalPages = pdf.internal.getNumberOfPages();
                // If html2pdf generated a second blank page due to fractional overflow, delete it
                for (let i = totalPages; i > 1; i--) {
                    pdf.deletePage(i);
                }
            }).save();
        }

        // Auto-download if requested
        <?php if ($shouldDownload): ?>
        window.onload = function() {
            setTimeout(downloadPDF, 1000);
        };
        <?php endif; ?>
    </script>
</body>
</html>
