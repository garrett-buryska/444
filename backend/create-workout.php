<?php
session_start();
$username = $_SESSION['username'] ?? "";

// Check for username in session
if (!$username) {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

try {
    //Connect to the database
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the selected category, default to 'chest' if not provided
    $selectedCategory = strtolower(str_replace(' ', '-', $_GET['category'] ?? 'chest'));

    // 1. Create Workout SQL statement and execute
    $stmt = $db->prepare("INSERT INTO Workout (time_stamp, workout_type, username) VALUES (CURRENT_TIMESTAMP, ?, ?)");
    $stmt->execute([$selectedCategory, $username]);
    $workoutId = $db->lastInsertId();

    // 2. Get Random Exercises Query and execute that much category
    $query = "SELECT activity_name, average_sets, average_reps, set_type FROM Activities WHERE main_muscle_group = ? ORDER BY RANDOM() LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$selectedCategory]);
    $lifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Maxes
    $activityNames = array_column($lifts, 'activity_name');
    $inQuery = implode(',', array_fill(0, count($activityNames), '?'));
    $stmtMax = $db->prepare("SELECT activity_name, max_value FROM \"Max\" WHERE username = ? AND activity_name IN ($inQuery)");
    $stmtMax->execute(array_merge([$username], $activityNames));
    // Convert to associative array for easy lookup
    $maxValues = $stmtMax->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Insert Lifts and Sets 
    if (count($lifts) >= 4) {
        // Prepare statements for inserting lifts and sets
        $insertLiftStmt = $db->prepare("INSERT INTO Lift (workoutID, activity_name, num_sets) VALUES (?, ?, ?)");
        $insertSetStmt = $db->prepare("INSERT INTO Sets (liftID, set_number, set_text, reps, weight) VALUES (?, ?, ?, ?, ?)");

        foreach ($lifts as $lift) {
            // Get the max value for this activity, if it exists
            $actName = $lift['activity_name'];
            $max = $maxValues[$actName] ?? 0;

            // Determine number of sets based on average, with some randomness. +- 1
            $avgSets = (int) $lift['average_sets'];
            $numSets = rand(max(1, $avgSets - 1), $avgSets + 1);

            // Insert the lift and get its ID
            $insertLiftStmt->execute([$workoutId, $actName, $numSets]);
            $liftId = $db->lastInsertId();

            // Insert every set for this lift
            for ($i = 1; $i <= $numSets; $i++) {
                $type = $lift['set_type'];
                $weight = 0;
                $intensity = 0.60; 

                // First set is always 60% of max, second set is 75%, and then we increase by 5% for each subsequent set, capping at 95%
                if ($i == 1) {
                    $intensity = 0.60;
                } elseif ($i == 2) {
                    $intensity = 0.75;
                } else {
                    $intensity = min(0.95, 0.75 + (($i - 2) * 0.05));
                }

                // Checks type of exercise needed and calculates the set text and reps/seconds accordingly
                if ($type === 'reps') {
                    // WEIGHTED LOGIC
                    $value = max(2, (int) $lift['average_reps'] - (($i - 1) * 2));
                    $text = $value . " Reps";

                    if ($max > 0) {
                        $rawWeight = $max * $intensity;
                        $weight = round($rawWeight / 5) * 5;
                    }

                } elseif ($type === 'body') {
                    if ($max > 0) {
                        $value = max(1, round($max * $intensity));
                    } else {
                        $value = (int) $lift['average_reps'];
                    }
                    $text = $value . " Reps";
                    $weight = 0;

                } else {
                    $value = (int) $lift['average_reps'];
                    $text = $value . " " . ucfirst($type);
                    $weight = 0;
                }

                // Sometimes have the last set end with until failure
                if ($type === 'body' && $i == $numSets) {
                    $text = "Until Failure";
                    $value = 0;
                } 
                // If it's 'reps' type, randomly (50%) end in failure
                elseif ($type === 'reps' && $i == $numSets && rand(0, 1) == 1) {
                    $text = "Until Failure";
                    $value = 0;
                }

                // Insert the set
                $insertSetStmt->execute([$liftId, $i, $text, $value, $weight]);
            }
        }
        // Redirect to the current workout page with the workout ID as a query parameter
        header("Location: ../frontend/current-workout.html?id=" . $workoutId);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Need at least 4 exercises."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}