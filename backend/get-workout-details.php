<?php
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");

try {
    $dbPath = __DIR__ . '/gym_app.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $workoutId = $_GET['id'] ?? null;

    if (!$workoutId) {
        http_response_code(400);
        echo json_encode(['error' => 'No workout ID provided.']);
        exit;
    }

    // Join Lift, Activities, and Sets to get everything in one query
    $query = "SELECT 
                L.liftID, 
                L.activity_name, 
                L.num_sets, 
                A.description, 
                A.youtube_link, 
                A.main_muscle_group, 
                S.set_number, 
                S.set_text,
                S.completed
              FROM Lift L
              JOIN Activities A ON L.activity_name = A.activity_name
              JOIN Sets S ON L.liftID = S.liftID
              WHERE L.workoutID = ?
              ORDER BY L.liftID, S.set_number";

    $stmt = $db->prepare($query);
    $stmt->execute([$workoutId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group the results into a clean structure for the frontend
    $workoutData = [];
    foreach ($results as $row) {
        $liftId = $row['liftID'];

        // If this lift isn't in our array yet, initialize it
        if (!isset($workoutData[$liftId])) {
            $workoutData[$liftId] = [
                'liftID' => $liftId,
                'activity_name' => $row['activity_name'],
                'num_sets' => $row['num_sets'],
                'description' => $row['description'],
                'youtube_link' => $row['youtube_link'],
                'main_muscle_group' => $row['main_muscle_group'],
                'sets' => [] // This will hold the set details
            ];
        }

        // Add the set info to this lift
        $workoutData[$liftId]['sets'][] = [
            'set_number' => $row['set_number'],
            'set_text' => $row['set_text'],
            'completed' => (bool) $row['completed']
        ];
    }

    // Re-index array to be a clean list
    echo json_encode(array_values($workoutData));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
?>