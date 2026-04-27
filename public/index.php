<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets Accessories - Trang chủ</title>
    <link rel="stylesheet" href="../frontend/layout/style.css">
</head>

<body>

    <?php
    // Kết nối DB để lấy sản phẩm cho trang chủ
    require_once __DIR__ . '/../backend/config/database.php';

    $featuredProducts = [];
    $newProducts = [];
    $saleProducts = [];
    $db = $pdo;

    if ($db instanceof PDO) {
        try {
            $featuredStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 1
             ORDER BY (CASE WHEN p.discount_price > 0 THEN 1 ELSE 0 END) DESC, p.stock_quantity DESC, p.created_at DESC
             LIMIT 8"
            );
            $featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

            $newStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 1
             ORDER BY p.created_at DESC
             LIMIT 8"
            );
            $newProducts = $newStmt->fetchAll(PDO::FETCH_ASSOC);

            $saleStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                p.discount_price AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 1
               AND p.discount_price > 0
               AND p.discount_price < p.price
             ORDER BY (p.price - p.discount_price) DESC, p.created_at DESC
             LIMIT 8"
            );
            $saleProducts = $saleStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Giữ mảng rỗng để ProductGrid hiển thị thông báo fallback.
        }
    }

    // Tích hợp các components
    require_once __DIR__ . '/../frontend/layout/Header.php';
    require_once __DIR__ . '/../frontend/components/BannerSlider.php';
    require_once __DIR__ . '/../frontend/components/NewsSection.php';

    // Các section Sản phẩm:
    $sectionTitle = "Sản phẩm Nổi Bật";
    $products = $featuredProducts;
    require __DIR__ . '/../frontend/components/ProductGrid.php';

    $sectionTitle = "Sản phẩm Mới";
    $products = $newProducts;
    require __DIR__ . '/../frontend/components/ProductGrid.php';

    $sectionTitle = "Khuyến Mãi Khủng";
    $products = $saleProducts;
    require __DIR__ . '/../frontend/components/ProductGrid.php';

    // Tin tức & Thông tin
    require_once __DIR__ . '/../frontend/layout/Footer.php';
    ?>

</body>

</html>