<?php
require_once 'config.php';

if (isset($_GET['logout'])) {
    $_SESSION = array(); 
    session_destroy();   
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $roles = [
                'student' => 'students',
                'host' => 'hosts',
                'organizer' => 'organizers'
            ];
           
            foreach ($roles as $type => $table) {
                $stmt = $conn->prepare("SELECT * FROM `$table` WHERE Email = ? AND Password = ?");
                $stmt->bind_param("ss", $email, $password);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $_SESSION['user_id'] = $user[ucfirst($type) . 'ID'];
                    $_SESSION['user_name'] = $user['Name'];
                    $_SESSION['user_type'] = $type;
                    
                    header("Location: $type/index.php");
                    exit();
                }
            }
            
            $error = "Invalid email or password.";
        } elseif ($_POST['action'] === 'signup') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $major = $_POST['major'];
            $password = $_POST['password'];
            
            $email_exists = false;
             
            $tables = ['students', 'hosts', 'organizers'];
            
            foreach ($tables as $table) {
                $check = $conn->prepare("SELECT Email FROM `$table` WHERE Email = ?");
                $check->bind_param("s", $email);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $email_exists = true;
                    break;
                }
            }

            if ($email_exists) {
                $error = "This email is already associated with an account.";
            } else {
                $stmt = $conn->prepare("INSERT INTO students (Name, Email, Phone, Major, Password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $email, $phone, $major, $password);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                    $active_panel = ''; 
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login / Signup - IU EVENTS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/common.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container" id="authContainer">
            <div class="form-container sign-up-container">
                <form class="auth-form" method="POST">   
                    <h1 class="mb-1">Create Account</h1>
                    <input type="hidden" name="action" value="signup">
 
                    <div class="auth-input-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control-dark" placeholder="Enter your name" required>
                    </div>
                    <div class="row w-100 p-0 m-0">
                        <div class="col-6 ps-0">
                            <div class="auth-input-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control-dark" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="col-6 pe-0">
                            <div class="auth-input-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control-dark" placeholder="Phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="auth-input-group">
                        <label>Major</label>
                        <input type="text" name="major" class="form-control-dark" placeholder="Your major" required>
                    </div>
                    <div class="auth-input-group mb-4">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control-dark" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn btn-login w-100 py-3 mb-3">Sign Up</button>
                </form>
            </div>

            <div class="form-container sign-in-container">
                <form class="auth-form" method="POST">
                    <h1 class="mb-4">Sign In</h1>
                    <input type="hidden" name="action" value="login">

                <?php if ($error): ?>
                    <div class="error-alert w-100">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success-alert w-100">
                        <i class="bi bi-check-circle-fill"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                    <div class="auth-input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control-dark" placeholder="Enter email" required>
                    </div>
                    <div class="auth-input-group mb-2">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control-dark" placeholder="Enter password" required>
                    </div>
                    
                    <div class="auth-footer mb-4">
                        <a href="index.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>
                    </div>

                    <button type="submit" class="btn btn-login w-100 py-3 mb-3">Sign In</button>
                </form>
            </div>

            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>Welcome Back!</h1>
                        <p>To keep connected with us please login with your personal info</p>
                        <button class="btn-ghost" id="signIn">Sign In</button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>Hello, Student!</h1>
                        <p>Enter your personal details and start your journey with us</p>
                        <button class="btn-ghost" id="signUp">Sign Up</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('authContainer');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
</body>
</html>
