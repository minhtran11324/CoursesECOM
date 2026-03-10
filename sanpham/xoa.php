<?php
// Kiểm tra nếu có ID cần xóa
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Kết nối với cơ sở dữ liệu
    require_once 'config/db.php';

    // Chuẩn bị câu lệnh xóa với prepared statement để tránh SQL Injection
    $sql = "DELETE FROM product WHERE id = ?";
    
    // Sử dụng prepared statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);  // "i" là kiểu dữ liệu integer (số nguyên)
    
    if ($stmt->execute()) {
        // Sau khi xóa thành công, chuyển hướng về trang danh sách sản phẩm
        header("Location: quanly.php");
        exit();  // Đảm bảo dừng thực thi mã tiếp theo
    } else {
        echo "Lỗi khi xóa sản phẩm: " . $stmt->error;
    }

    // Đóng kết nối
    $stmt->close();
    mysqli_close($conn);
} else {
    echo "Không có ID sản phẩm để xóa.";
}
?>
