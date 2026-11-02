<?php
require_once '../config.php';
checkRole('host');

$hostId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone'] ?? '');
    
    $stmt = $conn->prepare("UPDATE hosts SET Phone=? WHERE HostID=?");
    $stmt->bind_param("si", $phone, $hostId);
    if ($stmt->execute()) {
        $success = "Profile updated successfully.";
    } else {
        $error = "Failed to update profile.";
    }

    $new_pw = $_POST['new_password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $current_pw = $_POST['current_password'] ?? '';

    if (!empty($new_pw)) {
        $stmt = $conn->prepare("SELECT Password FROM hosts WHERE HostID = ?");
        $stmt->bind_param("i", $hostId);
        $stmt->execute();
        $host = $stmt->get_result()->fetch_assoc();

        if ($host['Password'] !== $current_pw) {
            $error = "Incorrect current password.";
            $success = ''; 
        } elseif (strlen($new_pw) < 6) {
            $error = "New password must be at least 6 characters long.";
            $success = '';
        } elseif ($new_pw !== $confirm_pw) {
            $error = "New passwords do not match.";
            $success = '';
        } else {
            $stmt = $conn->prepare("UPDATE hosts SET Password = ? WHERE HostID = ?");
            $stmt->bind_param("si", $new_pw, $hostId);
            if ($stmt->execute()) {
                $success = "Profile and password updated successfully.";
            } else {
                $error = "Failed to change password.";
                $success = '';
            }
        }
    }
}

$stmt = $conn->prepare("SELECT Name, Phone, Email, Password FROM hosts WHERE HostID = ?");
$stmt->bind_param("i", $hostId);
$stmt->execute();
$currentHost = $stmt->get_result()->fetch_assoc();

$pageTitle = 'Host Profile - IU Events';
include 'includes/header.php';
?>

<div class="scroll-main">
    <div style="max-width: 1100px;">
        <div class="page-header bordered">
            <div>
                <h1 class="page-title">My <span class="title-accent">Profile</span></h1>
                <p class="page-sub">Manage your account settings</p>
            </div>
        </div>

        <?php if($success): ?>
            <div class="alert alert-obsidian mb-4"><i class="bi bi-check-circle-fill me-2"></i><?=$success?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-obsidian error mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?=$error?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                <div>
                    <h3 class="mb-4 pb-2 text-white border-bottom border-secondary" style="font-family:'Outfit';"><i class="bi bi-person-vcard text-warning me-2"></i> Personal Details</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?=htmlspecialchars($currentHost['Name'])?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?=htmlspecialchars($currentHost['Email'])?>" readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($currentHost['Phone']??'')?>">
                    </div>
                </div>

                <div>
                    <h3 class="mb-4 pb-2 text-white border-bottom border-secondary" style="font-family:'Outfit';"><i class="bi bi-shield-lock text-warning me-2"></i> Security & Password</h3>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Password (Preview)</label>
                        <input type="text" name="current_password" class="form-control" value="<?=htmlspecialchars($currentHost['Password'])?>">
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
            
            <div class="mt-4 pt-4" style="border-top: 1px solid var(--obsidian-border);">
                <button type="submit" name="update_profile" class="btn btn-gold" style="min-width: 250px;">
                    <i class="bi bi-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
