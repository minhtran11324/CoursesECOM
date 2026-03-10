<?php
include 'config/db.php';

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra nếu có ID sản phẩm (được truyền qua URL)
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Truy vấn thông tin sản phẩm theo ID
    $sql = "SELECT product.*, brands.namebrand 
            FROM product 
            INNER JOIN brands 
            ON product.idbrand = brands.idbrand 
            WHERE product.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra nếu có kết quả
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Không tìm thấy sản phẩm!");
    }
} else {
    die("ID sản phẩm không hợp lệ!");
}

// Kiểm tra nếu người dùng submit form
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];
    $idbrand = $_POST['idbrand'];
    
    // Kiểm tra nếu người dùng có upload ảnh mới
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = 'jmg_sanpham/' . $image;
        
        // Upload ảnh mới
        if (move_uploaded_file($image_tmp, $image_path)) {
            // Cập nhật thông tin sản phẩm vào cơ sở dữ liệu (bao gồm ảnh mới)
            $sql = "UPDATE product SET name = ?, price = ?, quantity = ?, description = ?, idbrand = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdissis", $name, $price, $quantity, $description, $idbrand, $image, $id);
        } else {
            echo "Lỗi tải ảnh!";
        }
    } else {
        // Nếu không upload ảnh mới, chỉ cập nhật các thông tin khác
        $sql = "UPDATE product SET name = ?, price = ?, quantity = ?, description = ?, idbrand = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdissi", $name, $price, $quantity, $description, $idbrand, $id);
    }

    if ($stmt->execute()) {
        header("Location: quanly.php"); // Chuyển hướng về trang danh sách sản phẩm
        exit();
    } else {
        echo "Lỗi cập nhật sản phẩm: " . $stmt->error;
    }
}

?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h2>Sửa sản phẩm</h2>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Tên sản phẩm</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Giá sản phẩm</label>
                    <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="quantity">Số lượng sản phẩm</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo $product['quantity']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả sản phẩm</label>
                    <input type="text" name="description" class="form-control" value="<?php echo $product['description']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="idbrand">Hãng hàng</label>
                    <select name="idbrand" class="form-control" required>
                        <?php
                        // Lấy danh sách các hãng hàng
                        $brand_query = mysqli_query($conn, "SELECT * FROM brands");
                        while ($row_brand = mysqli_fetch_assoc($brand_query)) {
                            echo '<option value="' . $row_brand['idbrand'] . '"' . ($row_brand['idbrand'] == $product['idbrand'] ? ' selected' : '') . '>' . $row_brand['namebrand'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Ảnh sản phẩm</label>
                    <input type="file" name="image" class="form-control">
                    <img src="jmg_sanpham/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="100">
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Cập nhật</button>
            </form>
        </div>
    </div>
</div>

<?php
// Đóng kết nối
mysqli_close($conn);
?>
