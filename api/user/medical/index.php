<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");
$db = new SQLite3("database.db");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $user = getUser($session);

            if ($user == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            $query = <<<SQL
                UPDATE `users`
                SET `height` = :height,
                `weight` = :weight,
                `blood_type` = :blood_type,
                `allergies` = :allergies,
                `chronic_conditions` = :chronic_conditions,
                `immunization_status` = :immunization_status,
                `major_illnesses` = :major_illnesses,
                `current_medications` = :current_medications,
                `family_medical_history` = :family_medical_history,
                `smoking_status` = :smoking_status,
                `alcohol_use` = :alcohol_use,
                `physical_activity` = :physical_activity,
                `dietary_habits` = :dietary_habits,
                `last_updated` = :last_updated
                WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":height", $_POST["height"] + 0);
            $stmt->bindValue(":weight", $_POST["weight"] + 0);
            $stmt->bindValue(":blood_type", $_POST["blood_type"]);
            $stmt->bindValue(":allergies", $_POST["allergies"]);
            $stmt->bindValue(":chronic_conditions", $_POST["chronic_conditions"]);
            $stmt->bindValue(":immunization_status", $_POST["immunization_status"]);
            $stmt->bindValue(":major_illnesses", $_POST["major_illnesses"]);
            $stmt->bindValue(":current_medications", $_POST["current_medications"]);
            $stmt->bindValue(":family_medical_history", $_POST["family_medical_history"]);
            $stmt->bindValue(":smoking_status", $_POST["smoking_status"]);
            $stmt->bindValue(":alcohol_use", $_POST["alcohol_use"]);
            $stmt->bindValue(":physical_activity", $_POST["physical_activity"]);
            $stmt->bindValue(":dietary_habits", $_POST["dietary_habits"]);
            $stmt->bindValue(":last_updated", time());
            $stmt->bindValue(":id", $user["id"]);
            $stmt->execute();

            echo json_encode([
                "success" => true
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