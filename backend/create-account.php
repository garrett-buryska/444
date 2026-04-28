<?php
session_start();

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->username) ||
    !isset($data->password) ||
    !isset($data->name)
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

$username = $data->username;
$password = $data->password;
$name = $data->name;
$imgUrl = $data->img_url ?? null;
$dob = $data->dob ?? null;
$weight = $data->weight ?? null;
$height = $data->height ?? null;
$skillLevel = $data->skill ?? null;

try {
    $dbPath = __DIR__ . '/gym_app.db';
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
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

    $_SESSION['username'] = $username;

    echo json_encode(["status" => "success", "message" => "Account created successfully.", "username" => $username]);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo json_encode(["status" => "error", "message" => "Username already exists. Please choose another one."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}