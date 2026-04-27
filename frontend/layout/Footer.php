<?php
// frontend/components/Footer.php
?>
<footer>
    <div class="footer-content">
        <div class="about-us">
            <h4>Về PetsAccessories</h4>
            <p>Cửa hàng phụ kiện thú cưng uy tín hàng đầu.</p>
        </div>
        <div class="contact-info">
            <h4>Liên hệ</h4>
            <p>Email: contact@petsaccessories.com</p>
            <p>Phone: 1900 xxxx</p>
        </div>
    </div>
    <div class="copyright">
        &copy; <?php echo date('Y'); ?> PetsAccessories. All rights reserved.
    </div>
</footer>

<script>
function addToCart(btn) {
    const productId = btn.getAttribute('data-id');
    if (!productId) return alert('Lỗi: Không tìm thấy ID sản phẩm.');

    // Vô hiệu hóa nút trong khi chờ
    const originalText = btn.innerText;
    btn.innerText = 'Đang thêm...';
    btn.disabled = true;

    fetch('/PetsAccessories/frontend/components/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng trên icon Header
            const badge = document.getElementById('cart-count-badge');
            if (badge) {
                badge.innerText = data.cartCount;
            }
            alert(data.message);
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra kết nối Server.');
    })
    .finally(() => {
        // Phục hồi nút
        btn.innerText = originalText;
        btn.disabled = false;
    });
}
</script>
