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
            $fields = ["firstname", "lastname", "middleinitial", "birthdate", "gender", "height", "weight", "blood_type", "allergies", "chronic_conditions", "immunization_status", "major_illnesses", "current_medications", "family_medical_history", "smoking_status", "alcohol_use", "physical_activity", "dietary_habits"];
            $userInfo = "";

            foreach ($fields as $field) {
                if ($user[$field] == null) continue;

                if ($field == "height") {
                    $userInfo .= "Height: " . $user[$field] . " cm\n";
                    continue;
                }

                if ($field == "weight") {
                    $userInfo .= "Weight: " . $user[$field] . " kg\n";
                    continue;
                }

                $userInfo .= $field . ": " . $user[$field] . "\n";
            }

            $messages = [
                [
                    "role" => "system",
                    "content" => "You are a doctor and you are helping a patient with the following information:\n" . $userInfo
                ],
                [
                    "role" => "assistant",
                    "content" => "Hello, I'm your virtual health assistant. To start, please tell me your reason for this consultation?"
                ]
            ];

            $query = <<<SQL
                INSERT INTO `chats` (`user_id`, `content`)
                VALUES (:user_id, :content)
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":user_id", $user["id"]);
            $stmt->bindValue(":content", $_POST["content"]);
            $stmt->execute();
            $id = $db->lastInsertRowID();

            echo json_encode([
                "data" => $id
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