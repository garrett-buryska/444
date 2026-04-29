<?php
session_start();

//Converts the JSON code to a PHP object
$data = json_decode(file_get_contents("php://input"));

//Check if required fields are present
if (
    !isset($data->username) ||
    !isset($data->password) ||
    !isset($data->name) ||
    !isset($data->dob)
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

// Extracting variables from the data object
$username = $data->username;
$password = $data->password;
$name = $data->name;
$imgUrl = $data->img_url ?? null;
$dob = $data->dob ?? null;
$weight = $data->weight ?? null;
$height = $data->height ?? null;
$skillLevel = $data->skill ?? null;


try {
    // Connect to the database
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //Prepare and execute the SQL statement to insert a new user
    $stmt = $db->prepare("
        INSERT INTO User (username, password, name, img_url, DoB, weight, height, skill_level) 
        VALUES (:username, :password, :name, :imgUrl, :dob, :weight, :height, :skillLevel)
    ");

    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':name' => $name,
        ':imgUrl' => $imgUrl,
        ':weight' => $weight,
        ':height' => $height,
        ':skillLevel' => $skillLevel,
        ':dob' => $dob
    ]);

    // Set the session variable for the username
    $_SESSION['username'] = $username;

    // Return a success response with the username
    echo json_encode(["status" => "success", "message" => "Account created successfully.", "username" => $username]);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo json_encode(["status" => "error", "message" => "Username already exists. Please choose another one."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}