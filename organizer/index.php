<?php
/**
 * index.php - Organizer Portal Dashboard Home.
 * Provides a high-level snapshot of program volume, student reach, 
 * and active certificates, along with a live attendance trend chart.
 */
require_once '../config.php';
checkRole('organizer');

// ── 1. Global KPI Counters ──────────────────────────────────────────
// Quick statistics shown in the header KPI cards.
$total_events   = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
$total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
$total_hosts    = $conn->query("SELECT COUNT(*) as c FROM hosts")->fetch_assoc()['c'];
$total_certs    = $conn->query("SELECT COUNT(*) as c FROM certificates")->fetch_assoc()['c'];
$upcoming_count = $conn->query("SELECT COUNT(*) as c FROM events WHERE EventDate > CURDATE()")->fetch_assoc()['c'];

// ── 2. Attendance Volume Trend Data ─────────────────────────────────
// Aggregates total student turnout by date to visualize program growth.
// Filters only for events that have already occurred.
$trend_res = $conn->query("
    SELECT e.EventDate, SUM(a.Attended) as att
    FROM attendance a JOIN events e ON a.EventID=e.EventID
    WHERE e.EventDate <= CURDATE()
    GROUP BY e.EventDate ORDER BY e.EventDate ASC
");
$trend_labels=[]; $trend_data=[];
while($r=$trend_res->fetch_assoc()){
    $trend_labels[] = date('M d', strtotime($r['EventDate']));
    $trend_data[]   = (int)$r['att'];
}

$pageTitle = 'Dashboard — IU Events Organizer';
include 'includes/header.php';
?>

<div class="dash-main">
    <!-- Dashboard Header -->
    <div class="page-header no-bg">
        <div>
            <h1 class="page-title">Program <span class="title-accent">Volume</span></h1>
            <p class="page-sub">Live capacity snapshot · Data up to <?=date('F j, Y')?></p>
        </div>
    </div>

    <!-- Top KPI Cards Section -->
    <div class="kpi-row">
        <!-- Events KPI -->
        <div class="kpi-card">
            <div class="kpi-icon ic-events"><i class="bi bi-calendar2-event-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=number_format($total_events)?></div>
                <div class="kpi-label">Total Events</div>
                <div class="kpi-sub"><?=$upcoming_count?> upcoming</div>
            </div>
        </div>
        <!-- Students KPI -->
        <div class="kpi-card">
            <div class="kpi-icon ic-students"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=number_format($total_students)?></div>
                <div class="kpi-label">Students</div>
                <div class="kpi-sub">Registered users</div>
            </div>
        </div>
        <!-- Hosts KPI -->
        <div class="kpi-card">
            <div class="kpi-icon ic-hosts"><i class="bi bi-mic-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=number_format($total_hosts)?></div>
                <div class="kpi-label">Hosts</div>
                <div class="kpi-sub">Available speakers</div>
            </div>
        </div>
        <!-- Certificates KPI -->
        <div class="kpi-card">
            <div class="kpi-icon ic-certs"><i class="bi bi-award-fill"></i></div>
            <div class="kpi-info">
                <div class="kpi-val"><?=number_format($total_certs)?></div>
                <div class="kpi-label">Certificates</div>
                <div class="kpi-sub">Issued documents</div>
            </div>
        </div>
    </div>

    <!-- Charts Row: Main Trend Analysis -->
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">Attendance Volume Trend</h3>
                    <p class="chart-sub">Daily student turnout</p>
                </div>
                <div class="chart-icon"><i class="bi bi-activity"></i></div>
            </div>
            <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
        </div>
    </div>
</div>

<script>
    // ── Global Chart.js Defaults ─────────────────────────────────────
    Chart.defaults.color = '#64748B';
    Chart.defaults.font.family = 'Inter';
    Chart.defaults.font.size = 11;

    // High-Premium Tooltip Styling
    const tooltipStyle = {
        backgroundColor: 'rgba(15,23,42,0.95)', titleColor: '#F8FAFC', titleFont: {family:'Outfit', size:13, weight:'bold'},
        bodyColor: '#94A3B8', bodyFont: {size:12}, borderColor: 'rgba(255,255,255,0.1)', borderWidth: 1, padding: 12,
        boxPadding: 6, usePointStyle: true
    };

    // ── Attendance Trend Line Chart ──────────────────────────────────
    // Renders the daily student turnout with a smooth spline and gradient fill.
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?=json_encode($trend_labels)?>,
            datasets: [{
                label: 'Students',
                data: <?=json_encode($trend_data)?>,
                borderColor: '#FACC15',
                backgroundColor: ctx => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    g.addColorStop(0, 'rgba(250, 204, 21, 0.25)');
                    g.addColorStop(1, 'rgba(250, 204, 21, 0)');
                    return g;
                },
                borderWidth: 3, fill: true, tension: 0.4,
                pointRadius: 0, pointHoverRadius: 6, pointHoverBackgroundColor: '#FACC15', pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: {
                y: { border: {display: false}, grid: { color: 'rgba(255,255,255,0.03)', drawTicks: false }, ticks: { padding: 10, maxTicksLimit: 6 } },
                x: { border: {display: false}, grid: { display: false }, ticks: { padding: 10, maxTicksLimit: 8, maxRotation: 0 } }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>

