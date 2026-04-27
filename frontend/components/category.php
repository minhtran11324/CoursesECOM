<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoryName = 'Danh mục sản phẩm';
$products = [];
$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if (!$categoryId) {
        $error = 'Danh mục không hợp lệ.';
    } else {
        try {
            $categoryStmt = $db->prepare('SELECT category_id, category_name FROM categories WHERE category_id = ? AND status = 1');
            $categoryStmt->execute([$categoryId]);
            $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                $error = 'Danh mục không tồn tại hoặc đã ẩn.';
            } else {
                $categoryName = $category['category_name'];

                $productsStmt = $db->prepare(
                    'SELECT
                        p.product_id,
                        p.product_name AS name,
                        COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                        COALESCE(NULLIF(p.thumbnail, ""), "/PetsAccessories/public/images/default-product.png") AS image
                     FROM products p
                     WHERE p.status = 1
                       AND p.category_id IN (
                           SELECT c.category_id
                           FROM categories c
                           WHERE c.category_id = :categoryId OR c.parent_id = :categoryId
                       )
                     ORDER BY p.created_at DESC'
                );
                $productsStmt->execute(['categoryId' => $categoryId]);
                $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error = 'Không thể tải dữ liệu danh mục lúc này.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoryName); ?> - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="product-section">
    <h2><?php echo htmlspecialchars($categoryName); ?></h2>

    <?php if (!empty($error)): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php elseif (empty($products)): ?>
        <p>Chưa có sản phẩm trong danh mục này.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php include __DIR__ . '/ProductCard.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>
