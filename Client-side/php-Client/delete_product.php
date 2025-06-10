<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Connect to database
$conn = new mysqli("localhost", "root", "", "dentalclinic");
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Decode input data
$data = json_decode(file_get_contents('php://input'), true);
error_log("Received delete request: " . print_r($data, true));

// Validate input
$id = $data['id_product'] ?? null;
if (!$id || !is_numeric($id)) {
    error_log("Invalid product ID received: " . print_r($data, true));
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit();
}
error_log("Parsed product ID: $id");

// First, delete related records in purch_orders
$sqlDeleteOrders = "DELETE FROM purch_orders WHERE id_product = ?";
$stmtOrders = $conn->prepare($sqlDeleteOrders);
if (!$stmtOrders) {
    error_log("Failed to prepare delete orders statement: " . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare delete orders statement']);
    exit();
}
$stmtOrders->bind_param("i", $id);
if (!$stmtOrders->execute()) {
    error_log("Failed to execute delete orders query: " . $stmtOrders->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete related orders']);
    $stmtOrders->close();
    $conn->close();
    exit();
}
$stmtOrders->close();

// Now, delete the product itself
$sql = "DELETE FROM products WHERE id_product = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Failed to prepare delete product statement: " . $conn->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare delete product statement']);
    exit();
}
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        error_log("Product deleted successfully: ID $id");
        echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully']);
    } else {
        error_log("No rows affected. Product ID not found: $id");
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
} else {
    error_log("Failed to execute delete product query: " . $stmt->error);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
}

// Close connection
$stmt->close();
$conn->close();
?>
