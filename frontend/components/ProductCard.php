<?php
// frontend/components/ProductCard.php
// Dự kiến nhận vào mảng $product với các thông tin: id, name, price, image...
?>
<div class="product-card">
    <div class="product-image">
        <img src="<?php echo htmlspecialchars($product['image'] ?? '/public/images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Tên sản phẩm'); ?>">
    </div>
    <div class="product-info">
        <h3><?php echo htmlspecialchars($product['name'] ?? 'Product Name'); ?></h3>
        <p class="price"><?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> đ</p>
        <button class="btn-add-cart" data-id="<?php echo (int)($product['product_id'] ?? 0); ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
    </div>
</div>
