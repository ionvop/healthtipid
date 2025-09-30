<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $user = getUser($session);

            if ($user == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            if (isset($_GET["id"])) {
                $query = <<<SQL
                    SELECT * FROM `cases` WHERE `id` = :id
                SQL;

                $stmt = $db->prepare($query);
                $stmt->bindValue(":id", $_GET["id"]);
                $case = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

                if ($case == false) {
                    http_response_code(404);

                    echo json_encode([
                        "error" => "Case not found"
                    ]);

                    exit;
                }

                if ($case["user_id"] != $user["id"]) {
                    http_response_code(403);

                    echo json_encode([
                        "error" => "Case not found"
                    ]);

                    exit;
                }

                echo json_encode([
                    "case" => $case
                ]);

                exit;
            }

            $query = <<<SQL
                SELECT * FROM `cases` WHERE `user_id` = :user_id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":user_id", $user["id"]);
            $result = $stmt->execute();
            $cases = [];

            while ($case = $result->fetchArray(SQLITE3_ASSOC)) {
                $cases[] = $case;
            }

            echo json_encode([
                "cases" => $cases
            ]);

            exit;
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $user = getUser($session);

            if ($user == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            $query = <<<SQL
                INSERT INTO `cases` (`user_id`, `details`, `urgency`, `preliminary_impression`, `self_care_advice`)
                VALUES (:user_id, :details, :urgency, :preliminary_impression, :self_care_advice)
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":user_id", $user["id"]);
            $stmt->bindValue(":details", json_encode($_POST["details"]));
            $stmt->bindValue(":urgency", $_POST["urgency"]);
            $stmt->bindValue(":preliminary_impression", $_POST["preliminary_impression"]);
            $stmt->bindValue(":self_care_advice", $_POST["self_care_advice"]);
            $stmt->execute();

            echo json_encode([
                "id" => $db->lastInsertRowID()
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