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
            $user = getUser($session);

            if ($user == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "User not found"
                ]);

                exit;
            }

            $query = <<<SQL
                SELECT * FROM `cases` WHERE `id` = :id
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":id", $_GET["id"]);
            $case = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

            if ($case == false) {
                http_response_code(403);

                echo json_encode([
                    "error" => "Case not found"
                ]);

                exit;
            }

            $query = <<<SQL
                SELECT * FROM `timeslots`
                WHERE `start_time` > :current_time
                GROUP BY `doctor_id`
            SQL;

            $stmt = $db->prepare($query);
            $stmt->bindValue(":current_time", time());
            $result = $stmt->execute();
            $doctors = [];
            $doctorIds = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $doctorIds[] = $row["doctor_id"];

                $query = <<<SQL
                    SELECT * FROM `doctors` WHERE `id` = :id
                SQL;

                $stmt = $db->prepare($query);
                $stmt->bindValue(":id", $row["doctor_id"]);
                $doctor = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                $doctors[] = $doctor;
            }

            if (count($doctors) == 0) {
                http_response_code(404);

                echo json_encode([
                    "error" => "No doctors available"
                ]);

                exit;
            }

            $case["user_details"] = $user;
            $prompt = file_get_contents("assets/prompt.md");

            $messages = [
                [
                    "role" => "system",
                    "content" => $prompt
                ],
                [
                    "role" => "system",
                    "content" => "Case information:\n\n" . json_encode($case, JSON_PRETTY_PRINT)
                ],
                [
                    "role" => "system",
                    "content" => "Doctors available:\n\n" . json_encode($doctors, JSON_PRETTY_PRINT)
                ],
                [
                    "role" => "system",
                    "content" => "This project is still in development so the records may only contain testing data and accounts."
                ]
            ];

            $tools = [
                [
                    "type" => "function",
                    "function" => [
                        "name" => "suggest_doctors",
                        "description" => "Pick at most 3 doctors for the user to choose from based on their specialization and the details of the consultation.",
                        "parameters" => [
                            "type" => "object",
                            "properties" => [
                                "doctors" => [
                                    "type" => "array",
                                    "description" => "A list of doctors to suggest.",
                                    "items" => [
                                        "type" => "object",
                                        "description" => "A doctor to suggest.",
                                        "properties" => [
                                            "id" => [
                                                "type" => "integer",
                                                "description" => "The id of the doctor.",
                                                "enum" => $doctorIds
                                            ],
                                            "remarks" => [
                                                "type" => "string",
                                                "description" => "Reason why you recommend this doctor."
                                            ]
                                        ],
                                        "required" => ["id", "remarks"],
                                        "additionalProperties" => false
                                    ],
                                    "maxItems" => 3
                                ],
                                "remarks" => [
                                    "type" => "string",
                                    "description" => "Say something about your recommendations."
                                ]
                            ],
                            "required" => ["doctors", "remarks"],
                            "additionalProperties" => false
                        ],
                        "strict" => true
                    ]
                ]
            ];

            $response = fetch("https://api.openai.com/v1/chat/completions", [
                "method" => "POST",
                "headers" => [
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer " . $OPENAI_API_KEY
                ],
                "body" => json_encode([
                    "model" => "gpt-4o-mini",
                    "messages" => $messages,
                    "tools" => $tools,
                    "tool_choice" => [
                        "type" => "function",
                        "function" => [
                            "name" => "suggest_doctors"
                        ]
                    ]
                ])
            ]);

            file_put_contents("logs/" . date("Y-m-d_H-i-s") . ".json", json_encode([
                "model" => "gpt-4o-mini",
                "messages" => $messages,
                "tools" => $tools,
                "tool_choice" => [
                    "type" => "function",
                    "function" => [
                        "name" => "suggest_doctors"
                    ]
                ]
            ]), JSON_PRETTY_PRINT);

            file_put_contents("logs/" . date("Y-m-d_H-i-s") . ".json", json_encode($response, JSON_PRETTY_PRINT));
            $data = $response["json"];
            $args = $data["choices"][0]["message"]["tool_calls"][0]["function"]["arguments"];
            $args = json_decode($args, true);
            $doctors = [];

            foreach ($args["doctors"] as $index => $doctorSuggestion) {
                $doctorId = $doctorSuggestion["id"];

                $query = <<<SQL
                    SELECT * FROM `doctors` WHERE `id` = :id
                SQL;

                $stmt = $db->prepare($query);
                $stmt->bindValue(":id", $doctorId);
                $doctor = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                $args["doctors"][$index]["details"] = $doctor;
            }

            echo json_encode([
                "suggestions" => $args,
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