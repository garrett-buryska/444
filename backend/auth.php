<?php
session_start();

$action = $_GET['action'] ?? '';

// Checks if the user wants to log out
if ($action === 'logout') {
    session_unset(); // clear session
    session_destroy(); // delete session

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
    exit;
}

// Checking if user logged in
if (isset($_SESSION['username'])) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}