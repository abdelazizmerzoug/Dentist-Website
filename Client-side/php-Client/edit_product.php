<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "dentalclinic");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id_product'] ?? '';
$product_name = $data['product_name'] ?? '';
$unit_price = $data['unit_price'] ?? '';
$date_exp = $data['date_exp'] ?? '';
$supplier = $data['supplier'] ?? '';
$category = $data['category'] ?? '';
$quantity = $data['quantity'] ?? '';

// Update query
$sql = "UPDATE products SET 
        product_name = ?, unit_price = ?, date_exp = ?, quantity = ?
        WHERE id_product = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sdsii", $product_name, $unit_price, $date_exp, $quantity, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update product.']);
}
$conn->close();
?>
