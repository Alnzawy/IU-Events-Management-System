<?php
/**
 * event_wizard_helper.php - Unified Event Management Wizard.
 * This file serves as a multi-step form (wizard) for both creating 
 * and editing events. It includes complex server-side conflict detection 
 * for halls and hosts.
 */
require_once '../config.php';
checkRole('organizer');

// ── 1. Initialization ───────────────────────────────────────────────
// Determine if we are in 'Edit' mode based on the presence of an ID.
$event_id = intval($_GET['id'] ?? 0);
$is_edit = ($event_id > 0);
$event_data = null;
$error = '';

if ($is_edit) {
    // Fetch existing event details if editing.
    $stmt = $conn->prepare("SELECT * FROM events WHERE EventID = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$event_data) {
        // Redirect back if the event ID is invalid.
        header("Location: events_manage.php");
        exit();
    }
}

// ── 2. AJAX Actions (Internal API) ──────────────────────────────────
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    // Action: Get the next available Event ID (for display purposes).
    if ($_GET['action'] == 'get_next_id') {
        $res = $conn->query("SELECT MAX(EventID) as last_id FROM events");
        $row = $res->fetch_assoc();
        $next_id = ($row['last_id'] ?? 0) + 1;
        echo json_encode(['next_id' => $next_id]);
        exit();
    }

    // Action: Check for scheduling conflicts before allowing selection.
    // Verifies if a specific hall or host is already booked for a given date/time.
    if ($_GET['action'] == 'check_conflicts') {
        $date = $_GET['date'] ?? '';
        $hall_id = intval($_GET['hall_id'] ?? 0);
        $host_id = intval($_GET['host_id'] ?? 0);

        $sql = "SELECT TimeToStart, TimeToEnd, HallID, HostID 
                FROM events 
                WHERE EventDate = ? 
                AND (HallID = ? OR HostID = ?) 
                AND EventID != ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $date, $hall_id, $host_id, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $conflicts = [];
        while ($row = $result->fetch_assoc()) {
            $conflicts[] = [
                'start' => $row['TimeToStart'],
                'end'   => $row['TimeToEnd']
            ];
        }

        echo json_encode(['conflicts' => $conflicts]);
        exit;
    }
}

