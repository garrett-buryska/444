<?php
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_unset(); // clear session
    session_destroy(); // delete session

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
    exit;
}

// checking if user logged in
if (isset($_SESSION['username'])) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}