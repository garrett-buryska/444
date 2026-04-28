<?php
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

    $updateSetStmt = $pdo->prepare("
        UPDATE Sets 
        SET completed = :completed 
        WHERE liftID = :liftID AND set_number = :set_number
    ");

    foreach ($sets as $set) {
        if (isset($set->liftID) && isset($set->set_number) && isset($set->completed)) {
            $updateSetStmt->execute([
                ':completed' => $set->completed ? 1 : 0,
                ':liftID' => $set->liftID,
                ':set_number' => $set->set_number
            ]);
        }
    }

    $getLiftsStmt = $pdo->prepare("SELECT liftID FROM Lift WHERE workoutID = :workoutID");
    $getLiftsStmt->execute([':workoutID' => $workoutId]);
    $liftIDs = $getLiftsStmt->fetchAll(PDO::FETCH_COLUMN);

    $updateLiftStmt = $pdo->prepare("UPDATE Lift SET completed = :completed WHERE liftID = :liftID");
    $checkSetsStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Sets 
        WHERE liftID = :liftID AND (completed = 0 OR completed IS NULL)
    ");

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
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}