<?php

chdir("../");
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

            echo json_encode([
                "doctor" => $doctor
            ]);

            exit;
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);
            $headers = getallheaders();
            $session = $headers["Authorization"];
            $doctor = getDoctor($session);

            if ($doctor == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            $query = <<<SQL
                UPDATE `doctors`
                SET `fullname` = :fullname,
                `phone` = :phone,
                `license_number` = :license_number,
                `professional_title` = :professional_title,
                `years_of_experience` = :years_of_experience,
                `description` = :description
                WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":fullname", $_POST["fullname"]);
            $stmt->bindValue(":phone", $_POST["phone"]);
            $stmt->bindValue(":license_number", $_POST["license"]);
            $stmt->bindValue(":professional_title", $_POST["title"]);
            $stmt->bindValue(":years_of_experience", $_POST["experience"]);
            $stmt->bindValue(":description", $_POST["bio"]);
            $stmt->bindValue(":id", $doctor["id"]);
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