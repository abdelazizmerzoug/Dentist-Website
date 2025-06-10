<?php
header('Content-Type: application/json');

try {
    // Database connection
    require_once 'db.php'; // Ensure `db.php` contains the correct connection logic.
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit;
    }

    // Decode input data
    $data = json_decode(file_get_contents('php://input'), true);
    $doctor_id = $data['doctor_id'] ?? null;
    $date = $data['date'] ?? null;

    // Validate input data
    if (!$doctor_id || !$date) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
        exit;
    }

    // Define working hours and slots
    $slots = [
        "08:00:00", "09:00:00", "10:00:00", "11:00:00",
        "13:00:00", "14:00:00", "15:00:00"
    ];

    // Fetch already booked slots
    $query = "SELECT appointment_time FROM appointments WHERE id_doctor = ? AND appointment_date = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("is", $doctor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_slots = [];
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['appointment_time'];
    }

    // Determine available slots
    $available_slots = array_diff($slots, $booked_slots);

    // Send response
    echo json_encode(['status' => 'success', 'slots' => array_values($available_slots)]);
} catch (Exception $e) {
    // Handle unexpected errors
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
} finally {
    // Ensure database resources are closed
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
