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

try {
    $dbPath = __DIR__ . "/gym_app.db";
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $username = $_SESSION["username"] ?? "";

        if ($username === "") {
            echo json_encode(["status" => "error", "message" => "Username is required."]);
            exit;
        }

        $stmt = $pdo->prepare("
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

        $user["age"] = calculateAge($user["DoB"]);

        echo json_encode(["status" => "success", "profile" => $user]);
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        $username = trim($_SESSION["username"] ?? "");

        if ($username === "") {
            echo json_encode(["status" => "error", "message" => "Username is required."]);
            exit;
        }

        $name = trim($data["name"] ?? "");
        $imgUrl = trim($data["img_url"] ?? "");
        $weight = isset($data["weight"]) && $data["weight"] !== "" ? $data["weight"] : null;
        $height = isset($data["height"]) && $data["height"] !== "" ? $data["height"] : null;
        $skillLevel = trim($data["skill_level"] ?? "");
        $dob = trim($data["DoB"] ?? "");

        $existsStmt = $pdo->prepare("SELECT username FROM User WHERE username = :username");
        $existsStmt->execute([":username" => $username]);

        if (!$existsStmt->fetchColumn()) {
            echo json_encode(["status" => "error", "message" => "User not found."]);
            exit;
        }

        $updateStmt = $pdo->prepare("
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

        $profileStmt = $pdo->prepare("
            SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max
            FROM User
            WHERE username = :username
        ");
        $profileStmt->execute([":username" => $username]);
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
        $profile["age"] = calculateAge($profile["DoB"]);

        echo json_encode(["status" => "success", "message" => "Profile updated successfully.", "profile" => $profile]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}