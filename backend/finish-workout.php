<?php
// backend/finish-workout.php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->workoutId) || !isset($data->sets) || !is_array($data->sets)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid data provided. 'workoutId' and 'sets' array are required."]);
    exit;
}

$workoutId = $data->workoutId;
$sets = $data->sets;

try {
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    // Prepare statement for updating sets
    $updateSetStmt = $pdo->prepare("
        UPDATE Sets 
        SET completed = :completed 
        WHERE liftID = :liftID AND set_number = :set_number
    ");

    // Update each set's completion status
    foreach ($sets as $set) {
        if (isset($set->liftID) && isset($set->set_number) && isset($set->completed)) {
            $updateSetStmt->execute([
                ':completed' => $set->completed ? 1 : 0,
                ':liftID' => $set->liftID,
                ':set_number' => $set->set_number
            ]);
        }
    }

    // Get all liftIDs for this workout to update their overall status
    $getLiftsStmt = $pdo->prepare("SELECT liftID FROM Lift WHERE workoutID = :workoutID");
    $getLiftsStmt->execute([':workoutID' => $workoutId]);
    $liftIDs = $getLiftsStmt->fetchAll(PDO::FETCH_COLUMN);

    $updateLiftStmt = $pdo->prepare("UPDATE Lift SET completed = :completed WHERE liftID = :liftID");
    $checkSetsStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Sets 
        WHERE liftID = :liftID AND (completed = 0 OR completed IS NULL)
    ");

    // Update each lift's completion status based on its sets
    foreach ($liftIDs as $liftID) {
        $checkSetsStmt->execute([':liftID' => $liftID]);
        $incompleteSetsCount = $checkSetsStmt->fetchColumn();
        $isLiftCompleted = ($incompleteSetsCount == 0);

        $updateLiftStmt->execute([
            ':completed' => $isLiftCompleted ? 1 : 0,
            ':liftID' => $liftID
        ]);
    }

    $pdo->commit();

    echo json_encode(["status" => "success", "message" => "Workout finished successfully."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>