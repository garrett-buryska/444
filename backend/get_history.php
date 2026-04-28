<?php
// backend/get_history.php

session_start();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

$username = $_SESSION['username'] ?? null;

if (!$username) {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

try {
    // Connect to your SQLite database
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch workouts and concatenate the activities performed in each workout
    $stmt = $pdo->prepare("
        SELECT w.workout_Id, w.workout_type, w.time_stamp,
               GROUP_CONCAT(l.activity_name, ', ') as exercises
        FROM Workout w
        LEFT JOIN Lift l ON w.workout_Id = l.workoutID
        WHERE w.username = :username
        GROUP BY w.workout_Id
        ORDER BY w.time_stamp DESC
    ");
    $stmt->execute([':username' => $username]);
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $workouts]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>