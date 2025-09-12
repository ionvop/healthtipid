<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$_POST = json_decode(file_get_contents('php://input'), true);

try {
    $db = new SQLite3("database.db");
    session_start();

    if ($_POST["code"] != $_SESSION["code"]) {
        http_response_code(400);

        echo json_encode([
            "error" => "Invalid code"
        ]);

        exit;
    }

    $query = <<<SQL
        SELECT * FROM `users` WHERE `email` = :email
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user == false) {
        http_response_code(404);

        echo json_encode([
            "error" => "User not found"
        ]);

        exit;
    }

    $session = uniqid("session-");

    $query = <<<SQL
        UPDATE `users` SET `session` = :session WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $session);
    $stmt->bindValue(":id", $user["id"]);
    $stmt->execute();

    echo json_encode([
        "session" => $session
    ]);
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}