<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            $query = <<<SQL
                SELECT * FROM `users` WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":id", $_GET["id"]);
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