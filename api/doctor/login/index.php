<?php

chdir("../../");
require_once "common.php";
header("Content-Type: application/json");

try {
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "POST":
            $_POST = json_decode(file_get_contents('php://input'), true);

            if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) == false) {
                http_response_code(400);

                echo json_encode([
                    "error" => "Invalid email"
                ]);

                exit;
            }

            $code = substr(md5(time()), 0, 5);
            session_start();
            $_SESSION["code"] = $code;
            $_SESSION["email"] = $_POST["email"];

            $response = fetch("https://api.brevo.com/v3/smtp/email", [
                "method" => "POST",
                "headers" => [
                    "Content-Type" => "application/json",
                    "Accept" => "application/json",
                    "Api-Key" => $BREVO_API_KEY
                ],
                "body" => json_encode([
                    "sender" => [
                        "name" => "HealthTipid",
                        "email" => "ionvop@gmail.com"
                    ],
                    "to" => [
                        [
                            "email" => $_POST["email"]
                        ]
                    ],
                    "textContent" => "Your doctor login code is: {$code}\n\nIf you did not request this code, you can safely ignore this email.",
                    "subject" => "HealthTipid Doctor login code"
                ])
            ]);

            echo json_encode([
                "message" => "Email sent"
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