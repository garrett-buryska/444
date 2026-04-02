<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

function respond($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function calculateAge($dob) {
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
        $username = $_GET["username"] ?? "";

        if ($username === "") {
            respond(["status" => "error", "message" => "Username is required."], 400);
        }

        $stmt = $pdo->prepare("
            SELECT username, name, img_url, weight, height, skill_level, DoB, bench_max, squat_max, deadlift_max
            FROM User
            WHERE username = :username
        ");
        $stmt->execute([":username" => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            respond(["status" => "error", "message" => "User not found."], 404);
        }

        $user["age"] = calculateAge($user["DoB"]);

        respond([
            "status" => "success",
            "profile" => $user
        ]);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || empty($data["username"])) {
            respond(["status" => "error", "message" => "Username is required."], 400);
        }

        $username = trim($data["username"]);
        $name = trim($data["name"] ?? "");
        $imgUrl = trim($data["img_url"] ?? "");
        $weight = isset($data["weight"]) && $data["weight"] !== "" ? $data["weight"] : null;
        $height = isset($data["height"]) && $data["height"] !== "" ? $data["height"] : null;
        $skillLevel = trim($data["skill_level"] ?? "");
        $dob = trim($data["DoB"] ?? "");

        $existsStmt = $pdo->prepare("SELECT username FROM User WHERE username = :username");
        $existsStmt->execute([":username" => $username]);

        if (!$existsStmt->fetchColumn()) {
            respond(["status" => "error", "message" => "User not found."], 404);
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

        respond([
            "status" => "success",
            "message" => "Profile updated successfully.",
            "profile" => $profile
        ]);
    }

    respond(["status" => "error", "message" => "Method not allowed."], 405);
} catch (PDOException $e) {
    respond(["status" => "error", "message" => "Database error: " . $e->getMessage()], 500);
}
?>
