<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../backend/config/database.php';

$response = ['success' => false, 'message' => '', 'cartCount' => 0];

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    $response['message'] = 'Sản phẩm không hợp lệ.';
    echo json_encode($response);
    exit;
}

$db = $pdo;
if (!($db instanceof PDO)) {
    $response['message'] = 'Không thể kết nối cơ sở dữ liệu.';
    echo json_encode($response);
    exit;
}

try {
    // Check product exists and stock > 0
    $stmt = $db->prepare('SELECT product_name, stock_quantity FROM products WHERE product_id = ? FOR UPDATE');
    $db->beginTransaction();
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $response['message'] = 'Sản phẩm không tồn tại.';
        $db->rollBack();
    } elseif ($product['stock_quantity'] <= 0) {
        $response['message'] = 'Sản phẩm đã hết hàng.';
        $db->rollBack();
    } else {
        // Decrease stock
        $updateStmt = $db->prepare('UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = ?');
        $updateStmt->execute([$productId]);

        // Add to session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }
        $_SESSION['cart'][$productId]++;

        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Đã thêm vào giỏ hàng!';
        $response['cartCount'] = array_sum($_SESSION['cart']);
    }
} catch (PDOException $e) {
    $db->rollBack();
    $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
}

echo json_encode($response);
exit;
