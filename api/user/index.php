<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            $headers = getallheaders();
            $session = $headers["Authorization"];
            
            $query = <<<SQL
                SELECT * FROM `users` WHERE `session` = :session
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":session", $session);
            $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if ($user == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "User not found"
                ]);
                
                exit;
            }

            echo json_encode([
                "user" => $user
            ]);

            exit;
        case "OPTIONS":
            http_response_code(204);
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}