<?php
/**
 * profile.php - Organizer Account Management.
 * Handles personal detail updates (phone) and secure password 
 * change logic with current password verification.
 */
require_once '../config.php';
checkRole('organizer');

$organizerId = $_SESSION['user_id'];
$success = '';
$error = '';

// ── 1. Handle Profile & Security Update (POST) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone'] ?? '');
    
    // Step A: Update Phone Number
    $stmt = $conn->prepare("UPDATE organizers SET Phone=? WHERE OrganizerID=?");
    $stmt->bind_param("si", $phone, $organizerId);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
    } else {
        $error = "Failed to update profile.";
    }

    // Step B: Check for Password Change Request
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $current_pw = $_POST['current_password'] ?? '';

    if (!empty($new_pw)) {
        // Step C: Verify Current Password before allowing change
        $stmt = $conn->prepare("SELECT Password FROM organizers WHERE OrganizerID = ?");
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
        $org = $stmt->get_result()->fetch_assoc();

        if ($org['Password'] !== $current_pw) {
            $error = "Incorrect current password.";
            $success = ''; 
        } elseif (strlen($new_pw) < 6) {
            $error = "New password must be at least 6 characters long.";
            $success = '';
        } elseif ($new_pw !== $confirm_pw) {
            $error = "New passwords do not match.";
            $success = '';
        } else {
            // Step D: Apply New Password
            $stmt = $conn->prepare("UPDATE organizers SET Password = ? WHERE OrganizerID = ?");
            $stmt->bind_param("si", $new_pw, $organizerId);
            if ($stmt->execute()) {
                $success = "Profile and password updated successfully.";
            } else {
                $error = "Failed to change password.";
                $success = '';
            }
        }
    }
}

// ── 2. Fetch Current Details for Form Display ────────────────────────
$stmt = $conn->prepare("SELECT Name, Phone, Email, Password FROM organizers WHERE OrganizerID = ?");
$stmt->bind_param("i", $organizerId);
$stmt->execute();
$currentOrg = $stmt->get_result()->fetch_assoc();

$pageTitle = 'Organizer Profile - IU EVENTS';
include 'includes/header.php';
?>

<div class="scroll-main">
    <div style="max-width: 1100px;">
        <!-- Page Header -->
        <div class="page-header no-bg">
            <div>
                <h1 class="page-title">My <span class="title-accent">Profile</span></h1>
                <div class="page-sub">Manage your account settings and security preferences</div>
            </div>
        </div>

        <!-- Feedback Notifications -->
        <?php if($success): ?>
            <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-25 mb-4"><i class="bi bi-check-circle-fill me-2"></i><?=$success?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-danger border-opacity-25 mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=$error?></div>
        <?php endif; ?>

        <!-- Main Form Container -->
        <div class="mb-4">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                    <!-- Left Column: Personal Details (Read-only Name/Email) -->
                    <div>
                        <h3 class="card-title mb-4 pb-2 border-bottom border-secondary border-opacity-25"><i class="bi bi-person-vcard text-warning me-2"></i> Personal Details</h3>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?=htmlspecialchars($currentOrg['Name'])?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?=htmlspecialchars($currentOrg['Email'])?>" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($currentOrg['Phone']??'')?>">
                        </div>
                    </div>

                    <!-- Right Column: Security (Password Management) -->
                    <div>
                        <h3 class="card-title mb-4 pb-2 border-bottom border-secondary border-opacity-25"><i class="bi bi-shield-lock text-warning me-2"></i> Security & Password</h3>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Password (Required for changes)</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Verify identity to change password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                </div>
                
                <!-- Action Footer -->
                <div class="mt-4 pt-4" style="border-top: 1px solid var(--obsidian-border);">
                    <button type="submit" name="update_profile" class="btn btn-gold" style="min-width: 250px;">
                        <i class="bi bi-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

