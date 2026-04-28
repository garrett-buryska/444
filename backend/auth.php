<?php
// backend/auth.php

session_start();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    // Unset all session variables and destroy the session
    session_unset();
    session_destroy();

    // Clear the session cookie to ensure the browser completely drops it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
    exit;
}

// Default action: Check if user is logged in
if (isset($_SESSION['username'])) {
    echo json_encode(["status" => "success", "username" => $_SESSION['username']]);
} else {
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
}
?>