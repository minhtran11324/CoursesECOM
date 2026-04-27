<?php
// frontend/layout/Header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$db = $pdo;

if (!($db instanceof PDO)) {
    $category_tree = [];
} else {
    try {
        $stmt = $db->query("SELECT category_id, category_name, parent_id FROM categories WHERE status = 1 ORDER BY COALESCE(parent_id, 0), sort_order, category_name");
        $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $category_tree = [];
        foreach ($all_categories as $category) {
            if ($category['parent_id'] === null) {
                $category_tree[(int) $category['category_id']] = $category;
                $category_tree[(int) $category['category_id']]['children'] = [];
            }
        }

        foreach ($all_categories as $category) {
            $parent_id = $category['parent_id'];
            if ($parent_id !== null && isset($category_tree[(int) $parent_id])) {
                $category_tree[(int) $parent_id]['children'][] = $category;
            }
        }
    } catch (PDOException $e) {
        $category_tree = [];
    }
}

$mega_menu_columns = array_chunk(array_values($category_tree), 3);

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>

<header>
    <div class="header-top">
        <div class="logo">
            <a href="/PetsAccessories/public/index.php" style="text-decoration: none; color: inherit;">
                <h1>PetsAccessories</h1>
            </a>
        </div>
        <div class="search-bar">
            <form action="/PetsAccessories/frontend/components/search.php" method="GET">
                <input type="text" name="q" placeholder="Tìm kiếm nhanh sản phẩm...">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>
        <div class="auth-buttons">
            <div class="cart-icon-container" style="margin-right: 15px; position: relative;">
                <a href="/PetsAccessories/frontend/components/cart.php" style="text-decoration: none; color: #333; font-weight: bold; font-size: 16px;">
                    🛒 Giỏ hàng
                    <span id="cart-count-badge" style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; position: absolute; top: -10px; right: -15px;"><?php echo $cartCount; ?></span>
                </a>
            </div>
            <?php if (isset($_SESSION['user_name'])): ?>
                <div class="user-menu">
                    <button class="btn-profile">Chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?> &#9662;</button>
                    <div class="dropdown-content">
                        <a href="/PetsAccessories/frontend/components/profile.php">Hồ sơ cá nhân</a>
                        <a href="/PetsAccessories/frontend/components/orders.php">Đơn mua hàng</a>
                        <a href="/PetsAccessories/frontend/components/change_password.php">Đổi mật khẩu</a>
                        <a href="/PetsAccessories/frontend/components/logout.php" class="logout-link">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="login">
                    <form action="/PetsAccessories/frontend/components/login.php" method="GET">
                        <button type="submit" class="btn-login">Đăng nhập</button>
                    </form>
                </div>
                <div class="register">
                    <form action="/PetsAccessories/frontend/components/register.php" method="GET">
                        <button type="submit" class="btn-register">Đăng ký</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div> <!-- /header-top -->

    <!-- MEGA MENU BAR - DYNAMIC FROM DATABASE -->
    <nav class="primary-nav">
        <ul class="primary-menu">
            <li class="has-mega">
                <a href="#">Chó</a>
                
                <div class="mega-dropdown">
                    <div class="mega-inner">
                        <?php foreach ($mega_menu_columns as $column): ?>
                            <div class="mega-col">
                                <?php foreach ($column as $parent_category): ?>
                                    <div class="mega-group">
                                        <h4><?php echo htmlspecialchars($parent_category['category_name']); ?></h4>
                                        <?php if (!empty($parent_category['children'])): ?>
                                            <ul>
                                                <?php foreach ($parent_category['children'] as $child): ?>
                                                    <li><a href="/PetsAccessories/frontend/components/category.php?id=<?php echo (int) $child['category_id']; ?>"><?php echo htmlspecialchars($child['category_name']); ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($mega_menu_columns)): ?>
                            <div class="mega-col">
                                <div class="mega-group">
                                    <h4>Danh mục đang cập nhật</h4>
                                    <ul>
                                        <li><a href="/PetsAccessories/public/index.php">Quay về trang chủ</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div> <!-- /mega-dropdown -->
            </li>
            
            <li><a href="#">Mèo</a></li>
            <li><a href="#">Thiết bị thông minh</a></li>
            <li><a href="#">Hàng mới về</a></li>
            <li><a href="#">Thương hiệu</a></li>
            <li><a href="#">Pagazine chăm Boss</a></li>
            <li><a href="#">News</a></li>
            <li><a href="#">Khuyến Mãi Mới Nhất</a></li>
            <li><a href="#">VI / USD</a></li>
        </ul>
    </nav>
</header>