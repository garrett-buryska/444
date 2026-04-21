<?php
try {
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the category and ensure it matches the keys in our switch statement
    $selectedCategory = strtolower(str_replace(' ', '-', $_GET['category'] ?? 'chest'));

    // Define Training Profiles
    switch ($selectedCategory) {
        case 'legs':
        case 'lower-body':
            $profile = ['minSets' => 4, 'maxSets' => 5, 'startReps' => 10, 'decrement' => 1];
            break;
        case 'cardio':
            $profile = ['minSets' => 3, 'maxSets' => 4, 'startReps' => 20, 'decrement' => 5];
            break;
        case 'core':
            $profile = ['minSets' => 3, 'maxSets' => 4, 'startReps' => 15, 'decrement' => 2];
            break;
        case 'chest':
        case 'back':
        case 'arms':
        case 'upper-body':
        default:
            // Standard Hypertrophy
            $profile = ['minSets' => 3, 'maxSets' => 4, 'startReps' => 12, 'decrement' => 2];
            break;
    }

    // 1. Create Workout Header
    $stmt = $db->prepare("INSERT INTO Workout (time_stamp, workout_type) VALUES (CURRENT_TIMESTAMP, ?)");
    $stmt->execute([$selectedCategory]);
    $workoutId = $db->lastInsertId();

    // 2. Select 4 Random Lifts
    // Note: Ensure your 'Activities' table has these categories spelled exactly like the cases above
    $query = "SELECT activity_name FROM Activities WHERE main_muscle_group = ? ORDER BY RANDOM() LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$selectedCategory]);
    $lifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($lifts) >= 4) {
        $insertLiftStmt = $db->prepare("INSERT INTO Lift (workoutID, activity_name, num_sets) VALUES (?, ?, ?)");
        $insertSetStmt = $db->prepare("INSERT INTO Sets (liftID, set_number, set_text) VALUES (?, ?, ?)");

        foreach ($lifts as $lift) {
            $numSets = rand($profile['minSets'], $profile['maxSets']);
            
            $insertLiftStmt->execute([$workoutId, $lift['activity_name'], $numSets]);
            $liftId = $db->lastInsertId();

            for ($i = 1; $i <= $numSets; $i++) {
                // Calculation: Start at profile base, subtract based on decrement
                $currentReps = $profile['startReps'] - (($i - 1) * $profile['decrement']);
                $currentReps = max(2, $currentReps); // Never go below 2 reps
                
                // Set text logic: Default to "X Reps", but change to "Until Failure" on last set 50% of the time
                if ($i == $numSets && rand(0, 1) == 1) {
                    $text = "Until Failure";
                } else {
                    $text = "{$currentReps} Reps";
                }

                $insertSetStmt->execute([$liftId, $i, $text]);
            }
        }

        header("Location: ../frontend/current-workout.html?id=" . $workoutId);
        exit();

    } else {
        echo "Error: Need at least 4 exercises in category '$selectedCategory'.";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>