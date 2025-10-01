<?php

chdir("../../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            session_start();

            if ($_POST["code"] != $_SESSION["code"]) {
                http_response_code(400);

                echo json_encode([
                    "error" => "Invalid code"
                ]);

                exit;
            }

            $query = <<<SQL
                SELECT * FROM `doctors` WHERE `email` = :email
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":email", $_SESSION["email"]);
            $doctor = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if ($doctor == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            $session = uniqid("session-");

            $query = <<<SQL
                UPDATE `doctors` SET `session` = :session WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":session", $session);
            $stmt->bindValue(":id", $doctor["id"]);
            $stmt->execute();

            echo json_encode([
                "session" => $session
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

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}