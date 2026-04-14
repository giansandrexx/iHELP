<?php
header("Content-Type: application/json");

require_once 'config.php';

$date = $_GET['date'];
$service_id = $_GET['service_id'];

$slots = [];
for ($h = 7; $h <= 21; $h++) {
    $slots[] = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00:00";
}

$stmt = $conn->prepare("
    SELECT booking_time FROM bookings
    WHERE service_id = ?
    AND booking_date = ?
    AND status != 'cancelled'
");

$stmt->bind_param("is", $service_id, $date);
$stmt->execute();
$res = $stmt->get_result();

$booked = [];
while ($row = $res->fetch_assoc()) {
    $booked[] = $row['booking_time'];
}

$available = array_values(array_diff($slots, $booked));

echo json_encode([
    "success" => true,
    "slots" => $available
]);
?>
