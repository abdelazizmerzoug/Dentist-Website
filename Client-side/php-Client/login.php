<?php
// Database connection
include("./db.php");

// Login logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['Email']);
    $password = $conn->real_escape_string($_POST['Password']);

    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        // Redirect based on role
        switch ($user['role']) {
            case 'client':
                header("Location: ../html-Client/About-2.html");
                break;
            case 'dentist':
                header("Location: ../php-Client/dynamic-full-calendar.php");
                break;
            case 'assistant':
                header("Location: ../php-Client/dynamic-full-calendar.php");
                break;
            default:
                header("Location: ../html-Client/login.html");
        }
        exit;
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }
}

$conn->close();
?>
