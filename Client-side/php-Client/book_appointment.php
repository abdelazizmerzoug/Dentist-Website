<?php
header('Content-Type: application/json');

// Database connection
require_once 'db.php'; // Ensure `db.php` contains the correct connection logic.

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Collect form data
$first_name = trim($_POST['first_name'] ?? null);
$last_name = trim($_POST['last_name'] ?? null);
$birth_date = $_POST['birth_date'] ?? null;
$phone = trim($_POST['phone'] ?? null);
$email = trim($_POST['email'] ?? null);
$doctor_id = intval($_POST['doctor'] ?? 0);
$date = $_POST['appointment_date'] ?? null;
$time = $_POST['appointment_slot'] ?? null;
$reason = trim($_POST['reason'] ?? null); // Collect the reason for the appointment

// Validate input
if (!$first_name || !$last_name || !$phone || !$email || !$doctor_id || !$date || !$time || !$reason) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

try {
    // Step 1: Retrieve or Create Patient
    $patient_query = "SELECT id_patient FROM patients WHERE phone = ? OR email = ?";
    $stmt = $conn->prepare($patient_query);
    $stmt->bind_param("ss", $phone, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Patient exists
        $patient_row = $result->fetch_assoc();
        $patient_id = $patient_row['id_patient'];
    } else {
        // Insert new patient
        $insert_patient_query = "INSERT INTO patients (first_name, last_name, birth_date, phone, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_patient_query);
        $stmt->bind_param("sssss", $first_name, $last_name, $birth_date, $phone, $email);
        if ($stmt->execute()) {
            $patient_id = $stmt->insert_id;
        } else {
            throw new Exception("Failed to add new patient: " . $stmt->error);
        }
    }

    // Step 2: Insert Appointment
    $appointment_query = "INSERT INTO appointments (id_patient, id_doctor, appointment_date, appointment_time, reason, confirmation) 
                          VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($appointment_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare appointment query: " . $conn->error);
    }
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $reason);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
    } else {
        throw new Exception("Failed to book appointment: " . $stmt->error);
    }
} catch (Exception $e) {
    // Handle errors
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Cleanup resources
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
