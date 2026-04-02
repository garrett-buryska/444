<?php
// backend/login.php

// Allow cross-origin requests if you eventually run frontend and backend on different ports
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Read the incoming JSON data from the frontend
$data = json_decode(file_get_contents("php://input"));

// Check if data was sent
if (!isset($data->username) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Please provide both username and password."]);
    exit;
}

$username = $data->username;
$password = $data->password;

try {
    // Connect to your SQLite database
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement to prevent SQL injection
    // Note: Adjust table/column names if they differ from what's in your DB
    $stmt = $pdo->prepare("SELECT * FROM User WHERE username = :username AND password = :password");

    // Execute the query
    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);

    // Fetch the user
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // User found!
        echo json_encode([
            "status" => "success",
            "message" => "Welcome back, " . htmlspecialchars($username) . "!"
        ]);
    } else {
        // User not found or password incorrect
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password."
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>