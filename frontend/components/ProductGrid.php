<?php
// frontend/components/ProductGrid.php
// Dự kiến nhận vào: $sectionTitle (Tiêu đề), $products (Mảng sản phẩm)
?>
<section class="product-section">
    <h2><?php echo htmlspecialchars($sectionTitle ?? 'Danh sách sản phẩm'); ?></h2>
    <div class="product-grid">
        <?php 
        if (!empty($products) && is_array($products)) {
            foreach ($products as $product) {
                // Inline include of ProductCard, passing $product
                include __DIR__ . '/ProductCard.php';
            }
        } else {
            echo "<p>Chưa có sản phẩm nào.</p>";
        }
        ?>
    </div>
</section>
