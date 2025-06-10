<?php
if (isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'dentalclinic');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $result = $conn->query("SELECT id_patient, first_name, last_name, phone FROM patients WHERE id_patient = '$patient_id'");

    $response = [];
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['patientOptions'] = "<option value='{$row['id_patient']}'>{$row['first_name']} {$row['last_name']} (ID: {$row['id_patient']})</option>";
        $response['phoneNumber'] = $row['phone'];
    } else {
        $response['success'] = false;
    }

    echo json_encode($response);
    $conn->close();
}
?>
