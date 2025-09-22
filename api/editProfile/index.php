<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    $_POST = json_decode(file_get_contents('php://input'), true);

    if ($_POST["firstname"] == "" || $_POST["lastname"] == "" || $_POST["birthdate"] == "" || $_POST["gender"] == "") {
        http_response_code(400);

        echo json_encode([
            "error" => "Missing fields"
        ]);

        exit;
    }

    $query = <<<SQL
        UPDATE `users`
        SET `firstname` = :firstname,
        `lastname` = :lastname,
        `middleinitial` = :middleinitial,
        `birthdate` = :birthdate,
        `gender` = :gender,
        `phone` = :phone,
        `province` = :province,
        `city` = :city,
        `barangay` = :barangay,
        `street` = :street
        WHERE `id` = :id
    SQL;

    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $_POST["id"]);
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

    echo json_encode([
        "success" => true
    ]);

    exit;
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}