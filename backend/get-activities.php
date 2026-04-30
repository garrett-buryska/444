<?php
session_start();
$username = $_SESSION['username'] ?? 'Guest';

try {
    $db = new PDO("sqlite:gym_app.db");

    // Get all activities and the user's max values for those activities
    $stmt = $db->prepare("
        SELECT A.activity_name, A.set_type, M.max_value 
        FROM Activities A 
        LEFT JOIN \"Max\" M ON A.activity_name = M.activity_name AND M.username = :user
        ORDER BY A.activity_name ASC
    ");

    $stmt->execute([':user' => $username]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($activities);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}