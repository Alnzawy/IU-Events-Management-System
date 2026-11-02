<?php
/** Student Profile - Manage student account details */
require_once '../config.php';
checkRole('student');

$studentID = $_SESSION['user_id'];
$success = '';
$error = '';

$interestMapping = [
    "AI & Data Intelligence"     => "bi-robot",
    "Cybersecurity & Systems"    => "bi-shield-lock",
    "UX/UI & Creative Media"     => "bi-palette",
    "Game Dev & Virtual Reality" => "bi-controller",
    "Startups & Innovation"      => "bi-lightbulb",
    "Career & Leadership"        => "bi-award"
];

// Handle Profile & Security Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone'] ?? '');
    $major = trim($_POST['major'] ?? '');
    
    $selectedInterests = isset($_POST['student_interests']) ? $_POST['student_interests'] : [];
    $interestsStr = implode(',', $selectedInterests);
    
    // Update Personal Info & Interests
    $stmt = $conn->prepare("UPDATE students SET Phone=?, Major=?, Interests=? WHERE StudentID=?");
    $stmt->bind_param("sssi", $phone, $major, $interestsStr, $studentID);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
    } else {
        $error = "Failed to update profile.";
    }

    // Check for Password Change Request
    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $current_pw = $_POST['current_password'] ?? '';

    if (!empty($new_pw)) {
        // Verify Current Password
        $stmt = $conn->prepare("SELECT Password FROM students WHERE StudentID = ?");
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if ($student['Password'] !== $current_pw) {
            $error = "Incorrect current password.";
            $success = ''; 
        } elseif (strlen($new_pw) < 6) {
            $error = "New password must be at least 6 characters long.";
            $success = '';
        } elseif ($new_pw !== $confirm_pw) {
            $error = "New passwords do not match.";
            $success = '';
        } else {
            // Apply New Password
            $stmt = $conn->prepare("UPDATE students SET Password = ? WHERE StudentID = ?");
            $stmt->bind_param("si", $new_pw, $studentID);
            if ($stmt->execute()) {
                $success = "Profile and password updated successfully.";
            } else {
                $error = "Failed to change password.";
                $success = '';
            }
        }
    }
}

// Fetch Current Details
$stmt = $conn->prepare("SELECT Name, Phone, Email, Major, Interests FROM students WHERE StudentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$currentStudent = $stmt->get_result()->fetch_assoc();
$studentInterests = empty($currentStudent['Interests']) ? [] : explode(',', $currentStudent['Interests']);

$pageTitle = 'Student Profile - IU EVENTS';
include 'includes/header.php';
?>

        <!-- Page Header -->
        <div class="page-header no-bg mt-2">
            <div>
                <h1 class="page-title">My <span class="title-accent">Profile</span></h1>
                <div class="page-sub">Manage your account settings, security, and interests</div>
            </div>
        </div>

        <?php if($success): ?>
            <div class="alert alert-obsidian mb-4"><i class="bi bi-check-circle-fill me-2"></i><?=$success?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-obsidian error mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=$error?></div>
        <?php endif; ?>

        <!-- Main Form Container -->
        <div class="mb-4">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                    
                    <!-- Left Column: Personal Details & Major -->
                    <div>
                        <h3 class="card-title mb-4 pb-2 border-bottom border-secondary border-opacity-25"><i class="bi bi-person-vcard text-warning me-2"></i> Personal Details</h3>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?=htmlspecialchars($currentStudent['Name'])?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?=htmlspecialchars($currentStudent['Email'])?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($currentStudent['Phone'])?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Major</label>
                            <select name="major" class="form-select">
                                <option value="CS" <?=($currentStudent['Major'] === 'CS' ? 'selected' : '')?>>Computer Science (CS)</option>
                                <option value="IT" <?=($currentStudent['Major'] === 'IT' ? 'selected' : '')?>>Information Technology (IT)</option>
                                <option value="IS" <?=($currentStudent['Major'] === 'IS' ? 'selected' : '')?>>Information Systems (IS)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column: Security -->
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

                <!-- Full Width Bottom Section: Interests -->
                <div class="mt-4 pt-2">
                    <h3 class="card-title mb-3 pb-2 border-bottom border-secondary border-opacity-25"><i class="bi bi-stars text-warning me-2"></i> Your Interests</h3>
                    <p class="text-white-50 small mb-3">Select your interests to personalize your AI event recommendations.</p>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php foreach ($interestMapping as $intKey => $icon): 
                            $isChecked = in_array($intKey, $studentInterests);
                        ?>
                            <div class="form-check-inline m-0">
                                <input type="checkbox" name="student_interests[]" value="<?php echo $intKey; ?>" 
                                       id="tag_<?php echo md5($intKey); ?>" class="btn-check" 
                                       <?php echo $isChecked ? 'checked' : ''; ?>>
                                <label class="btn btn-outline-secondary rounded-pill px-3 py-2 text-white border-opacity-25" for="tag_<?php echo md5($intKey); ?>" style="transition: all 0.2s;">
                                    <i class="bi <?php echo $icon; ?> me-2 <?php echo $isChecked ? 'text-gold' : ''; ?>"></i><?php echo $intKey; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Action Footer -->
                <div class="mt-4 pt-4 mb-4" style="border-top: 1px solid var(--obsidian-border);">
                    <button type="submit" name="update_profile" class="btn btn-gold" style="min-width: 250px;">
                        <i class="bi bi-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
<?php include 'includes/footer.php'; ?>
