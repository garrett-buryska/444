<?php
header('Content-Type: application/json');

try {
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $workoutId = $_GET['id'] ?? null;

    // Join Lift and Activities to get all the info at once
    $query = "SELECT L.activity_name, L.num_sets, A.description, A.youtube_link, A.set_text, A.main_muscle_group
              FROM Lift L
              JOIN Activities A ON L.activity_name = A.activity_name
              WHERE L.workoutID = ?";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$workoutId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>