<?php
include 'config/db.php';
// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thực hiện truy vấn
$sql = "SELECT product.*, brands.namebrand 
        FROM product 
        INNER JOIN brands 
        ON product.idbrand = brands.idbrand";
$result = mysqli_query($conn, $sql);

// Kiểm tra kết quả truy vấn
if (!$result) {
    die("Truy vấn thất bại: " . mysqli_error($connect));
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h2>Danh sách sản phẩm</h2>
        </div>
        <div class="card-body">
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th>Stt</th>
                        <th>Tên sản phẩm</th>
                        <th>Ảnh sản phẩm</th>
                        <th>Giá sản phẩm</th>
                        <th>Số lượng sản phẩm</th>
                        <th>Mô tả sản phẩm</th>
                        <th>Hãng hàng</th>
                        <th>Sửa</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>
                                <?php 
                                    if (!empty($row['image'])) { 
                                        echo "<img src='jmg_sanpham/" . $row['image'] . "' alt='" . $row['name'] . "' width='200'>"; 
                                    } else {
                                        echo "Không có ảnh";
                                    }
                                ?>
                            </td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo $row['description']; ?></td>
                            <td><?php echo $row['namebrand']; ?></td>
                            <td><a onclick="return confirm('Bạn có muốn SỬA sẩn phẩm này không');" href="index1.php?page_layout=sua&id=<?php echo $row['id'];?>" class="btn btn-warning">Sửa</a></td>
                            <td><a onclick="return confirm('Bạn có muốn XÓA sẩn phẩm này không');" href="index1.php?page_layout=xoa&id=<?php echo $row['id'];?>" class="btn btn-danger">Xóa</a></td>
                            
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="index1.php?page_layout=them" class="btn btn-success">Thêm mới</a>
            <a href="http://localhost:8080/Demo/index.php" class="btn btn-secondary">Quay Lại Trang Chủ</a>
        </div>
    </div>
</div>

