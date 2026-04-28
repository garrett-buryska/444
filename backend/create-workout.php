<?php
session_start();
$username = $_SESSION['username'] ?? "";

if (!$username) {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

try {
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selectedCategory = strtolower(str_replace(' ', '-', $_GET['category'] ?? 'chest'));

    // 1. Create Workout
    $stmt = $db->prepare("INSERT INTO Workout (time_stamp, workout_type, username) VALUES (CURRENT_TIMESTAMP, ?, ?)");
    $stmt->execute([$selectedCategory, $username]);
    $workoutId = $db->lastInsertId();

    // 2. Get Random Exercises
    $query = "SELECT activity_name, average_sets, average_reps, set_type FROM Activities WHERE main_muscle_group = ? ORDER BY RANDOM() LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$selectedCategory]);
    $lifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Maxes for these specific activities
    $activityNames = array_column($lifts, 'activity_name');
    $inQuery = implode(',', array_fill(0, count($activityNames), '?'));
    $stmtMax = $db->prepare("SELECT activity_name, max_value FROM \"Max\" WHERE username = ? AND activity_name IN ($inQuery)");
    $stmtMax->execute(array_merge([$username], $activityNames));
    $maxValues = $stmtMax->fetchAll(PDO::FETCH_KEY_PAIR);

    if (count($lifts) >= 4) {
        $insertLiftStmt = $db->prepare("INSERT INTO Lift (workoutID, activity_name, num_sets) VALUES (?, ?, ?)");

        // Updated INSERT statement to include 'weight'
        $insertSetStmt = $db->prepare("INSERT INTO Sets (liftID, set_number, set_text, reps, weight) VALUES (?, ?, ?, ?, ?)");

        foreach ($lifts as $lift) {
            $actName = $lift['activity_name'];
            $max = $maxValues[$actName] ?? 0;

            $avgSets = (int) $lift['average_sets'];
            $numSets = rand(max(1, $avgSets - 1), $avgSets + 1);

            $insertLiftStmt->execute([$workoutId, $actName, $numSets]);
            $liftId = $db->lastInsertId();

            for ($i = 1; $i <= $numSets; $i++) {
                $type = $lift['set_type'];

                if ($type === 'reps') {
                    $value = (int) $lift['average_reps'] - (($i - 1) * 2);
                    $value = max(2, $value);
                    $text = $value . " Reps";

                    // DYNAMIC WEIGHT LOGIC (Ascending with big initial jump)
                    if ($max > 0) {
                        if ($i == 1) {
                            // Start at 60%
                            $intensity = 0.60;
                        } elseif ($i == 2) {
                            // Big jump to 75%
                            $intensity = 0.75;
                        } else {
                            // Gradual increase of 5% per set thereafter
                            $intensity = 0.75 + (($i - 2) * 0.05);
                        }

                        // Cap intensity at 95%
                        $intensity = min($intensity, 0.95);

                        // Calculate weight and round to nearest 5
                        $rawWeight = $max * $intensity;
                        $weight = round($rawWeight / 5) * 5;

                    } else {
                        $weight = 0; // Fallback if no max is set
                    }

                    if ($i == $numSets && rand(0, 1) == 1) {
                        $text = "Until Failure";
                        $value = 0;
                    }
                } else {
                    $value = (int) $lift['average_reps'];
                    $text = $value . " " . ucfirst($type);
                    $weight = 0;
                }

                $insertSetStmt->execute([$liftId, $i, $text, $value, $weight]);
            }
        }

        header("Location: ../frontend/current-workout.html?id=" . $workoutId);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Need at least 4 exercises."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}