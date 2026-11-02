<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "iu_events";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /IU_EVENTS/login.php");
        exit();
    }
}

function checkRole($allowedRole) {
    requireLogin(); 
    if ($_SESSION['user_type'] !== $allowedRole) {
        header("Location: /IU_EVENTS/" . $_SESSION['user_type'] . "/index.php");
        exit();
    }
}

define('GEMINI_API_KEY', 'AIzaSyBL1JQwPN39drTqGwpE5f4Rqx0Vl1lEPdQ'); 
?>
