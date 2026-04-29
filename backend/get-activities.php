<?php
session_start();
$username = $_SESSION['username'] ?? 'Guest';

try {
    $dbPath = __DIR__ . '/gym_app.db';
    $db = new PDO('sqlite:' . $dbPath);

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
?>