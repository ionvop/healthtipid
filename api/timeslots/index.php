<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    $query = <<<SQL
        DELETE FROM `timeslots`
        WHERE `end_time` < :current_time
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":current_time", time());
    $stmt->execute();

    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            $query = <<<SQL
                SELECT * FROM `timeslots`
                WHERE `doctor_id` = :doctor_id
                AND `case_id` IS NULL
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":doctor_id", $_GET["id"]);
            $result = $stmt->execute();
            $timeslots = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $timeslots[] = $row;
            }

            echo json_encode([
                "timeslots" => $timeslots
            ]);

            exit;
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);

            $query = <<<SQL
                UPDATE `timeslots`
                SET `case_id` = :case_id
                WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":case_id", $_POST["case_id"]);
            $stmt->bindValue(":id", $_POST["id"]);
            $stmt->execute();

            echo json_encode([
                "success" => true
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