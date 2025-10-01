<?php

chdir("../../");
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
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $doctor = getDoctor($session);

            if ($doctor == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Doctor not found"
                ]);
                
                exit;
            }

            $query = <<<SQL
                SELECT * FROM `timeslots` WHERE `doctor_id` = :doctor_id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":doctor_id", $doctor["id"]);
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
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $doctor = getDoctor($session);

            if ($doctor == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Doctor not found"
                ]);
                
                exit;
            }

            $query = <<<SQL
                INSERT INTO `timeslots` (`doctor_id`, `start_time`, `end_time`)
                VALUES (:doctor_id, :start_time, :end_time)
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":doctor_id", $doctor["id"]);
            $stmt->bindValue(":start_time", $_POST["start_time"]);
            $stmt->bindValue(":end_time", $_POST["end_time"]);
            $stmt->execute();

            $query = <<<SQL
                SELECT * FROM `timeslots` WHERE `doctor_id` = :doctor_id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":doctor_id", $doctor["id"]);
            $result = $stmt->execute();
            $timeslots = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $timeslots[] = $row;
            }

            echo json_encode([
                "timeslots" => $timeslots
            ]);

            exit;
        case "DELETE":
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $doctor = getDoctor($session);

            if ($doctor == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Doctor not found"
                ]);
                
                exit;
            }

            $query = <<<SQL
                DELETE FROM `timeslots` WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":id", $_GET["id"]);
            $stmt->execute();

            $query = <<<SQL
                SELECT * FROM `timeslots` WHERE `doctor_id` = :doctor_id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":doctor_id", $doctor["id"]);
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