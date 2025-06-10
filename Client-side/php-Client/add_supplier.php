<?php
header('Content-Type: application/json');

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = ""; // Replace with your MySQL root password if set
$dbname = "dentalclinic";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form input
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);

    // Check if phone number already exists
    $check_sql = "SELECT * FROM suppliers WHERE phone = '$phone'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Duplicate entry notification
        echo json_encode(['status' => 'error', 'message' => 'This phone number is already registered.']);
    } else {
        // Insert the new supplier
        $sql = "INSERT INTO suppliers (first_name, last_name, phone, email) 
                VALUES ('$first_name', '$last_name', '$phone', '$email')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['status' => 'success', 'message' => 'New supplier added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the database connection
$conn->close();
?>
