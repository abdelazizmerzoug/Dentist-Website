<?php
$conn = new mysqli('localhost', 'root', '', 'dentalclinic');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Collect data from POST
$product_name = $_POST['product_name'] ?? '';
$category_id = $_POST['category_id'] ?? null;
$unit_price = $_POST['unit_price'] ?? 0;
$quantity = $_POST['quantity'] ?? 0;
$expiry_date = $_POST['expiry_date'] ?? '';
$supplier_id = $_POST['supplier_id'] ?? null;
$total_cost = $_POST['total_cost'] ?? 0;

// Handle file upload
$photo = '';
if (!empty($_FILES['photo']['name'])) {
    $upload_dir = './uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Ensure the directory exists
    }
    $photo = $upload_dir . basename($_FILES['photo']['name']);
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
        echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
        exit();
    }
}

// Validate inputs
if (!$product_name || !$category_id || !$unit_price || !$quantity || !$expiry_date || !$supplier_id || !$total_cost) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

if (!DateTime::createFromFormat('Y-m-d', $expiry_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid expiry date format.']);
    exit();
}

// Check if the product already exists
$product_check_query = $conn->prepare("
    SELECT id_product, quantity FROM products 
    WHERE product_name = ? AND category_id = ? AND supplier_id = ? AND date_exp = ?
");
$product_check_query->bind_param('siis', $product_name, $category_id, $supplier_id, $expiry_date);

if (!$product_check_query->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Query execution failed: ' . $conn->error]);
    exit();
}

$product_check_result = $product_check_query->get_result();
if ($product_check_result->num_rows > 0) {
    // Product exists: Update quantity and insert into purch_orders
    $product = $product_check_result->fetch_assoc();
    $new_quantity = $product['quantity'] + $quantity;

    $update_query = $conn->prepare("
        UPDATE products SET quantity = ?, unit_price = ? WHERE id_product = ?
    ");
    $update_query->bind_param('idi', $new_quantity, $unit_price, $product['id_product']);
    if (!$update_query->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update product: ' . $conn->error]);
        exit();
    }

    $insert_purch_order_query = $conn->prepare("
        INSERT INTO purch_orders (id_product, photo, total_cost) 
        VALUES (?, ?, ?)
    ");
    $insert_purch_order_query->bind_param('isd', $product['id_product'], $photo, $total_cost);
    if ($insert_purch_order_query->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated and purchase order added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add purchase order: ' . $conn->error]);
    }
} else {
    // Product does not exist: Insert into products and purch_orders
    $insert_product_query = $conn->prepare("
        INSERT INTO products (product_name, category_id, unit_price, quantity, date_exp, supplier_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert_product_query->bind_param('sidisi', $product_name, $category_id, $unit_price, $quantity, $expiry_date, $supplier_id);

    if ($insert_product_query->execute()) {
        $id_product = $insert_product_query->insert_id;

        $insert_purch_order_query = $conn->prepare("
            INSERT INTO purch_orders (id_product, photo, total_cost) 
            VALUES (?, ?, ?)
        ");
        $insert_purch_order_query->bind_param('isd', $id_product, $photo, $total_cost);

        if ($insert_purch_order_query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product and purchase order added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add purchase order: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product: ' . $conn->error]);
    }
}

$conn->close();
?>
