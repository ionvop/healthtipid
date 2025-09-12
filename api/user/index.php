<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");

try {
    $db = new SQLite3("database.db");
    $_POST = json_decode(file_get_contents('php://input'), true);

    $query = <<<SQL
        SELECT * FROM `users` WHERE `session` = :session
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $_POST["session"]);
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
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}