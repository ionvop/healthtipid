<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    $_POST = json_decode(file_get_contents('php://input'), true);
    session_start();

    if ($_POST["firstname"] == "" || $_POST["lastname"] == "" || $_POST["birthdate"] == "" || $_POST["gender"] == "") {
        http_response_code(400);

        echo json_encode([
            "error" => "Missing fields"
        ]);

        exit;
    }

    $query = <<<SQL
        INSERT INTO `users` (`email`, `firstname`, `lastname`, `middleinitial`, `birthdate`, `gender`, `email`, `phone`, `province`, `city`, `barangay`, `street`)
        VALUES (:email, :firstname, :lastname, :middleinitial, :birthdate, :gender, :email, :phone, :province, :city, :barangay, :street)
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $stmt->bindValue(":firstname", $_POST["firstname"]);
    $stmt->bindValue(":lastname", $_POST["lastname"]);
    $stmt->bindValue(":middleinitial", $_POST["middleinitial"]);
    $stmt->bindValue(":birthdate", $_POST["birthdate"]);
    $stmt->bindValue(":gender", $_POST["gender"]);
    $stmt->bindValue(":phone", $_POST["phone"]);
    $stmt->bindValue(":province", $_POST["province"]);
    $stmt->bindValue(":city", $_POST["city"]);
    $stmt->bindValue(":barangay", $_POST["barangay"]);
    $stmt->bindValue(":street", $_POST["street"]);
    $stmt->execute();
    $session = uniqid("session-");

    $query = <<<SQL
        UPDATE `users` SET `session` = :session WHERE `email` = :email
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":session", $session);
    $stmt->bindValue(":email", $_SESSION["email"]);
    $stmt->execute();

    echo json_encode([
        "session" => $session
    ]);

    exit;
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}