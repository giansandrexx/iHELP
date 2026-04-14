<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$user_id     = $data['user_id'] ?? null;
$service_id  = $data['service_id'] ?? null;
$address_id  = $data['address_id'] ?? null;
$date        = $data['date'] ?? null;
$time        = $data['time'] ?? null;
$instructions= $data['instructions'] ?? "";

if (!$user_id || !$service_id || !$address_id || !$date || !$time) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

try {

    $check = $conn->prepare("
        SELECT id FROM bookings
        WHERE service_id = ?
        AND booking_date = ?
        AND booking_time = ?
        AND status != 'cancelled'
    ");

    $check->bind_param("iss", $service_id, $date, $time);
    $check->execute();
    $result = $check->get_result();

    $stmt = $conn->prepare("
        INSERT INTO bookings 
        (user_id, service_id, address_id, booking_date, booking_time, instructions)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("iiisss",
        $user_id,
        $service_id,
        $address_id,
        $date,
        $time,
        $instructions
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "booking_id" => $stmt->insert_id,
            "message" => "Booking created successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to create booking"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
