<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = "metro.proxy.rlwy.net";
$port = 15753;
$db   = "railway";
$user = "root";
$pass = "trJFAuDlcDgVeYuZXpIZuQaeJETOsXGX";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "DB connection failed: " . $conn->connect_error
    ]));
}
?>
