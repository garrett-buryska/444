<?php
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->workoutId) || !isset($data->sets)) {
    echo json_encode(["status" => "error", "message" => "Invalid data provided. 'workoutId' and 'sets' array are required."]);
    exit;
}

$workoutId = $data->workoutId;
$sets = $data->sets;

try {
    $db = new PDO("sqlite:gym_app.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->beginTransaction();

    $setStmt = $db->prepare("
        UPDATE Sets 
        SET completed = :completed 
        WHERE liftID = :liftID AND set_number = :set_number
    ");

    foreach ($sets as $set) {
        $setStmt->execute([
            ':completed' => $set->completed ? 1 : 0,
            ':liftID' => $set->liftID,
            ':set_number' => $set->set_number
        ]);
    }

    $liftsStmt = $db->prepare("SELECT liftID FROM Lift WHERE workoutID = :workoutID");
    $liftsStmt->execute([':workoutID' => $workoutId]);
    $liftIDs = $liftsStmt->fetchAll(PDO::FETCH_COLUMN);

    $updateLiftStmt = $db->prepare("UPDATE Lift SET completed = :completed WHERE liftID = :liftID");
    $checkSetsStmt = $db->prepare("
        SELECT COUNT(*) 
        FROM Sets 
        WHERE liftID = :liftID AND (completed = 0 OR completed IS NULL)
    ");

    foreach ($liftIDs as $liftID) {
        $checkSetsStmt->execute([':liftID' => $liftID]);
        $incompleteSetsCount = $checkSetsStmt->fetchColumn();
        $isLiftCompleted = ($incompleteSetsCount == 0);

        $updateLiftStmt->execute([
            ':completed' => $isLiftCompleted ? true : false,
            ':liftID' => $liftID
        ]);
    }

    $db->commit();

    echo json_encode(["status" => "success", "message" => "Workout finished successfully."]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}