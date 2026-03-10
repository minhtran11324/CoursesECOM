<?php
include 'config/db.php';

// Nhận dữ liệu từ fetch (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['customer']) || !isset($data['cart'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$customer = $data['customer'];
$cart     = $data['cart'];

// Validate cơ bản
$fullname = trim($customer['fullname']);
$phone    = trim($customer['phone']);
$address  = trim($customer['address']);
$email    = trim($customer['email']);

if (empty($fullname) || empty($phone) || empty($address)) {
    echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin khách hàng"]);
    exit;
}

// Tính tổng đơn hàng
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$conn->begin_transaction();

try {
    // Thêm đơn hàng
    $stmt = $conn->prepare("INSERT INTO orders (fullname, phone, address, email, total_price, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssd", $fullname, $phone, $address, $email, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Chuẩn bị câu lệnh
    $stmt_item   = $conn->prepare("INSERT INTO order_items (order_id, product_id, price, quantity, subtotal) 
                               VALUES (?, ?, ?, ?, ?)");
    $stmt_update = $conn->prepare("UPDATE product SET quantity = quantity - ? WHERE id = ?");

    foreach ($cart as $item) {
        $product_id = isset($item['id']) ? (int)$item['id'] : null;
        $price      = (float)$item['price'];
        $quantity   = (int)$item['quantity'];
        $subtotal   = $price * $quantity;
    
        if ($product_id) {
            $stmt_item->bind_param("iidid", $order_id, $product_id, $price, $quantity, $subtotal);
            if (!$stmt_item->execute()) {
                throw new Exception("Lỗi insert order_items: " . $stmt_item->error);
            }
    
            $stmt_update->bind_param("ii", $quantity, $product_id);
            if (!$stmt_update->execute()) {
                throw new Exception("Lỗi update product: " . $stmt_update->error);
            }
        }
    }    

    $stmt_item->close();
    $stmt_update->close();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Đặt hàng thành công!"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Lỗi khi lưu đơn hàng: " . $e->getMessage()]);
}
