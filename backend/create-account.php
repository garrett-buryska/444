<?php
// backend/create_account.php

// Start the session to store user data across pages
session_start();

// Allow cross-origin requests
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Read the incoming JSON data from the frontend
$data = json_decode(file_get_contents("php://input"));

// Check if required data is present
if (
    !isset($data->username) ||
    !isset($data->password) ||
    !isset($data->name)
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

$username = $data->username;
$password = $data->password; // Note: For better security, consider hashing passwords using password_hash()
$name = $data->name;
$img_url = isset($data->img_url) ? $data->img_url : null;
$weight = isset($data->weight) ? $data->weight : null;
$height = isset($data->height) ? $data->height : null;
$skill = isset($data->skill) ? $data->skill : null;

try {
    // Connect to your SQLite database
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $pdo->prepare("
        INSERT INTO User (username, password, name, img_url, weight, height, skill_level) 
        VALUES (:username, :password, :name, :img_url, :weight, :height, :skill)
    ");

    // Execute the query
    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':name' => $name,
        ':img_url' => $img_url,
        ':weight' => $weight,
        ':height' => $height,
        ':skill' => $skill
    ]);

    // Automatically log the user in by saving the username to session
    $_SESSION['username'] = $username;

    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully.",
        "username" => $username
    ]);

} catch (PDOException $e) {
    // 23000 is the SQLSTATE error code for constraint violation (e.g., UNIQUE constraint on username)
    if ($e->getCode() == 23000) {
        echo json_encode(["status" => "error", "message" => "Username already exists. Please choose another one."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>