<?php
session_start();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Please provide both username and password."]);
    exit;
}

$username = $data->username;
$password = $data->password;

try {
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM User WHERE username = :username AND password = :password");

    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION["username"] = $username;

        echo json_encode(["status" => "success", "message" => "Welcome back, " . htmlspecialchars($username) . "!", "username" => $username]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}