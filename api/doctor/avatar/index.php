<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $doctor = getDoctor($session);

            if ($doctor == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            if ($_FILES["avatar"]["error"] != 0) {
                http_response_code(400);

                echo json_encode([
                    "error" => "Failed to upload file"
                ]);

                exit;
            }

            $filename = uniqid("avatar-") . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], "uploads/avatar/" . $filename) == false) {
                http_response_code(500);

                echo json_encode([
                    "error" => "Failed to move file"
                ]);

                exit;
            }

            $query = <<<SQL
                UPDATE `doctors`
                SET `avatar` = :avatar
                WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":avatar", $filename);
            $stmt->bindValue(":id", $doctor["id"]);
            $stmt->execute();

            echo json_encode([
                "message" => "Avatar updated"
            ]);

            exit;
        default:
            http_response_code(405);

            echo json_encode([
                "error" => "Method not allowed"
            ]);

            exit;
    }
} catch (Exception $e) {
    http_response_code(500);
}