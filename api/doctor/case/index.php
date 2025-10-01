<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
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

            echo json_encode([
                "case" => $case
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