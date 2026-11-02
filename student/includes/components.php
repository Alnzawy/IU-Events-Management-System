<?php
// student/includes/components.php
// Reusable UI components for the student portal

/**
 * Renders a data table for events (used in index.php)
 */
function renderEventsTable($eventsList, $showActions = true) {
    if (empty($eventsList)) {
        echo '<div class="card card-custom p-4 text-center border-0"><p class="text-white-50 mb-0">No events in this category.</p></div>';
        return;
    }
    ?>
    <div class="card card-custom border-0 overflow-hidden">
        <div class="table-container p-0">
            <table class="table-modern mb-0 w-100">
                <thead>
                    <tr>
                        <th>Event Details</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <?php if ($showActions): ?>
                            <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($eventsList as $ev):
                    $isAttended = isset($ev['IsAttended']) && $ev['IsAttended'] == 1;
                    $isUpcoming = strtotime($ev['EventDate']) >= strtotime('today');

                    $state = $isAttended ? 'attended' : ($isUpcoming ? 'upcoming' : 'missed');
                    $label = $isAttended ? 'Attended'  : ($isUpcoming ? 'Upcoming'  : 'Missed');

                    $cap      = max(1, (int)$ev['Capacity']);
                    $regCount = (int)$ev['RegCount'];
                ?>
                    <tr>
                        <!-- Event Details -->
                        <td>
                            <div class="event-title mb-2"><?php echo htmlspecialchars($ev['Title']); ?></div>
                            <span class="category-badge me-1"><?php echo htmlspecialchars($ev['Category']); ?></span>
                            <?php if ($ev['IsCertified']): ?>
                                <span class="badge" style="background: rgba(212,175,55,0.1); color: #D4AF37; border: 1px solid rgba(212,175,55,0.25); font-weight: 600;">
                                    <i class="bi bi-award-fill me-1"></i>Certified
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Date & Time -->
                        <td>
                            <div class="date-wrapper text-white">
                                <i class="bi bi-calendar3 date-icon"></i>
                                <div>
                                    <div class="small fw-bold">
                                        <?php echo date('D, M d Y', strtotime($ev['EventDate'])); ?>
                                    </div>
                                    <small class="time-text">
                                        <?php echo date('h:i A', strtotime($ev['TimeToStart'])); ?>
                                    </small>
                                </div>
                            </div>
                        </td>

                        <!-- Location -->
                        <td>
                            <div class="d-flex align-items-center text-white-50">
                                <i class="bi bi-geo-alt me-2 text-gold"></i>
                                <?php echo htmlspecialchars($ev['HallName'] ?? 'TBD'); ?>
                            </div>
                        </td>

                        <!-- Status -->
                        <td>
                            <div class="mb-1">
                                <span class="st-badge <?php echo $state; ?>">
                                    <span class="st-dot"></span>
                                    <?php echo $label; ?>
                                </span>
                            </div>
                            <small class="text-white-50"><?php echo $regCount; ?> / <?php echo $cap; ?> registered</small>
                        </td>

                        <!-- Actions -->
                        <?php if ($showActions): ?>
                        <td class="text-end pe-4">
                            <?php if ($state === 'upcoming'): ?>
                                <form method="POST" onsubmit="return confirm('Cancel your registration for this event?')">
                                    <input type="hidden" name="event_id" value="<?php echo $ev['EventID']; ?>">
                                    <button type="submit" name="cancel_event" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Renders a single event card (used in events_all.php)
 */
function renderEventCard($event, $isAiMatch = false) {
    $sl  = $event['seats_left'];
    $slC = $sl === 0 ? '#ef4444' : ($sl <= 5 ? '#f59e0b' : '#10b981');
    $slP = min(100, round(($event['reg_count'] / max(1,$event['capacity'])) * 100));
    $slB = $slP >= 100 ? '#ef4444' : ($slP >= 80 ? '#f59e0b' : '#10b981');
    ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="crystal-card card-sm">
            <div class="position-relative overflow-hidden">
                <img src="<?php echo $event['image']; ?>" class="crystal-img" alt="">
                 <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                    <?php if ($isAiMatch): ?>
                        <span class="premium-tag px-2 py-1" style="font-size: 0.65rem;"><i class="bi bi-lightning-charge-fill me-1"></i> AI MATCH</span>
                    <?php endif; ?>
                    <span class="badge bg-dark bg-opacity-60 backdrop-blur rounded-pill px-3 py-2" style="font-size: 0.65rem;"><?php echo $event['category']; ?></span>
                    <?php if ($event['is_certified']): ?>
                        <span class="badge bg-warning text-dark border-0 rounded-pill px-3 py-2 shadow-sm" style="font-size: 0.65rem;">
                            <i class="bi bi-award-fill me-1"></i> CERTIFICATED
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body-wrap p-4">
                <h6 class="fw-bold text-white mb-2 bidi-text text-truncate-2" dir="auto"><?php echo htmlspecialchars($event['title']); ?></h6>

                <?php if ($isAiMatch && !empty($event['ai_reason'])): ?>
                <div class="ai-reason-bubble mb-3">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <?php echo htmlspecialchars($event['ai_reason']); ?>
                </div>
                <?php endif; ?>

                <div class="text-white-50 small mb-3 mt-auto">
                    <div class="mb-1 text-truncate"><i class="bi bi-geo-alt text-warning me-1"></i> <?php echo htmlspecialchars($event['hall']); ?></div>
                    <div><i class="bi bi-calendar3 text-emerald me-1"></i> <?php echo date('M d, Y', strtotime($event['date'])); ?> | <?php echo $event['time']; ?></div>
                </div>

                <div class="d-flex justify-content-between mb-1" style="font-size:0.73rem;">
                    <span style="color:#475569;"><i class="bi bi-people-fill me-1"></i><?php echo $event['reg_count']; ?>/<?php echo $event['capacity']; ?> registered</span>
                    <span style="color:<?php echo $slC; ?>; font-weight:700;"><?php echo $sl > 0 ? $sl.' left' : 'Full'; ?></span>
                </div>
                <div style="height:3px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;margin-bottom:14px;">
                    <div style="height:100%;width:<?php echo $slP; ?>%;background:<?php echo $slB; ?>;border-radius:4px;"></div>
                </div>

                <button type="button" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-bold text-sm"
                        onclick="openReg(this)"
                        data-id="<?php echo $event['id']; ?>"
                        data-title="<?php echo htmlspecialchars($event['title'], ENT_QUOTES); ?>"
                        data-date="<?php echo date('D, M d Y', strtotime($event['date'])); ?>"
                        data-time="<?php echo $event['time']; ?>"
                        data-hall="<?php echo htmlspecialchars($event['hall'], ENT_QUOTES); ?>"
                        data-cat="<?php echo htmlspecialchars($event['category'], ENT_QUOTES); ?>"
                        data-left="<?php echo $sl; ?>"
                        data-cap="<?php echo $event['capacity']; ?>"
                        data-reg="<?php echo $event['reg_count']; ?>"
                        data-certified="<?php echo $event['is_certified']; ?>"
                        data-desc="<?php echo htmlspecialchars($event['description'] ?? '', ENT_QUOTES); ?>">
                    <i class="bi bi-info-circle me-1"></i> View & Register
                </button>
            </div>
        </div>
    </div>
    <?php
}
?>
