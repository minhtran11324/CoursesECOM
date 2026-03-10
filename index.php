<?php
session_start();
include 'config/db.php'; // Kết nối DB

// Lấy danh sách thương hiệu
$sql_brand = "SELECT * FROM brands";
$result_brand = $conn->query($sql_brand);
$brands = [];
while ($row = $result_brand->fetch_assoc()) {
    $brands[] = $row;
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Thú Cưng</title>

    <!-- Thêm Bootstrap CSS từ CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Thêm FontAwesome cho biểu tượng giỏ hàng và kính lúp -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Thêm icon user bằng boxicon -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        /* Tùy chỉnh thêm */
        .product-card {
            margin-bottom: 30px;
            background-image: url(image_web/leg-cat2.png);
        }

        .hero-section {
            background-color: #f99bdd;
            padding: 60px 0;
            border-radius: 10px;
            background-image: url(image_web/leg-cat.png);
        }

        .hero-text {
            text-align: center;
            padding: 30px;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 25px 0;
            text-align: center;
        }

        .footer a {
            color: #dcdcdc;
            text-decoration: none;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 4px 8px;
            font-size: 8px;
        }

        .navbar-nav .nav-item {
            position: relative;
        }

        /* Cấu hình sticky header */
        .navbar {
            transition: all 0.3s ease; /* Thêm hiệu ứng trượt */
        }

        /* Tùy chỉnh giao diện thanh tìm kiếm */
        .search-bar .input-group {
            width: 100%;  /* Đảm bảo ô tìm kiếm chiếm hết chiều rộng */
            max-width: 600px; /* Giới hạn chiều rộng tối đa */
            margin: 20px auto;  /* Căn giữa */
        }

        .search-bar .input-group-text {
            background-color: #f99bdd; /* Màu nền cho biểu tượng kính lúp */
            border: 1px solid #f99bdd;  /* Màu viền */
        }

        .search-bar .form-control {
            border-left: 0;  /* Ẩn viền trái của ô nhập */
            border-radius: 0;  /* Không bo góc */
        }

        /* CSS để dropdown tự động mở khi di chuột */
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0; /* Đảm bảo dropdown không bị chệch xuống quá nhiều */
        }

        /* Tùy chỉnh thêm để giúp dropdown không bị ẩn ngay sau khi di chuột ra */
        .nav-item.dropdown .dropdown-menu {
            display: none; /* Mặc định là ẩn */
            position: absolute;
            will-change: opacity;
            transition: opacity 0.3s ease; /* Hiệu ứng mờ khi mở */
        }

        /* Tùy chỉnh thêm hiệu ứng cho khi di chuột */
        .nav-item.dropdown:hover .dropdown-menu {
            opacity: 1; /* Hiển thị khi hover */
        }

        .product-card:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="image_web/Hinh-bieu-tuong-shop-thu-cung-doc-dao.jpg" alt="Logo" style="width: 100px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <?php if (!isset($_SESSION['username'])): ?>
                        <a class="nav-link" href="login1.php"><i class="fas fa-user"></i></a>
                    <?php else: ?>
                        <a class="nav-link" href="logout.php" style="font-weight: bold;">Đăng Xuất</a>
                    <?php endif; ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tuihang.php" id="cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-badge" id="cart-count">0</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-between">
        <div class="col-md-6">
            <form method="POST" action="" class="search-bar" id="search-form">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Tìm kiếm sản phẩm..." name="tukhoa" id="search">
                    <button class="input-group-text" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="col-md-3">
            <select id="brand-filter" class="form-select">
                <option value="">Chọn Thương Hiệu</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['idbrand'] ?>"><?= htmlspecialchars($brand['namebrand']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<section class="hero-section">
    <div class="container">
        <div class="hero-text">
            <h1>Chào Mừng Đến Với Shop Thú Cưng</h1>
            <p>Cung cấp tất cả những gì bạn cần cho thú cưng yêu quý của mình.</p>
        </div>
    </div>
</section>

<div class="container">
    <h2 class="text-center my-4">Danh Sách Sản Phẩm</h2>
    <div class="row" id="product-list">
        <?php include 'get_products.php'; ?>
    </div>
</div>

<footer class="footer">
    <p>&copy;Shop Thú Cưng. Tất cả quyền lợi được bảo lưu.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script>
    let isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    function addToCart(name, price, image) {
        if (!isLoggedIn) {
            alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.");
            window.location.href = "login1.php";
            return;
        }
        let product = cart.find(p => p.name === name);
        if (product) {
            product.quantity++;
        } else {
            cart.push({ name, price, image, quantity: 1 });
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        alert("Đã thêm vào giỏ hàng!");
    }

    function updateCartCount() {
        let total = cart.reduce((sum, item) => sum + item.quantity, 0);
        document.getElementById('cart-count').innerText = total;
    }

    function fetchProducts(formData) {
        fetch('get_products.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(html => {
            document.getElementById('product-list').innerHTML = html;
        })
        .catch(err => console.error("Lỗi khi tải sản phẩm: ", err));
    }


    document.getElementById("search-form").addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('brandFilter', document.getElementById("brand-filter").value);
        fetchProducts(formData);
    });

    document.getElementById("brand-filter").addEventListener("change", function() {
        const formData = new FormData();
        formData.append('tukhoa', document.getElementById("search").value);
        formData.append('brandFilter', this.value);
        fetchProducts(formData);
    });

    updateCartCount();
</script>
</body>
</html>