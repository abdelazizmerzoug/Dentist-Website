<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('./db.php');

$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 'all';

try {
    // Base query
    $query = "SELECT 
                a.id_appointment, 
                p.first_name AS patient_first_name, 
                p.last_name AS patient_last_name, 
                p.phone, 
                p.birth_date, 
                d.id_doctor, 
                d.first_name AS doctor_first_name, 
                d.last_name AS doctor_last_name, 
                a.appointment_date, 
                a.appointment_time
              FROM appointments a
              JOIN patients p ON a.id_patient = p.id_patient
              JOIN doctors d ON a.id_doctor = d.id_doctor
              WHERE a.confirmation = 1"; // Only include confirmed appointments

    if ($doctorId !== 'all') {
        $query .= " AND a.id_doctor = ?";
    }

    $stmt = mysqli_prepare($conn, $query);

    if ($doctorId !== 'all') {
        mysqli_stmt_bind_param($stmt, 'i', $doctorId);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        throw new Exception("Query error: " . mysqli_error($conn));
    }

    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate patient's age
        $birthDate = new DateTime($row['birth_date']);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDate)->y;

        // Calculate start and end times
        $startDateTime = $row['appointment_date'] . 'T' . $row['appointment_time'];
        $endDateTime = date('Y-m-d\TH:i:s', strtotime($startDateTime . ' +1 hour'));

        // Determine the color based on whether the slot is past or upcoming
        $currentDateTime = new DateTime();
        $appointmentDateTime = new DateTime($startDateTime);
        $color = $appointmentDateTime < $currentDateTime ? '#FF0000' : '#28a745'; // Red for past, green for future

        $events[] = [
            'id' => $row['id_appointment'],
            'title' => $row['patient_first_name'] . ' ' . $row['patient_last_name'] . ' (' . $age . ' years) - ' . $row['phone'],
            'start' => $startDateTime,
            'end' => $endDateTime,
            'color' => $color, // Dynamically set color
            'textColor' => '#ffffff',
            'doctor_id' => $row['id_doctor']
        ];
    }

    echo json_encode($events);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    http_response_code(500);
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>
