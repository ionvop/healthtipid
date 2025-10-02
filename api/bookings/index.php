<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            if (isset($_GET["id"])) {
                $query = <<<SQL
                    SELECT * FROM `timeslots` WHERE `id` = :id
                SQL;

                $stmt = $db->prepare($query);
                $stmt->bindValue(":id", $_GET["id"]);
                $timeslot = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

                echo json_encode([
                    "timeslot" => $timeslot
                ]);

                exit;
            }

            $headers = getallheaders();
            $session = $headers["Authorization"];
            $user = getUser($session);

            $query = <<<SQL
                SELECT `timeslots`.* FROM `timeslots`
                LEFT JOIN `cases` ON `cases`.`id` = `timeslots`.`case_id`
                LEFT JOIN `users` ON `users`.`id` = `cases`.`user_id`
                WHERE `users`.`id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":id", $user["id"]);
            $result = $stmt->execute();
            $timeslots = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $timeslots[] = $row;
            }

            echo json_encode([
                "timeslots" => $timeslots
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