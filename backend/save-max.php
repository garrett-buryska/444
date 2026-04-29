<?php
session_start();
$username = $_SESSION['username'] ?? "";

if ($username === "") {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

// Get the activity and max value from the request body
$data = json_decode(file_get_contents('php://input'), true);

$activity = $data['activity'] ?? "";
$maxValue = $data['max_value'] ?? 0;

try {
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Use an UPSERT query to insert or update the max value for the given activity and user
    $stmt = $db->prepare("
        INSERT INTO \"Max\" (activity_name, username, max_value) 
        VALUES (:activity, :user, :val)
        ON CONFLICT(activity_name, username) 
        DO UPDATE SET max_value = excluded.max_value
    ");

    $stmt->execute([':activity' => $activity, ':user' => $username, ':val' => $maxValue]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}