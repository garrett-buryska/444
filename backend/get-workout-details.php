<?php
try {
    $db = new PDO("sqlite:gym_app.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $workoutId = $_GET['id'] ?? null;

    if (!$workoutId) {
        echo json_encode(['error' => 'No workout ID provided.']);
        exit;
    }

    // query to fetch lift details along with activity information and set details for the given workout ID
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

    $stmt = $db->prepare($query);
    $stmt->execute([$workoutId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $workoutData = [];
    
    // Process the results to structure them by lift and include sets as a nested array
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

        $workoutData[$liftId]['sets'][] = [
            'set_number' => $row['set_number'],
            'set_text' => $row['set_text'],
            'completed' => (bool) $row['completed'],
            'weight' => (float) $row['weight']
        ];
    }

    echo json_encode(array_values($workoutData));

} catch (Exception $e) {
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}