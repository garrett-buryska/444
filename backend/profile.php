<?php
session_start();

function calculateAge($dob)
{
    if (!$dob) {
        return null;
    }

    try {
        $birthDate = new DateTime($dob);
        $today = new DateTime("today");
        return $birthDate->diff($today)->y;
    } catch (Exception $e) {
        return null;
    }
}

$username = $_SESSION["username"] ?? "";

if ($username === "") {
    echo json_encode(["status" => "error", "message" => "Username is required."]);
    exit;
}

try {
    $db = new PDO("sqlite:gym_app.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // get profile
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $stmt = $db->prepare("
            SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max
            FROM User
            WHERE username = :username
        ");
        $stmt->execute([":username" => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "User not found."]);
            exit;
        }

        echo json_encode(["status" => "success", "profile" => $user]);
        exit;
    }

    // submit profile
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        $name = trim($data["name"] ?? "");
        $imgUrl = trim($data["img_url"] ?? "");
        $weight = isset($data["weight"]) && $data["weight"] !== "" ? $data["weight"] : null;
        $height = isset($data["height"]) && $data["height"] !== "" ? $data["height"] : null;
        $skillLevel = trim($data["skill_level"] ?? "");
        $dob = trim($data["DoB"] ?? "");

        $existsStmt = $db->prepare("SELECT username FROM User WHERE username = :username");
        $existsStmt->execute([":username" => $username]);

        if (!$existsStmt->fetchColumn()) {
            echo json_encode(["status" => "error", "message" => "User not found."]);
            exit;
        }

        $updateStmt = $db->prepare("
            UPDATE User
            SET name = :name,
                img_url = :img_url,
                weight = :weight,
                height = :height,
                skill_level = :skill_level,
                DoB = :dob
            WHERE username = :username
        ");

        $updateStmt->execute([
            ":name" => $name !== "" ? $name : null,
            ":img_url" => $imgUrl !== "" ? $imgUrl : null,
            ":weight" => $weight,
            ":height" => $height,
            ":skill_level" => $skillLevel !== "" ? $skillLevel : null,
            ":dob" => $dob !== "" ? $dob : null,
            ":username" => $username
        ]);

        $profileStmt = $db->prepare("
            SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max
            FROM User
            WHERE username = :username
        ");
        $profileStmt->execute([":username" => $username]);
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "message" => "Profile updated successfully.", "profile" => $profile]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}