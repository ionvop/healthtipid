<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            session_start();

            if ($_POST["fullname"] == "" || $_POST["email"] == "") {
                http_response_code(400);

                echo json_encode([
                    "error" => "Missing fields"
                ]);

                exit;
            }

            $query = <<<SQL
                INSERT INTO `doctors` (`fullname`, `email`, `phone`, `license_number`, `professional_title`, `years_of_experience`, `description`)
                VALUES (:fullname, :email, :phone, :license_number, :professional_title, :years_of_experience, :description)
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":fullname", $_POST["fullname"]);
            $stmt->bindValue(":email", $_POST["email"]);
            $stmt->bindValue(":phone", $_POST["phone"]);
            $stmt->bindValue(":license_number", $_POST["license"]);
            $stmt->bindValue(":professional_title", $_POST["title"]);
            $stmt->bindValue(":years_of_experience", $_POST["experience"]);
            $stmt->bindValue(":description", $_POST["bio"]);
            $stmt->execute();
            $session = uniqid("session-");

            $query = <<<SQL
                UPDATE `doctors` SET `session` = :session WHERE `email` = :email
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":session", $session);
            $stmt->bindValue(":email", $_SESSION["email"]);
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