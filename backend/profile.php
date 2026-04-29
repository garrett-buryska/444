<?php
session_start();

$username = $_SESSION["username"] ?? "";

if ($username === "") {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

try {
    $db = new PDO("sqlite:gym_app.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get profile details for the logged-in user
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $stmt = $db->prepare("SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max FROM User WHERE username = :username");
        $stmt->execute([":username" => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "User not found."]);
            exit;
        }

        echo json_encode(["status" => "success", "profile" => $user]);
        exit;
    }

    // Update profile details for the logged-in user
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        // Perform update directly using the session username
        $updateStmt = $db->prepare("
            UPDATE User 
            SET name = :name, img_url = :img_url, weight = :weight, height = :height, skill_level = :skill_level, DoB = :dob 
            WHERE username = :username
        ");

        $updateStmt->execute([
            ":name"        => !empty(trim($data["name"] ?? "")) ? trim($data["name"]) : null,
            ":img_url"     => !empty(trim($data["img_url"] ?? "")) ? trim($data["img_url"]) : null,
            ":weight"      => ($data["weight"] ?? "") !== "" ? $data["weight"] : null,
            ":height"      => ($data["height"] ?? "") !== "" ? $data["height"] : null,
            ":skill_level" => !empty(trim($data["skill_level"] ?? "")) ? trim($data["skill_level"]) : null,
            ":dob"         => !empty(trim($data["DoB"] ?? "")) ? trim($data["DoB"]) : null,
            ":username"    => $username
        ]);

        // Re-fetch updated profile to send back to frontend
        $stmt = $db->prepare("SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max FROM User WHERE username = :username");
        $stmt->execute([":username" => $username]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "message" => "Profile updated.", "profile" => $profile]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Method not allowed."]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}