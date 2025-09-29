<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            $user = getUser($_POST["session"]);

            $query = <<<SQL
                SELECT * FROM `chats` WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":id", $_POST["id"]);
            $chat = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if ($chat == false) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Chat not found"
                ]);

                exit;
            }

            echo json_encode([
                "data" => $chat
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