// ── 3. Form Submission (POST Handler) ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $event_date = $_POST['event_date'];
    $time_start = $_POST['time_start'];
    $time_end = $_POST['time_end'];
    $description = htmlspecialchars($_POST['description']);
    $category = $_POST['category'];
    $is_certified = isset($_POST['is_certified']) ? 1 : 0;
    $hall_id = intval($_POST['hall_id']);
    $host_id = intval($_POST['host_id']);

    // Perform a final safety check for conflicts before database insertion.
    $conflict_query = "SELECT COUNT(*) as count FROM events 
                       WHERE (HallID = ? OR HostID = ?) 
                       AND EventDate = ? 
                       AND (TimeToStart < ? AND TimeToEnd > ?) 
                       AND EventID != ?";

    $stmt = $conn->prepare($conflict_query);
    $stmt->bind_param("iisssi", $hall_id, $host_id, $event_date, $time_end, $time_start, $event_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result['count'] > 0) {
        $error = "Conflict detected! The Hall or Host is already booked for this time.";
    } else {
        if ($is_edit) {
            // Logic for Update
            $stmt = $conn->prepare("
                UPDATE events 
                SET Title=?, EventDate=?, TimeToStart=?, TimeToEnd=?, 
                    Description=?, Category=?, HallID=?, HostID=?, IsCertified=? 
                WHERE EventID=?
            ");
            $stmt->bind_param("sssssssiii", $title, $event_date, $time_start, $time_end, $description, $category, $hall_id, $host_id, $is_certified, $event_id);
        } else {
            // Logic for Insert
            $res = $conn->query("SELECT MAX(EventID) as last_id FROM events");
            $row = $res->fetch_assoc();
            $new_id = ($row['last_id'] ?? 0) + 1;

            $stmt = $conn->prepare("
                INSERT INTO events 
                (EventID, Title, EventDate, TimeToStart, TimeToEnd, Description, Category, HallID, HostID, IsCertified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssssssii", $new_id, $title, $event_date, $time_start, $time_end, $description, $category, $hall_id, $host_id, $is_certified);
        }

        if ($stmt->execute()) {
            $_SESSION['flash_success'] = $is_edit ? "Event updated successfully!" : "Event created successfully!";
            header("Location: events_manage.php");
            exit();
        } else {
            $error = "Error saving event: " . $conn->error;
        }
        $stmt->close();
    }
}

// Pre-fetch resource lists for the wizard steps.
$halls_result = $conn->query("SELECT HallID, Name, Capacity FROM halls");
$hosts_result = $conn->query("SELECT HostID, Name FROM hosts");

$pageTitle = ($is_edit ? 'Edit Event' : 'Add New Event') . ' - IU Events';
include 'includes/header.php';
?>


<div class="scroll-main">
    <div class="container-fluid p-0">
        <!-- Header -->
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom border-secondary">
            <h1 class="h2 text-white"><?= $is_edit ? 'Edit Event' : 'Add New Event' ?></h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="events_manage.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Events
                </a>
            </div>
        </div>

        <!-- Toast Notification Container (for validation errors) -->
        <div class="toast-container" id="toastContainer">
            <?php if ($error): ?>
                <div class="toast-notification toast-error" id="errorToast">
                    <i class="bi bi-exclamation-triangle-fill toast-icon"></i>
                    <div class="toast-content"><?= $error; ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Wizard Container -->
        <div class="card bg-dark border border-secondary text-white shadow-lg mx-auto" style="max-width: 800px; border-radius: 16px;">
            <div class="card-body p-4">
                <form method="POST" id="eventForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="event_date" id="hidden_date" value="<?= $event_data['EventDate'] ?? '' ?>">
                    <input type="hidden" name="time_start" id="hidden_start" value="<?= $event_data['TimeToStart'] ?? '' ?>">
                    <input type="hidden" name="time_end" id="hidden_end" value="<?= $event_data['TimeToEnd'] ?? '' ?>">
                    <input type="hidden" name="hall_id" id="hidden_hall" value="<?= $event_data['HallID'] ?? '' ?>">
                    <input type="hidden" name="host_id" id="hidden_host" value="<?= $event_data['HostID'] ?? '' ?>">

                    <!-- Progress Indicator -->
                    <div class="d-flex justify-content-between mb-4 align-items-center">
                        <span class="text-muted small" id="step-indicator">Step 1 of 5</span>
                        <span class="text-info fw-bold h5 mb-0" id="step-title">Event Details</span>
                        <div class="progress" style="width: 100px; height: 6px;">
                            <div class="progress-bar bg-info" id="step-progress" role="progressbar" style="width: 20%"></div>
                        </div>
                    </div>

                    <!-- Step 1: Info -->
                    <div class="wizard-step active" id="step-1">
                        <div class="mb-3">
                            <label class="form-label text-white-50">Event ID</label>
                            <input type="text" class="form-control" id="display_id" readonly value="<?= $is_edit ? '#'.$event_id : '' ?>"
                                   style="background-color: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); color: #94a3b8; max-width: 100%;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Event Title</label>
                            <input type="text" class="form-control" name="title" id="title" required value="<?= htmlspecialchars($event_data['Title'] ?? '') ?>"
                                   style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; max-width: 100%;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Category</label>
                            <select class="form-select" name="category" id="category" required
                                    style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff;">
                                <?php 
                                $cats = ['Lecture', 'Workshop', 'Seminar', 'Training', 'Orientation', 'Showcase', 'Panel'];
                                foreach($cats as $cat) {
                                    $sel = ($event_data && $event_data['Category'] == $cat) ? 'selected' : '';
                                    echo "<option value='$cat' $sel>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"
                                      style="background-color: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #fff; max-width: 100%;"><?= htmlspecialchars($event_data['Description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <div class="form-check form-switch custom-switch">
                                <input class="form-check-input" type="checkbox" name="is_certified" id="is_certified" value="1" <?= ($event_data && $event_data['IsCertified']) ? 'checked' : '' ?>>
                                <label class="form-check-label ms-2 text-white-50" for="is_certified">
                                    <i class="bi bi-award me-1 text-warning"></i> Certificated Event?
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Calendar -->
                    <div class="wizard-step" id="step-2" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-white" id="prev-month"><i class="bi bi-chevron-left"></i></button>
                            <h6 class="mb-0 text-white" id="calendar-month-year"></h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-white" id="next-month"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid mb-1">
                            <div class="calendar-day-header">Sun</div><div class="calendar-day-header">Mon</div><div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div><div class="calendar-day-header">Thu</div><div class="calendar-day-header">Fri</div><div class="calendar-day-header">Sat</div>
                        </div>
                        <div class="calendar-grid" id="calendar-days"></div>
                        <div class="mt-3 text-center">
                            <small class="text-info" id="selected-date-display"><?= $is_edit ? date('F j, Y', strtotime($event_data['EventDate'])) : 'Select a date' ?></small>
                        </div>
                    </div>

                    <!-- Step 3: Hall -->
                    <div class="wizard-step" id="step-3" style="display:none;">
                        <label class="form-label text-white-50 mb-3">Select Hall</label>
                        <div class="list-group" id="hall-list">
                            <?php while($hall = $halls_result->fetch_assoc()): ?>
                                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border-secondary hall-option <?= ($event_data && $event_data['HallID'] == $hall['HallID']) ? 'active border-primary' : '' ?>" data-value="<?= $hall['HallID'] ?>">
                                    <?= $hall['Name'] ?> <span class="badge bg-secondary float-end">Cap: <?= $hall['Capacity'] ?></span>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Step 4: Host -->
                    <div class="wizard-step" id="step-4" style="display:none;">
                        <label class="form-label text-white-50 mb-3">Select Host</label>
                        <div class="list-group" id="host-list">
                            <?php while($host = $hosts_result->fetch_assoc()): ?>
                                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border-secondary host-option <?= ($event_data && $event_data['HostID'] == $host['HostID']) ? 'active border-primary' : '' ?>" data-value="<?= $host['HostID'] ?>">
                                    <?= $host['Name'] ?>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Step 5: Schedule -->
                    <div class="wizard-step" id="step-5" style="display:none;">
                        <div class="alert alert-info py-2 small mb-3"><i class="bi bi-info-circle me-1"></i> Select a starting hour (Duration: 1 Hour)</div>
                        <div class="time-grid" id="time-slots"></div>
                        <div class="mt-3 small text-muted text-center">
                            <span class="d-inline-block p-1 bg-danger opacity-50 rounded me-1" style="width:10px;height:10px;"></span> Booked
                            <span class="d-inline-block p-1 bg-primary opacity-50 rounded ms-2 me-1" style="width:10px;height:10px;"></span> Selected
                        </div>
                    </div>

                    <!-- Controls -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary">
                        <button type="button" class="btn btn-outline-light" id="btn-back" style="display:none;">Back</button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary px-4" id="btn-next">Next</button>
                            <button type="submit" class="btn btn-success px-4" id="btn-submit" style="display:none;">Confirm & Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * EventWizard - Front-end state machine for the multi-step event form.
 * Manages calendar rendering, hall/host selection, and time-slot conflict highlighting.
 */
class EventWizard {
    constructor(config) {
        // Core configuration and initial state.
        this.config = {
            step: 1,
            date: config.initialData?.date || null,
            hall: config.initialData?.hall || null,
            host: config.initialData?.host || null,
            startTime: config.initialData?.startTime || null,
            id: config.id || 0,
            isEdit: config.isEdit || false
        };

        // Initialize calendar to the current or selected month.
        this.currentMonth = this.config.date 
            ? new Date(this.config.date) 
            : new Date();

        this.init();
    }

    /**
     * Set up event listeners and initial UI state.
     */
    init() {
        document.getElementById('btn-next').onclick = () => this.next();
        document.getElementById('btn-back').onclick = () => this.back();

        // Hall and Host selection handlers.
        document.querySelectorAll('.hall-option, .host-option').forEach(btn => {
            btn.onclick = (e) => {
                const t = e.currentTarget;
                const isHall = t.classList.contains('hall-option');

                document.querySelectorAll(isHall ? '.hall-option' : '.host-option')
                    .forEach(b => b.classList.remove('active', 'border-primary'));

                t.classList.add('active', 'border-primary');

                // Sync with hidden inputs for POST submission.
                document.getElementById(isHall ? 'hidden_hall' : 'hidden_host').value = t.dataset.value;
                this.config[isHall ? 'hall' : 'host'] = t.dataset.value;
            };
        });

        // Calendar navigation.
        document.getElementById('prev-month').onclick = () => {
            this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
            this.renderCalendar();
        };

        document.getElementById('next-month').onclick = () => {
            this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
            this.renderCalendar();
        };

        this.renderCalendar();
        this.showStep(1);

        if (!this.config.isEdit) 
            this.fetchNextID();
    }

    /**
     * Fetches the predicted Next Event ID for a professional UI experience.
     */
    fetchNextID() {
        fetch('?action=get_next_id')
            .then(r => r.json())
            .then(data => {
                document.getElementById('display_id').value = '#' + data.next_id;
            });
    }

    /**
     * Manages step visibility, progress bar, and title updates.
     */
    showStep(n) {
        this.config.step = n;

        document.querySelectorAll('.wizard-step').forEach((el, i) => {
            el.style.display = ((i + 1) === n) ? 'block' : 'none';
        });

        const progress = (n / 5) * 100;
        document.getElementById('step-progress').style.width = progress + '%';
        document.getElementById('step-indicator').innerText = `Step ${n} of 5`;

        const titles = ['Event Details', 'Select date', 'Select Hall', 'Select Host', 'Select Time'];
        document.getElementById('step-title').innerText = titles[n - 1];

        // Toggle control button visibility based on step.
        document.getElementById('btn-back').style.display = (n > 1) ? 'block' : 'none';
        document.getElementById('btn-next').style.display = (n < 5) ? 'block' : 'none';
        document.getElementById('btn-submit').style.display = (n === 5) ? 'block' : 'none';
    }

    /**
     * Logic for advancing to the next step with basic validation.
     */
    next() {
        const s = this.config.step;

        if (s === 1 && !document.getElementById('title').value)
            return alert('Title is required');
        if (s === 2 && !this.config.date)
            return alert('Select a date');
        if (s === 3 && !this.config.hall)
            return alert('Select a hall');
        if (s === 4 && !this.config.host)
            return alert('Select a host');

        if (s < 5)
            this.showStep(s + 1);

        // Load available time slots when entering Step 5.
        if (this.config.step === 5)
            this.loadSlots();
    }

    back() {
        if (this.config.step > 1)
            this.showStep(this.config.step - 1);
    }

    /**
     * Renders a custom premium calendar for date selection.
     * Prevents selecting past dates.
     */
    renderCalendar() {
        const m = this.currentMonth.getMonth(),
              y = this.currentMonth.getFullYear();

        const first = new Date(y, m, 1).getDay(),
              days  = new Date(y, m + 1, 0).getDate();

        document.getElementById('calendar-month-year')
            .innerText = this.currentMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

        const grid = document.getElementById('calendar-days');
        grid.innerHTML = '';

        // Padding for empty days at the start of the month.
        for (let i = 0; i < first; i++)
            grid.innerHTML += '<div></div>';

        for (let d = 1; d <= days; d++) {
            const dayStr = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const div = document.createElement('div');

            div.className = 'calendar-day text-white' + (this.config.date === dayStr ? ' selected' : '');

            const today = new Date();
            today.setHours(0,0,0,0);

            // Disable past dates unless we are editing an event that occurred in the past.
            if (new Date(dayStr) < today && this.config.date !== dayStr)
                div.classList.add('disabled');

            div.innerText = d;

            div.onclick = () => {
                if (div.classList.contains('disabled')) return;
                this.config.date = dayStr;
                document.getElementById('hidden_date').value = dayStr;
                this.renderCalendar();
                document.getElementById('selected-date-display').innerText = new Date(dayStr).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            };

            grid.appendChild(div);
        }
    }

    /**
     * Communicates with the internal API to find and disable booked time slots.
     */
    loadSlots() {
        const grid = document.getElementById('time-slots');
        grid.innerHTML = '<div class="text-white text-center">Loading...</div>';

        fetch(`?action=check_conflicts&date=${this.config.date}&hall_id=${this.config.hall}&host_id=${this.config.host}`)
            .then(r => r.json())
            .then(data => {
                grid.innerHTML = '';

                for (let h = 8; h <= 21; h++) {
                    const start = `${String(h).padStart(2,'0')}:00:00`,
                        end   = `${String(h+1).padStart(2,'0')}:00:00`;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'time-cell text-white';
                    btn.innerText = `${h}:00 - ${h+1}:00`;

                    // Disable button if a conflict is returned by the server.
                    if (data.conflicts.some(c => start < c.end && end > c.start))
                        btn.disabled = true;

                    if (h === parseInt(this.config.startTime))
                        btn.classList.add('selected');

                    btn.onclick = () => {
                        document.querySelectorAll('.time-cell').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        
                        document.getElementById('hidden_start').value = start;
                        document.getElementById('hidden_end').value = end;
                        this.config.startTime = h;
                    };

                    grid.appendChild(btn);
                }
            });
    }
}


document.addEventListener('DOMContentLoaded', () => {
    new EventWizard({
        id: <?= $event_id ?>,
        isEdit: <?= $is_edit ? 'true' : 'false' ?>,
        initialData: {
            date: '<?= $event_data['EventDate'] ?? '' ?>',
            hall: '<?= $event_data['HallID'] ?? '' ?>',
            host: '<?= $event_data['HostID'] ?? '' ?>',
            startTime: '<?= isset($event_data['TimeToStart']) ? intval(date('H', strtotime($event_data['TimeToStart']))) : '' ?>'
        }
    });

    const errorToast = document.getElementById('errorToast');
    if (errorToast) {
        setTimeout(() => {
            errorToast.style.opacity = '0';
            setTimeout(() => errorToast.remove(), 300);
        }, 5000);
    }
});
</script>



<?php include 'includes/footer.php'; ?>
