<?php
// Include database connection
require_once 'config.php';

// Fetch events from database
$sql = "SELECT 
            e.EventID, 
            e.Title, 
            e.EventDate, 
            e.TimeToStart, 
            e.Description, 
            e.Category, 
            e.IsCertified, 
            h.Name AS HallName, 
            hst.Name AS HostName,
            h.Capacity AS HallCapacity,
            (SELECT COUNT(*) FROM registrations r WHERE r.EventID = e.EventID) AS RegisteredCount
        FROM events e
        LEFT JOIN halls h ON e.HallID = h.HallID
        LEFT JOIN hosts hst ON e.HostID = hst.HostID
        WHERE e.EventDate >= CURDATE()
        ORDER BY e.EventDate ASC";

$result = $conn->query($sql);

$events = [];

// temp until we adjust the database to have the images
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

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Determine image based on category
        $category = $row['Category'];
        $image = 'assets/images/event1.jpg'; // Default fallback

        if (isset($categoryImages[$category])) {
            $image = $categoryImages[$category];

        }

        $events[] = [
            'id' => $row['EventID'],
            'title' => $row['Title'],
            'date' => $row['EventDate'],
            'time' => date('h:i A', strtotime($row['TimeToStart'])), // Format time
            'hall' => $row['HallName'],
            'category' => $row['Category'],
            'host' => $row['HostName'],
            'image' => $image,
            'description' => $row['Description'],
            'seats_total' => $row['HallCapacity'],
            'seats_available' => $row['HallCapacity'] - $row['RegisteredCount'],
            'certificate' => (bool) $row['IsCertified']
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IU EVENTS - Islamic University Event Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/common.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/chatbot.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="bi bi-calendar-event"></i> IU EVENTS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#upcoming-events">Upcoming Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#events-by-hall">Events by Hall</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-login ms-lg-3" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

        <!-- MAIN LANDING AREA -->
    <section class="landing-header-section text-center">
        <div class="container">
            <h1 class="landing-title">Find Your Next Event</h1>
            <p class="landing-subtitle">Discover workshops, seminars, and activities at Islamic University</p>
        </div>
        <br>
        <div class="container">
            <div class="search-bar-wrap">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="globalSearchInput" class="form-control"
                        placeholder="Search for events, workshops ...">
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION 1: GLOBAL SEARCH DISCOVERY BAR -->
    


    <section>
        <div class="container">
            <h2 class="section-title text-center">Upcoming Events</h2>

            <!-- Category Filters -->
            <div class="category-filters hall-selector text-center mb-5">
                <button class="btn hall-btn active" data-category="all">
                    All Events
                </button>
                <?php
                $categoryIcons = [
                    'Orientation' => 'bi-compass',
                    'Seminar' => 'bi-mic',
                    'Workshop' => 'bi-tools',
                    'Lecture' => 'bi-journal-text',
                    'Training' => 'bi-person-workspace',
                    'Competition' => 'bi-trophy',
                    'Panel' => 'bi-people',
                    'Showcase' => 'bi-stars'
                ];

                $categoryColors = [
                    'Orientation' => '#0F4C75',
                    'Seminar' => '#0F4C75',
                    'Workshop' => '#0F4C75',
                    'Lecture' => '#0F4C75',
                    'Training' => '#0F4C75',
                    'Competition' => '#0F4C75',
                    'Panel' => '#0F4C75',
                    'Showcase' => '#0F4C75'
                ];

                // Get unique categories and generate filter buttons dynamically (same logic as halls)
                $categories = array_unique(array_column($events, 'category'));
                sort($categories);
                foreach ($categories as $category):
                    $icon = isset($categoryIcons[$category]) ? $categoryIcons[$category] : 'bi-tag';
                    $btnColor = isset($categoryColors[$category]) ? $categoryColors[$category] : 'var(--primary-color)';
                    ?>
                    <button class="btn hall-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                        <?php echo $category; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Event Cards Grid -->
            <div id="eventsGridContainer">
                <div id="eventsGrid">
                    <?php
                    $groupedEvents = [];
                    foreach ($events as $event) {
                        $groupedEvents[$event['date']][] = $event;
                    }
                    ksort($groupedEvents);

                    $dayCount = 0;
                    foreach ($groupedEvents as $dateKey => $dayEvents):
                        $dayCount++;
                        $dateObj = new DateTime($dateKey);
                        $fullDate = $dateObj->format('l, F d, Y');
                        $visibilityClass = $dayCount > 3 ? 'extra-day' : 'visible-day';
                        $displayStyle = $dayCount > 3 ? 'display: none;' : '';
                        ?>
                        <div class="day-group <?php echo $visibilityClass; ?>" style="<?php echo $displayStyle; ?>"
                            data-day-group="true">
                            <h3 class="mb-4 mt-5" style="color: var(--text-light);">
                                <i class="bi bi-calendar-event"></i> <?php echo $fullDate; ?>
                            </h3>
                            <div class="row g-4 day-cards-container">
                                <?php foreach ($dayEvents as $event):
                                    $dateObj = new DateTime($event['date']);
                                    $day = $dateObj->format('d');
                                    $month = $dateObj->format('M');
                                    $fullDateStr = $dateObj->format('l, F d, Y');
                                    $perc = $event['seats_total'] > 0 ? (100 - ($event['seats_available'] / $event['seats_total']) * 100) : 0;

                                    $seatsAvailable = $event['seats_available'];
                                    $seatsTotal = $event['seats_total'];
                                    $seatPerc = $seatsTotal > 0 ? ($seatsAvailable / $seatsTotal) * 100 : 0;

                                    $seatColorClass = 'bg-primary';
                                    if ($seatsAvailable <= 0) {
                                        $seatColorClass = 'bg-secondary';
                                    } elseif ($seatPerc < 20) {
                                        $seatColorClass = 'bg-danger';
                                    } elseif ($seatPerc <= 50) {
                                        $seatColorClass = 'bg-warning text-dark';
                                    } else {
                                        $seatColorClass = 'bg-success';
                                    }
                                    ?>
                                    <div class="col-lg-4 col-md-6 event-card-wrapper"
                                        data-category="<?php echo $event['category']; ?>"
                                        data-title="<?php echo strtolower($event['title']); ?>"
                                        data-hall="<?php echo strtolower($event['hall']); ?>">
                                        <div class="event-card" style="cursor: pointer;" data-id="<?php echo $event['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                            data-category="<?php echo $event['category']; ?>"
                                            data-description="<?php echo htmlspecialchars($event['description']); ?>">
                                            <div class="event-image">
                                                <img src="<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>">
                                                <div class="date-badge">
                                                    <span class="day"><?php echo $day; ?></span>
                                                    <span class="month"><?php echo strtoupper($month); ?></span>
                                                </div>
                                            </div>
                                            <div class="event-body">
                                                <?php
                                                $catColor = isset($categoryColors[$event['category']]) ? $categoryColors[$event['category']] : 'var(--primary-color)';
                                                ?>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="category-label mb-0"
                                                        style="background-color: <?php echo $catColor; ?>; border-radius: 4px; padding: 0.2rem 0.6rem; font-size: 0.8rem;"><?php echo $event['category']; ?></span>
                                                    <?php if ($event['certificate']): ?>
                                                        <span class="badge text-dark d-flex align-items-center"
                                                            style="background: linear-gradient(135deg, #FFD700 0%, #D4AF37 100%); font-size: 0.75rem;"><i
                                                                class="bi bi-award-fill me-1"></i> Certified</span>
                                                    <?php endif; ?>
                                                </div>
                                                <h3 class="event-title"><?php echo $event['title']; ?></h3>
                                                <div class="event-meta">
                                                    <i class="bi bi-clock"></i> <?php echo $event['time']; ?> |
                                                    <i class="bi bi-geo-alt"></i> <?php echo $event['hall']; ?>
                                                </div>
                                                <div class="event-host">
                                                    <i class="bi bi-person"></i> Host: <?php echo $event['host']; ?>
                                                </div>
                                                <div class="event-seats mt-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-muted small"><i class="bi bi-people"></i> Seats</span>
                                                        <span
                                                            class="badge <?php echo $seatColorClass; ?> rounded-pill"><?php echo $event['seats_available']; ?>
                                                            left from <?php echo $event['seats_total']; ?></span>
                                                    </div>
                                                    <div class="progress"
                                                        style="height: 6px; background-color: rgba(255,255,255,0.1);">
                                                        <div class="progress-bar <?php echo $seatColorClass; ?>"
                                                            role="progressbar" style="width: <?php echo $perc; ?>%"></div>
                                                    </div>
                                                </div>
                                                <div class="event-description mt-3 pt-3 border-top"
                                                    style="font-size: 0.9rem; color: var(--text-light);">
                                                    <h6 class="text-white mb-2">About This Event</h6>
                                                    <?php echo htmlspecialchars($event['description']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <hr style="border-color: rgba(255,255,255,0.1); margin-top: 3rem; margin-bottom: 0;">
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($dayCount > 3): ?>
                    <div class="text-center mt-5" id="showMoreContainer">
                        <button id="showLessDaysBtn" class="btn btn-outline-secondary px-4 py-2 me-2"
                            style="border-radius: 30px; display: none;">Go Back</button>
                        <button id="showMoreDaysBtn" class="btn btn-outline-primary px-4 py-2"
                            style="border-radius: 30px;">View Next 5 Days</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>





    <!-- Upcoming Events Section -->
    <section id="upcoming-events" class="events-section">
        
    </section>

    <!-- Events by Hall Section -->
    <section id="events-by-hall" class="hall-section">
        <div class="container">
            <h2 class="section-title text-center">Events by Hall</h2>
            <p class="text-center mb-4" style="color: var(--text-light); font-size: 1.1rem;">Select a hall to view its
                weekly timetable</p>

            <!-- Hall Selector -->
            <div class="hall-selector text-center mb-5">
                <?php
                // Get unique halls
                $halls = array_unique(array_column($events, 'hall'));
                sort($halls);
                foreach ($halls as $index => $hall):
                    ?>
                    <button class="btn hall-btn <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-hall="<?php echo htmlspecialchars($hall); ?>">
                        <i class="bi bi-building"></i> <?php echo $hall; ?>
                    </button>
                    <?php
                endforeach; ?>
            </div>

            <!-- Timetable Container -->
            <div class="timetable-container">
                <!-- Empty State -->
                <div class="empty-state text-center" style="display: none;">
                    <i class="bi bi-calendar-x"></i>
                    <h4>No Events Scheduled</h4>
                    <p>This hall has no events scheduled for this week.</p>
                </div>

                <!-- Timetable Grid -->
                <div class="timetable-wrapper">
                    <div class="table-responsive">
                        <table class="timetable-grid">
                            <thead>
                                <tr>
                                    <th class="time-column">Time</th>
                                    <?php
                                    // Set week starting from today
                                    $startOfWeek = new DateTime('today');

                                    for ($i = 0; $i < 7; $i++):
                                        $currentDay = clone $startOfWeek;
                                        $currentDay->modify('+' . $i . ' days');
                                        $dayName = $currentDay->format('l');
                                        $dayDate = $currentDay->format('M d');
                                        ?>
                                        <th data-day="<?php echo $dayName; ?>">
                                            <div><?php echo $dayName; ?></div>
                                            <small style="font-weight: 400; opacity: 0.8;"><?php echo $dayDate; ?></small>
                                        </th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody id="timetableBody">
                                <?php
                                // Define time slots
                                $timeSlots = [
                                    '08:00 AM',
                                    '09:00 AM',
                                    '10:00 AM',
                                    '11:00 AM',
                                    '12:00 PM',
                                    '01:00 PM',
                                    '02:00 PM',
                                    '03:00 PM',
                                    '04:00 PM',
                                    '05:00 PM',
                                    '06:00 PM'
                                ];

                                foreach ($timeSlots as $time):
                                    ?>
                                    <tr data-time="<?php echo $time; ?>">
                                        <td class="time-slot"><?php echo $time; ?></td>
                                        <?php for ($i = 0; $i < 7; $i++):
                                            $currentDay = clone $startOfWeek;
                                            $currentDay->modify('+' . $i . ' days');
                                            $dayName = $currentDay->format('l');
                                            ?>
                                            <td class="timetable-cell" data-day="<?php echo $dayName; ?>"
                                                data-date="<?php echo $currentDay->format('Y-m-d'); ?>"></td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Events Data (Hidden, for JavaScript) -->
            <script>
                const hallEventsData = <?php echo json_encode($events); ?>;
                const dynamicCategoryColors = <?php echo json_encode($categoryColors); ?>;
            </script>
        </div>
    </section>



    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-calendar-event"></i> IU EVENTS</h5>
                    <p>Connecting the Islamic University community through meaningful events.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 Islamic University. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS / Inline App Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- 1. SEARCH & CATEGORY FILTERING LOGIC ---
            const filterBtns = document.querySelectorAll('.category-filters .hall-btn');
            const megaCatCards = document.querySelectorAll('.mega-cat-card');
            const dayGroups = document.querySelectorAll('.day-group');
            const globalSearch = document.getElementById('globalSearchInput');

            let currentVisibleDays = 3;
            const totalDays = dayGroups.length;

            const showMoreBtn = document.getElementById('showMoreDaysBtn');
            const showLessBtn = document.getElementById('showLessDaysBtn');

            const updateVisibility = () => {
                const activeBtn = document.querySelector('.category-filters .hall-btn.active');
                const activeCategory = activeBtn ? activeBtn.getAttribute('data-category') : 'all';
                const searchTerm = globalSearch.value.toLowerCase().trim();

                let totalVisibleCards = 0;

                dayGroups.forEach((group, index) => {
                    let groupVisibleCards = 0;
                    const cards = group.querySelectorAll('.event-card-wrapper');

                    cards.forEach(card => {
                        const cardCat = card.getAttribute('data-category');
                        const cardTitle = card.getAttribute('data-title') || '';
                        const cardHall = card.getAttribute('data-hall') || '';

                        const matchesCategory = (activeCategory === 'all' || cardCat === activeCategory || cardCat.toLowerCase() === activeCategory.toLowerCase());
                        const matchesSearch = (searchTerm === '' || cardTitle.includes(searchTerm) || cardHall.includes(searchTerm));

                        if (matchesCategory && matchesSearch) {
                            card.style.display = 'block';
                            groupVisibleCards++;
                            totalVisibleCards++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (groupVisibleCards === 0) {
                        group.style.display = 'none';
                    } else {
                        // Only apply "Show More" limiting if no filters are active
                        if (activeCategory === 'all' && searchTerm === '') {
                            if (index < currentVisibleDays) {
                                group.style.display = 'block';
                            } else {
                                group.style.display = 'none';
                            }
                        } else {
                            group.style.display = 'block';
                        }
                    }
                });

                // Handle pagination buttons
                const btnContainer = document.getElementById('showMoreContainer');
                if (btnContainer) {
                    if (activeCategory === 'all' && searchTerm === '') {
                        btnContainer.style.display = 'block';
                        if (showLessBtn) showLessBtn.style.display = currentVisibleDays > 3 ? 'inline-block' : 'none';
                        if (showMoreBtn) showMoreBtn.style.display = currentVisibleDays < totalDays ? 'inline-block' : 'none';
                    } else {
                        btnContainer.style.display = 'none';
                    }
                }

                // Handle empty state
                let noEventsMsg = document.getElementById('no-events-msg');
                if (totalVisibleCards === 0) {
                    if (!noEventsMsg) {
                        noEventsMsg = document.createElement('div');
                        noEventsMsg.id = 'no-events-msg';
                        noEventsMsg.className = 'col-12 text-center text-muted py-5';
                        noEventsMsg.style.background = 'rgba(255,255,255,0.02)';
                        noEventsMsg.style.borderRadius = '20px';
                        noEventsMsg.innerHTML = '<i class="bi bi-calendar-x fs-1"></i><h4 class="mt-3">No events match your search</h4><p>Try different keywords or categories.</p>';
                        document.getElementById('eventsGrid').appendChild(noEventsMsg);
                    }
                    noEventsMsg.style.display = 'block';
                } else if (noEventsMsg) {
                    noEventsMsg.style.display = 'none';
                }
            };

            // Event Listeners for Filters
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    updateVisibility();
                });
            });

            // Event Listeners for Mega Cards
            megaCatCards.forEach(card => {
                card.addEventListener('click', function () {
                    const cat = this.getAttribute('data-category');
                    const targetBtn = document.querySelector(`.category-filters .hall-btn[data-category="${cat}"]`);
                    if (targetBtn) {
                        filterBtns.forEach(b => b.classList.remove('active'));
                        targetBtn.classList.add('active');
                        updateVisibility();
                        // Scroll to events smoothly
                        document.getElementById('upcoming-events').scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Event Listener for Search
            if (globalSearch) {
                globalSearch.addEventListener('input', updateVisibility);
            }

            // Pagination Buttons
            if (showMoreBtn) {
                showMoreBtn.addEventListener('click', () => { currentVisibleDays += 5; updateVisibility(); });
            }
            if (showLessBtn) {
                showLessBtn.addEventListener('click', () => { currentVisibleDays = 3; updateVisibility(); });
            }

            // --- 2. HALL TIMETABLE LOGIC ---
            const hallBtns = document.querySelectorAll('.hall-selector .hall-btn:not(.category-filters .hall-btn)');
            if (hallBtns.length > 0 && typeof hallEventsData !== 'undefined') {
                const renderTimetable = (hallName) => {
                    const events = hallEventsData.filter(e => e.hall === hallName);
                    document.querySelectorAll('.timetable-cell').forEach(cell => { cell.innerHTML = ''; cell.classList.remove('has-event'); });

                    let hasEvents = false;
                    events.forEach(event => {
                        const row = document.querySelector(`tr[data-time="${event.time}"]`);
                        if (row) {
                            const cellDate = row.querySelector(`.timetable-cell[data-date="${event.date}"]`);
                            if (cellDate) {
                                const brColor = dynamicCategoryColors[event.category] || '#10b981';
                                const bgGradient = `linear-gradient(135deg, ${brColor}26 0%, rgba(30, 41, 59, 0.95) 100%)`;
                                cellDate.innerHTML = `
                                <div class="p-2 rounded h-100 d-flex flex-column table-cell-event" style="background: ${bgGradient}; border-left: 4px solid ${brColor}; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                    <div class="fw-bold mb-1" style="font-size: 0.9rem; color: #fff; line-height: 1.2;">${event.title}</div>
                                    <div style="color: #e2e8f0; font-size: 0.75rem;"><i class="bi bi-clock" style="color: ${brColor};"></i> ${event.time}</div>
                                </div>`;
                                cellDate.classList.add('has-event');
                                hasEvents = true;
                            }
                        }
                    });

                    const emptyState = document.querySelector('.empty-state');
                    const wrapper = document.querySelector('.timetable-wrapper');
                    if (emptyState && wrapper) {
                        emptyState.style.display = hasEvents ? 'none' : 'block';
                        wrapper.style.display = hasEvents ? 'block' : 'none';
                    }
                };

                hallBtns.forEach(btn => {
                    btn.addEventListener('click', function () {
                        hallBtns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        renderTimetable(this.getAttribute('data-hall'));
                    });
                });

                const initialHall = document.querySelector('.hall-selector .hall-btn.active:not(.category-filters .hall-btn)');
                if (initialHall) renderTimetable(initialHall.getAttribute('data-hall'));
            }
        });
    </script>
    
    <!-- Gemini Chatbot Widget -->
    <?php include 'includes/chatbot.php'; ?>
</body>

</html>