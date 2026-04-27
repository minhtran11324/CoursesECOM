<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$query = $_GET['q'] ?? '';
$searchQuery = trim(strip_tags((string)$query));
$pageTitle = 'Kết quả tìm kiếm cho: "' . htmlspecialchars($searchQuery) . '"';
$products = [];
$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if (empty($searchQuery)) {
        $error = 'Vui lòng nhập từ khóa tìm kiếm.';
    } else {
        try {
            // Tìm kiếm theo tên sản phẩm, mã sản phẩm (giả sử có cột sku hoặc product_code), 
            // hoặc mô tả (giả sử cột description).
            // Nếu bạn không có sku/product_code hay description, truy vấn sẽ lỗi, 
            // nên ta dùng try-catch để an toàn nhất và fallback nếu column ko tồn tại.
            
            $sql = 'SELECT 
                        product_id, 
                        product_name AS name, 
                        COALESCE(NULLIF(discount_price, 0), price) AS price, 
                        COALESCE(NULLIF(thumbnail, ""), "/PetsAccessories/public/images/default-product.png") AS image
                    FROM products 
                    WHERE status = 1 AND (
                        product_name LIKE :keyword 
                        OR sku LIKE :keyword 
                        OR description LIKE :keyword 
                    )
                    ORDER BY created_at DESC';
            
            $productsStmt = $db->prepare($sql);
            $searchTerm = '%' . $searchQuery . '%';
            $productsStmt->execute(['keyword' => $searchTerm]);
            $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Fallback nếu không có cột sku hoặc description trong DB
            try {
                $sql = 'SELECT 
                            product_id, 
                            product_name AS name, 
                            COALESCE(NULLIF(discount_price, 0), price) AS price, 
                            COALESCE(NULLIF(thumbnail, ""), "/PetsAccessories/public/images/default-product.png") AS image
                        FROM products 
                        WHERE status = 1 AND product_name LIKE :keyword 
                        ORDER BY created_at DESC';
                
                $productsStmt = $db->prepare($sql);
                $productsStmt->execute(['keyword' => '%' . $searchQuery . '%']);
                $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                $error = 'Có lỗi khi tìm kiếm sản phẩm.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .search-results-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .search-results-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="search-results-section">
    <h2><?php echo empty($searchQuery) ? 'Tìm kiếm' : htmlspecialchars($pageTitle); ?></h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php elseif (empty($products)): ?>
        <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
    <?php else: ?>
        <p>Tìm thấy <strong><?php echo count($products); ?></strong> sản phẩm phù hợp.</p>
        <br>
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