<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$reviews = [];
$relatedProducts = [];
$error = '';
$db = $pdo;

if (!$productId) {
    $error = 'Sản phẩm không hợp lệ.';
} elseif (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    try {
        // Try full fields first
        $stmt = $db->prepare(
            'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, description, specifications, stock_quantity
             FROM products
             WHERE status = 1 AND product_id = ?
             LIMIT 1'
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $product = null;
    }

    if (!$product) {
        try {
            // Fallback if specifications column does not exist
            $stmt = $db->prepare(
                'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, description, stock_quantity
                 FROM products
                 WHERE status = 1 AND product_id = ?
                 LIMIT 1'
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $product['specifications'] = '';
            }
        } catch (PDOException $e) {
            $product = null;
        }
    }

    if (!$product) {
        try {
            // Minimal fallback
            $stmt = $db->prepare(
                'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, stock_quantity
                 FROM products
                 WHERE status = 1 AND product_id = ?
                 LIMIT 1'
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $product['description'] = '';
                $product['specifications'] = '';
            }
        } catch (PDOException $e) {
            $product = null;
        }
    }

    if (!$product) {
        $error = 'Không tìm thấy sản phẩm.';
    } else {
        // Fetch reviews if product exists
        try {
            $reviewStmt = $db->prepare(
                'SELECT id, user_id, rating, comment, created_at
                 FROM reviews
                 WHERE product_id = ? AND status = 1
                 ORDER BY created_at DESC
                 LIMIT 10'
            );
            $reviewStmt->execute([$productId]);
            $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $reviews = [];
        }

        // Fetch related products (prefer same category; fallback to latest products)
        $categoryId = (int) ($product['category_id'] ?? 0);

        if ($categoryId > 0) {
            try {
                $relatedStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     WHERE p.category_id = ?
                       AND p.status = 1
                       AND p.product_id != ?
                     ORDER BY p.product_id DESC
                     LIMIT 6'
                );
                $relatedStmt->execute([$categoryId, $productId]);
                $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $relatedProducts = [];
            }
        }

        if (empty($relatedProducts)) {
            try {
                $fallbackStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     WHERE p.status = 1
                       AND p.product_id != ?
                     ORDER BY p.product_id DESC
                     LIMIT 6'
                );
                $fallbackStmt->execute([$productId]);
                $relatedProducts = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $relatedProducts = [];
            }
        }
    }
}