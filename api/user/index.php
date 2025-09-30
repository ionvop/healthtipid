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
            $stmt->bindValue(":id", $user["id"]);
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