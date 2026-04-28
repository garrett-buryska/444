<?php
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    session_unset();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
    exit;
}

if (isset($_SESSION['username'])) {
    echo json_encode(["status" => "success", "username" => $_SESSION['username']]);
} else {
    echo json_encode(["status" => "error", "message" => "Not logged in."]);
}