<?php
include 'config/db.php';

// Lấy thông tin tìm kiếm và bộ lọc thương hiệu nếu có
$tukhoa = isset($_POST['tukhoa']) ? trim($_POST['tukhoa']) : '';
$brandFilter = isset($_POST['brandFilter']) ? intval($_POST['brandFilter']) : '';

// Tạo SQL cơ bản
$sql = "SELECT p.*, b.namebrand FROM product p 
        LEFT JOIN brands b ON p.idbrand = b.idbrand";

$conditions = [];
if (!empty($tukhoa)) {
    $tukhoa = $conn->real_escape_string($tukhoa);
    $conditions[] = "p.name LIKE '%$tukhoa%' OR p.description LIKE '%$tukhoa%'";
}
if (!empty($brandFilter)) {
    $conditions[] = "p.idbrand = $brandFilter";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

// Thực hiện truy vấn
$result = $conn->query($sql);

// Kiểm tra và trả về dữ liệu sản phẩm dưới dạng HTML
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="col-md-4 mb-4 product-card">
            <div class="card h-100 shadow-sm">
                <?php 
                    $image_path = 'jmg_sanpham/' . $row['image'];
                    if (!empty($row['image']) && file_exists($image_path)) { 
                        echo "<img src='" . $image_path . "' class='card-img-top' alt='" . htmlspecialchars($row['name']) . "'>"; 
                    } else {
                        echo "<img src='jmg_sanpham/default.jpg' class='card-img-top' alt='Hình ảnh sản phẩm'>";
                    }
                ?>
                <div class="card-body text-center">
                    <h4 class="card-title"><?= htmlspecialchars($row['name']) ?></h4>
                    <?php if ($row['quantity'] > 0): ?>
                        <p class="card-text"><?= number_format($row['price'], 0, ',', '.') ?> VNĐ</p>
                        <p class="card-text">Số lượng: <?= $row['quantity'] ?></p>
                        <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                        <p class="card-text"><strong>Hãng: </strong><?= htmlspecialchars($row['namebrand']) ?></p>
                        <button class="btn btn-primary" onclick="addToCart('<?= htmlspecialchars($row['name']) ?>', <?= $row['price'] ?>, '<?= $row['image'] ?>')">Thêm vào giỏ</button>
                    <?php else: ?>
                        <p class="card-text text-danger"><strong>Hết hàng</strong></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo "<div class='col-12'><p class='text-center text-muted'>Không tìm thấy sản phẩm phù hợp.</p></div>";
}
?>
