<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Decode input data
$data = json_decode(file_get_contents('php://input'), true);

// Database connection
$conn = new mysqli("localhost", "root", "", "dentalclinic");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Validate input
$id_product = $data['id_product'] ?? null;
$increment = $data['increment'] ?? null;

if (!$id_product || !is_numeric($increment)) {
    error_log("Invalid input: " . print_r($data, true));
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit();
}

// Update product quantity
$query = "UPDATE products SET quantity = quantity + ? WHERE id_product = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Failed to prepare statement: " . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param('ii', $increment, $id_product);

if ($stmt->execute()) {
    // Fetch updated quantity
    $query = "SELECT quantity FROM products WHERE id_product = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_product);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'message' => 'Quantity updated successfully',
            'quantity' => $row['quantity']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch updated quantity'
        ]);
    }
} else {
    error_log("Failed to execute update query: " . $stmt->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update quantity']);
}

// Close connection
$stmt->close();
$conn->close();
?>
