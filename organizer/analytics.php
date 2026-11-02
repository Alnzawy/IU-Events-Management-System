<?php
/**
 * analytics.php - High-level Analytics Dashboard for Organizers.
 * This page aggregates data across all events to show attendance trends,
 * satisfaction metrics, and hall utilization.
 */
require_once '../config.php';
checkRole('organizer');

// ── 1. KPI Stats (Cumulative Data) ───────────────────────────────────
// Fetches overall program volume including total registrations, attendance, and feedback ratings.
// Filters only for events that have already occurred (EventDate <= CURDATE()).
$stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM registrations r JOIN events e ON r.EventID=e.EventID WHERE e.EventDate<=CURDATE()) as total_regs,
        (SELECT SUM(a.Attended) FROM attendance a JOIN events e ON a.EventID=e.EventID WHERE e.EventDate<=CURDATE()) as total_atts,
        (SELECT AVG(f.Rating) FROM feedback f JOIN events e ON f.EventID=e.EventID WHERE e.EventDate<=CURDATE()) as avg_rating,
        (SELECT COUNT(*) FROM feedback f JOIN events e ON f.EventID=e.EventID WHERE e.EventDate<=CURDATE()) as total_feedbacks
")->fetch_assoc();

// Initialize KPI variables with fallback values
$total_regs   = $stats['total_regs']   ?? 0;
$total_atts   = $stats['total_atts']   ?? 0;
$total_feedbacks = $stats['total_feedbacks'] ?? 0;
$avg_rating   = $stats['avg_rating'] ? round($stats['avg_rating'], 2) : 0;

// Calculate secondary rates for the dashboard badges
$attendance_rate = ($total_regs > 0) ? round(($total_atts/$total_regs)*100,1) : 0;
$drop_off_rate   = ($total_regs > 0) ? round(100-$attendance_rate,1) : 0;
$feedback_rate   = ($total_atts > 0) ? round(($total_feedbacks/$total_atts)*100,1) : 0;

