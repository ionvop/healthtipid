<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            $query = <<<SQL
                SELECT * FROM `chats` WHERE `case_id` = :case_id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":case_id", $_GET["id"]);
            $result = $stmt->execute();
            $chats = [];

            while ($row = $result->fetchArray()) {
                $chats[] = $row;
            }

            echo json_encode([
                "chats" => $chats
            ]);

            exit;
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            $headers = getallheaders();
            $session = $headers["Authorization"];

            switch ($_POST["type"]) {
                case "patient":
                    $user = getUser($session);

                    $query = <<<SQL
                        INSERT INTO `chats` (`user_id`, `case_id`, `content`)
                        VALUES (:user_id, :case_id, :content)
                    SQL;

                    $stmt = $db->prepare($query);
                    $stmt->bindValue(":user_id", $user["id"]);
                    $stmt->bindValue(":case_id", $_POST["case_id"]);
                    $stmt->bindValue(":content", $_POST["content"]);
                    $stmt->execute();
                    break;
                case "doctor":
                    $doctor = getDoctor($session);

                    $query = <<<SQL
                        INSERT INTO `chats` (`doctor_id`, `case_id`, `content`)
                        VALUES (:doctor_id, :case_id, :content)
                    SQL;

                    $stmt = $db->prepare($query);
                    $stmt->bindValue(":doctor_id", $doctor["id"]);
                    $stmt->bindValue(":case_id", $_POST["case_id"]);
                    $stmt->bindValue(":content", $_POST["content"]);
                    $stmt->execute();
                    break;
            }

            echo json_encode([
                "success" => true
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