<?php

$server = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "ccsync_api";
// $server = "db.fr-pari1.bengt.wasmernet.com";
// $port = 10272;
// $username = "d3c8ccd17be68000cffc637f009d";
// $password = "068ed3c8-ccd1-7da7-8000-6cc3cd656ec2";
// $database = "ccsync_db";

try {
    $conn = new PDO("mysql:host=$server;port=$port;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    error_log("Database connected successfully");
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Connection OK\n", FILE_APPEND);
} catch (\Throwable $th) {
    error_log("Connection failed: " . $th->getMessage());
    file_put_contents("debug.log", date('Y-m-d H:i:s') . " - Connection failed: " . $th->getMessage() . "\n", FILE_APPEND);

    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "MySQL database connection failed"
    ]);
    exit();
}