// ── 2. Hall Utilization Metrics ──────────────────────────────────────
// Calculates how efficiently each hall is being used based on actual student registration
// vs. the hall's maximum capacity.
$hall_res = $conn->query("
    SELECT h.Name, ROUND((COUNT(r.RegistrationID)/(COUNT(DISTINCT e.EventID)*h.Capacity))*100,1) as util
    FROM halls h JOIN events e ON h.HallID=e.HallID JOIN registrations r ON e.EventID=r.EventID
    WHERE e.EventDate<=CURDATE() GROUP BY h.HallID
");
$hall_labels=[]; $hall_data=[];
while($r=$hall_res->fetch_assoc()){ $hall_labels[]=$r['Name']; $hall_data[]=(float)$r['util']; }

// ── 3. Weekly Attendance Rate Evolution ──────────────────────────────
// Tracks the week-over-week performance of student attendance to identify seasonal trends.
$week_att_res = $conn->query("
    SELECT YEARWEEK(e.EventDate,1) AS yw, DATE_FORMAT(MIN(e.EventDate),'%b %d') AS wl,
           ROUND(SUM(a.Attended)/COUNT(r.RegistrationID)*100,1) AS att_rate
    FROM events e JOIN registrations r ON e.EventID=r.EventID
    LEFT JOIN attendance a ON e.EventID=a.EventID AND r.StudentID=a.StudentID
    WHERE e.EventDate<=CURDATE() GROUP BY YEARWEEK(e.EventDate,1) ORDER BY yw ASC
");
$week_labels=[]; $week_rate_data=[];
while($r=$week_att_res->fetch_assoc()){ $week_labels[]='Wk '.$r['wl']; $week_rate_data[]=$r['att_rate']; }

// ── 4. Weekly Satisfaction (Rating) Evolution ────────────────────────
// Monitors the average rating provided by students over time to ensure quality consistency.
$week_rating_res = $conn->query("
    SELECT YEARWEEK(e.EventDate,1) AS yw, DATE_FORMAT(MIN(e.EventDate),'%b %d') AS wl,
           ROUND(AVG(f.Rating),2) AS avg_rating
    FROM events e JOIN feedback f ON e.EventID=f.EventID
    WHERE e.EventDate<=CURDATE() GROUP BY YEARWEEK(e.EventDate,1) ORDER BY yw ASC
");
$week_rating_labels=[]; $week_rating_data=[];
while($r=$week_rating_res->fetch_assoc()){ $week_rating_labels[]='Wk '.$r['wl']; $week_rating_data[]=$r['avg_rating']; }

// ── 5. Top 6 Events Leaderboard ──────────────────────────────────────
// Ranks events using a weighted score: 70% based on average rating, 30% on registration volume.
// This rewards both quality and popular reach.
$top_events_res = $conn->query("
    SELECT e.Title, e.Category, ROUND(AVG(f.Rating),1) as avg_f, COUNT(DISTINCT r.RegistrationID) as regs
    FROM events e
    LEFT JOIN feedback f ON e.EventID=f.EventID
    LEFT JOIN registrations r ON e.EventID=r.EventID
    WHERE e.EventDate<=CURDATE()
    GROUP BY e.EventID
    ORDER BY (IFNULL(AVG(f.Rating),0)*0.7 + COUNT(DISTINCT r.RegistrationID)*0.3) DESC
    LIMIT 6
");
$top_events=[];
while($r=$top_events_res->fetch_assoc()) $top_events[]=$r;

$pageTitle = 'Analytics Command Center - IU Events';
include 'includes/header.php';
?>

<div class="scroll-main">

    <!-- Page Title & Global Badge -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Quality <span class="title-accent">Analytics</span></h1>
            <p class="page-sub">Cumulative data across all events · up to <?=date('F j, Y')?></p>
        </div>
        <div class="alltime-badge"><i class="bi bi-infinity"></i> All Time</div>
    </div>

    <!-- KPI Strip (Summary Metrics) -->
    <div class="kpi-strip">
        <div class="kpi-item">
            <div class="kpi-icon ic-att"><i class="bi bi-person-check-fill"></i></div>
            <div class="kpi-info"><div class="kpi-val"><?=$attendance_rate?><span style="font-size:1rem; color:var(--text-muted); font-weight:600;">%</span></div><div class="kpi-label">Attendance Rate</div></div>
        </div>
        <div class="kpi-divider"></div>
        <div class="kpi-item">
            <div class="kpi-icon ic-sat"><i class="bi bi-star-fill"></i></div>
            <div class="kpi-info"><div class="kpi-val"><?=$avg_rating?><span style="font-size:1rem; color:var(--text-muted); font-weight:600;">/5</span></div><div class="kpi-label">Avg. Satisfaction</div></div>
        </div>
        <div class="kpi-divider"></div>
        <div class="kpi-item">
            <div class="kpi-icon ic-drp"><i class="bi bi-person-x-fill"></i></div>
            <div class="kpi-info"><div class="kpi-val"><?=$drop_off_rate?><span style="font-size:1rem; color:var(--text-muted); font-weight:600;">%</span></div><div class="kpi-label">Drop-off Rate</div></div>
        </div>
        <div class="kpi-divider"></div>
        <div class="kpi-item">
            <div class="kpi-icon ic-fbk"><i class="bi bi-chat-left-dots-fill"></i></div>
            <div class="kpi-info"><div class="kpi-val"><?=$feedback_rate?><span style="font-size:1rem; color:var(--text-muted); font-weight:600;">%</span></div><div class="kpi-label">Feedback Rate</div></div>
        </div>
    </div>

    <?php
        // Pre-calculate funnel percentages for the UI components below
        $pAtt = $total_regs > 0 ? round(($total_atts/$total_regs)*100) : 0;
        $pFbk = $total_regs > 0 ? round(($total_feedbacks/$total_regs)*100) : 0;
    ?>


    <!-- Hall Utilization Card -->
    <div class="a-card wide" style="height:340px;flex:unset;width:100%;">
        <p class="card-title"><i class="bi bi-building me-2" style="color:#60A5FA;"></i>Hall Utilization Rate</p>
        <p class="card-sub">Average capacity usage per hall (%)</p>
        <div class="chart-wrap"><canvas id="hallChart"></canvas></div>
    </div>

    <!-- Middle Row: Retention Funnel & Event Rankings -->
    <div class="charts-row">
        <!-- Retention Funnel (Registration -> Attendance -> Feedback) -->
        <div class="a-card auto" style="flex:1;">
            <p class="card-title"><i class="bi bi-funnel-fill me-2" style="color:#A78BFA;"></i>Retention Funnel</p>
            <p class="card-sub">Student journey from registration to feedback</p>
            <div class="funnel-wrap">
                <!-- Registered -->
                <div class="f-stage">
                    <i class="bi bi-person-plus-fill f-icon" style="color:#60A5FA;"></i>
                    <div class="f-info">
                        <div class="f-label">Registered</div>
                        <div class="f-count"><?=number_format($total_regs)?></div>
                    </div>
                    <div class="f-bar-wrap">
                        <div class="f-meta">
                            <span class="f-sublabel">Total enrolled students</span>
                            <span class="f-pct" style="color:#60A5FA;">100%</span>
                        </div>
                        <div class="f-track"><div class="f-fill reg" style="width:100%;"></div></div>
                    </div>
                </div>
                <!-- Attended -->
                <div class="f-stage">
                    <i class="bi bi-person-check-fill f-icon" style="color:#34D399;"></i>
                    <div class="f-info">
                        <div class="f-label">Attended</div>
                        <div class="f-count"><?=number_format($total_atts)?></div>
                    </div>
                    <div class="f-bar-wrap">
                        <div class="f-meta">
                            <span class="f-sublabel"><?=number_format($total_regs-$total_atts)?> did not attend</span>
                            <span class="f-pct" style="color:#34D399;"><?=$pAtt?>%</span>
                        </div>
                        <div class="f-track"><div class="f-fill att" style="width:<?=$pAtt?>%;"></div></div>
                    </div>
                </div>
                <!-- Feedback Submission -->
                <div class="f-stage">
                    <i class="bi bi-chat-left-text-fill f-icon" style="color:#FACC15;"></i>
                    <div class="f-info">
                        <div class="f-label">Feedback</div>
                        <div class="f-count"><?=number_format($total_feedbacks)?></div>
                    </div>
                    <div class="f-bar-wrap">
                        <div class="f-meta">
                            <span class="f-sublabel"><?=number_format($total_atts-$total_feedbacks)?> no response</span>
                            <span class="f-pct" style="color:#FACC15;"><?=$pFbk?>%</span>
                        </div>
                        <div class="f-track"><div class="f-fill fbk" style="width:<?=$pFbk?>%;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard: Ranked Success -->
        <div class="a-card auto" style="flex:1;">
            <p class="card-title"><i class="bi bi-trophy-fill me-2" style="color:var(--gold-primary);"></i>Top 6 Events</p>
            <p class="card-sub">Ranked by 70% satisfaction · 30% registrations</p>
            <div class="lb-list mt-2">
                <?php if(empty($top_events)): ?>
                    <div style="color:var(--text-muted);font-size:.85rem;">No data available.</div>
                <?php else: ?>
                    <?php foreach($top_events as $i=>$ev): $rc=($i<3)?'rank-'.($i+1):''; ?>
                    <div class="lb-item">
                        <div class="lb-rank <?=$rc?>">#<?=$i+1?></div>
                        <div class="lb-info">
                            <div class="lb-title"><?=htmlspecialchars($ev['Title'])?></div>
                            <div class="lb-meta"><?=htmlspecialchars($ev['Category'])?> &bull; <?=$ev['regs']?> regs</div>
                        </div>
                        <div class="lb-stars"><?=$ev['avg_f']?:'-'?> &#9733;</div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Satisfaction Evolution Chart -->
    <div class="a-card fill" style="height:340px;flex:unset;width:100%;">
        <p class="card-title"><i class="bi bi-star-half me-2" style="color:#A78BFA;"></i>Satisfaction Evolution</p>
        <p class="card-sub">Average student rating per week</p>
        <div class="chart-wrap"><canvas id="weekRatingChart"></canvas></div>
    </div>

</div>

<script>
    // ── Global Chart.js Defaults ─────────────────────────────────────
    Chart.defaults.color = '#94A3B8';
    Chart.defaults.font.family = 'Inter';
    Chart.defaults.font.size = 13;

    // Premium Tooltip Style Config
    const tStyle = {
        backgroundColor:'rgba(11,17,33,0.97)',titleColor:'#F8FAFC',titleFont:{family:'Outfit',size:13,weight:'bold'},
        bodyColor:'#CBD5E1',bodyFont:{size:12},borderColor:'rgba(255,255,255,0.12)',borderWidth:1,padding:12,usePointStyle:true
    };
    const gridColor = 'rgba(255,255,255,0.06)';

    // ── 1. Hall Utilization Bar Chart ────────────────────────────────
    // Uses dynamic color coding (Red/Blue/Green) based on utilization percentage.
    new Chart(document.getElementById('hallChart'), {
        type:'bar',
        data:{
            labels:<?=json_encode($hall_labels)?>,
            datasets:[{
                label:'Utilization %',data:<?=json_encode($hall_data)?>,
                backgroundColor:ctx=>{const v=ctx.raw;if(v<50)return'rgba(248,113,113,0.85)';if(v>85)return'rgba(52,211,153,0.85)';return'rgba(96,165,250,0.85)';},
                borderColor:ctx=>{const v=ctx.raw;if(v<50)return'#F87171';if(v>85)return'#34D399';return'#60A5FA';},
                borderWidth:1, borderRadius:8, barPercentage:0.55
            }]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},tooltip:{...tStyle,callbacks:{label:c=>` ${c.raw}% capacity used`}}},
            scales:{
                y:{beginAtZero:true,max:100,border:{display:false},
                   grid:{color:gridColor,drawTicks:false},
                   ticks:{padding:10,callback:v=>v+'%',font:{size:12},color:'#94A3B8'}},
                x:{border:{display:false},grid:{display:false},
                   ticks:{padding:6,maxRotation:40,minRotation:30,font:{size:11},color:'#94A3B8',
                          autoSkip:false,
                          callback:function(val,i){const l=this.getLabelForValue(val);return l.length>16?l.substring(0,16)+'…':l;}}}
            }
        }
    });

    // ── 2. Weekly Satisfaction Line Chart ────────────────────────────
    // Renders average student ratings as a smooth line with gradient area fill.
    new Chart(document.getElementById('weekRatingChart'), {
        type:'line',
        data:{
            labels:<?=json_encode($week_rating_labels)?>,
            datasets:[{
                label:'Avg Rating',data:<?=json_encode($week_rating_data)?>,
                borderColor:'#A78BFA',
                backgroundColor:ctx=>{const g=ctx.chart.ctx.createLinearGradient(0,0,0,ctx.chart.height);g.addColorStop(0,'rgba(167,139,250,0.4)');g.addColorStop(1,'rgba(167,139,250,0.03)');return g;},
                borderWidth:3,fill:true,tension:0.4,
                pointRadius:4,pointBackgroundColor:'#A78BFA',pointBorderColor:'#0B1121',pointBorderWidth:2,
                pointHoverRadius:7,pointHoverBackgroundColor:'#A78BFA',pointHoverBorderColor:'#fff',pointHoverBorderWidth:2
            }]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},tooltip:{...tStyle,callbacks:{label:c=>` ${c.raw} ★`}}},
            scales:{
                y:{min:1,max:5,border:{display:false},grid:{color:gridColor,drawTicks:false},
                   ticks:{padding:10,callback:v=>Number.isInteger(v)?v+' ★':'',font:{size:12},color:'#94A3B8',stepSize:1}},
                x:{border:{display:false},grid:{color:gridColor},
                   ticks:{padding:6,font:{size:10},color:'#94A3B8',
                          autoSkip:false,maxRotation:45,minRotation:30}}
            },
            interaction:{intersect:false,mode:'index'}
        }
    });
</script>

<?php include 'includes/footer.php'; ?>