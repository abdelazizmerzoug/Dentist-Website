<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dentalclinic";

// Create a new mysqli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set the charset to avoid character set issues
$conn->set_charset("utf8");
?>
