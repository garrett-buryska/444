<?php
session_start();

$username = $_SESSION['username'] ?? 'Guest';

try {
    $db = new PDO('sqlite:gym_app.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selectedCategory = strtolower(str_replace(' ', '-', $_GET['category'] ?? 'chest'));

    $stmt = $db->prepare("INSERT INTO Workout (time_stamp, workout_type, username) VALUES (CURRENT_TIMESTAMP, ?, ?)");
    $stmt->execute([$selectedCategory, $username]);
    $workoutId = $db->lastInsertId();

    $query = "SELECT activity_name, average_sets, average_reps, set_type FROM Activities WHERE main_muscle_group = ? ORDER BY RANDOM() LIMIT 4";
    $stmt = $db->prepare($query);
    $stmt->execute([$selectedCategory]);
    $lifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($lifts) >= 4) {
        $insertLiftStmt = $db->prepare("INSERT INTO Lift (workoutID, activity_name, num_sets) VALUES (?, ?, ?)");

        $insertSetStmt = $db->prepare("INSERT INTO Sets (liftID, set_number, set_text, reps) VALUES (?, ?, ?, ?)");

        foreach ($lifts as $lift) {
            $avgSets = (int) $lift['average_sets'];
            $numSets = rand(max(1, $avgSets - 1), $avgSets + 1);

            $insertLiftStmt->execute([$workoutId, $lift['activity_name'], $numSets]);
            $liftId = $db->lastInsertId();

            for ($i = 1; $i <= $numSets; $i++) {
                $type = $lift['set_type'];

                if ($type === 'reps') {
                    $value = (int) $lift['average_reps'] - (($i - 1) * 2);
                    $value = max(2, $value);
                    $text = $value . " Reps";

                    if ($i == $numSets && rand(0, 1) == 1) {
                        $text = "Until Failure";
                        $value = 0;
                    }
                } else {
                    $value = (int) $lift['average_reps'];
                    $text = $value . " " . ucfirst($type);
                }

                $insertSetStmt->execute([$liftId, $i, $text, $value]);
            }
        }

        header("Location: ../frontend/current-workout.html?id=" . $workoutId);
        exit();

    } else {
        echo json_encode(["status" => "error", "message" => "Error: Need at least 4 exercises in category '$selectedCategory'."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "Database Error: " . $e->getMessage()]);
}