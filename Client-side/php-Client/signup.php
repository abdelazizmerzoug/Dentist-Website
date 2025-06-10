<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "dentalclinic");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Signup logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $conn->real_escape_string($_POST['Username']);
    $email = $conn->real_escape_string($_POST['Email']);
    $password = $conn->real_escape_string($_POST['Password']);

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered');</script>";
    } else {
        // Insert new user
        $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'client')";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Account created successfully'); window.location.href = 'login.html';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}

$conn->close();
?>
