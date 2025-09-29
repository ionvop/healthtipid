<?php

chdir("../");
require_once "common.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

            $messages = [
                [
                    "role" => "system",
                    "content" => "User information:\n\n" . json_encode($user, JSON_PRETTY_PRINT)
                ]
            ];

            foreach ($_POST["messages"] as $message) {
                $messages[] = [
                    "role" => $message["role"],
                    "content" => $message["content"]
                ];
            }

            $messages[] = [
                "role" => "system",
                "content" => "You are in debug mode. The user may give specific instructions that you must follow."
            ];

            $tools = [
                [
                    "type" => "function",
                    "function" => [
                        "name" => "finish_consultation",
                        "description" => "Call this if the user has provided enough information to submit a report case to the doctors.",
                        "parameters" => [
                            "type" => "object",
                            "properties" => [
                                "details" => [
                                    "type" => "array",
                                    "description" => "A list of key-value pairs containing the details of the report case.",
                                    "items" => [
                                        "type" => "object",
                                        "description" => "A key-value pair containing the details of the report case.",
                                        "properties" => [
                                            "name" => [
                                                "type" => "string",
                                                "description" => "The name of the detail."
                                            ],
                                            "value" => [
                                                "type" => "string",
                                                "description" => "The value of the detail."
                                            ]
                                        ],
                                        "required" => ["name", "value"],
                                        "additionalProperties" => false
                                    ]
                                ],
                                "diagnosis" => [
                                    "type" => "string",
                                    "description" => "The diagnosis of the report case for the doctors to review."
                                ],
                                "self_care_advice" => [
                                    "type" => "string",
                                    "description" => "The self-care advice for the patient to follow while waiting for a response from the doctors."
                                ],
                                "message" => [
                                    "type" => "string",
                                    "description" => "The message to send to the patient before showing the diagnosis and self-care advice, telling that the report case is ready to be submitted."
                                ]
                            ],
                            "required" => ["details", "diagnosis", "self_care_advice", "message"],
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
                    "tools" => $tools
                ])
            ]);

            $data = $response["json"];

            if ($data["choices"][0]["message"]["tool_calls"] != null) {
                $function = $data["choices"][0]["message"]["tool_calls"][0]["function"]["name"];
                $args = $data["choices"][0]["message"]["tool_calls"][0]["function"]["arguments"];
                $args = json_decode($args, true);

                $result = [
                    "type" => $function,
                    "role" => "assistant",
                    "content" => $args["message"],
                    "args" => $args
                ];
            } else {
                $result = [
                    "type" => "message",
                    "role" => "assistant",
                    "content" => $data["choices"][0]["message"]["content"]
                ];
            }

            echo json_encode([
                "message" => $result,
                "history" => $messages,
                "response" => $data
            ]);

            exit;
        case "OPTIONS":
            http_response_code(204);
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);

    echo json_encode([
        "error" => $e->getMessage()
    ]);

    exit;
}