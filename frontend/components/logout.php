<?php
// frontend/components/logout.php
session_start();
// Xóa tất cả các biến session
session_unset();
// Hủy session
session_destroy();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tự động chuyển hướng về trang chủ sau 3 giây -->
    <meta http-equiv="refresh" content="3;url=/PetsAccessories/public/index.php">
    <title>Đăng xuất - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="auth-container">
    <div class="auth-box" style="text-align: center;">
        <h2 style="color: #ff6f61; margin-bottom: 15px;">Đăng xuất thành công!</h2>
        <p style="color: #888; font-size: 14px; margin-bottom: 30px;">Hệ thống sẽ tự động chuyển về trang chủ sau 3 giây...</p>
        
        <a href="/PetsAccessories/public/index.php" class="btn-auth" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 30px;">
            Quay về Trang chủ ngay
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>
</html>
