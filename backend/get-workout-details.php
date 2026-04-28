<?php
try {
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $workoutId = $_GET['id'] ?? null;

    if (!$workoutId) {
        http_response_code(400);
        echo json_encode(['error' => 'No workout ID provided.']);
        exit;
    }

    // 1. Added S.weight to the SELECT statement
    $query = "SELECT 
                L.liftID, 
                L.activity_name, 
                L.num_sets, 
                A.description, 
                A.youtube_link, 
                A.main_muscle_group, 
                S.set_number, 
                S.set_text,
                S.completed,
                S.weight
              FROM Lift L
              JOIN Activities A ON L.activity_name = A.activity_name
              JOIN Sets S ON L.liftID = S.liftID
              WHERE L.workoutID = ?
              ORDER BY L.liftID, S.set_number";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$workoutId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $workoutData = [];
    foreach ($results as $row) {
        $liftId = $row['liftID'];

        if (!isset($workoutData[$liftId])) {
            $workoutData[$liftId] = [
                'liftID' => $liftId,
                'activity_name' => $row['activity_name'],
                'num_sets' => $row['num_sets'],
                'description' => $row['description'],
                'youtube_link' => $row['youtube_link'],
                'main_muscle_group' => $row['main_muscle_group'],
                'sets' => []
            ];
        }

        // 2. Added 'weight' to the array
        $workoutData[$liftId]['sets'][] = [
            'set_number' => $row['set_number'],
            'set_text' => $row['set_text'],
            'completed' => (bool) $row['completed'],
            'weight' => (float) $row['weight'] // Cast to float for consistency
        ];
    }

    echo json_encode(array_values($workoutData));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
?>