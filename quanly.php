<?php
session_start();
require_once 'config/db.php';

// Kiểm tra thông báo
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success" role="alert">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}
// Kiểm tra xem có gửi form hay không
if (isset($_POST['add_account'])) {
    // Lấy thông tin từ form
    $username = $_POST['username'];
    $password = $_POST['password']; // Không mã hóa mật khẩu
    $role = $_POST['role'];

    // Kiểm tra xem tên người dùng đã tồn tại chưa
    try {
        // Truy vấn kiểm tra tên người dùng đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userExists) {
            // Nếu tên người dùng đã tồn tại
            $_SESSION['message'] = "Tên người dùng đã tồn tại!";
            header("Location: " . $_SERVER['PHP_SELF']); // Chuyển hướng lại trang hiện tại
            exit();
        }

        // Thực hiện truy vấn thêm tài khoản vào cơ sở dữ liệu
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$username, $password, $role]);

        // Hiển thị thông báo thành công
        $_SESSION['message'] = "Tạo tài khoản thành công!";
        header("Location: " . $_SERVER['PHP_SELF']); // Chuyển hướng lại trang hiện tại
        exit();
    } catch (PDOException $e) {
        // Nếu có lỗi xảy ra khi thực hiện truy vấn
        $_SESSION['message'] = "Lỗi: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']); // Chuyển hướng lại trang hiện tại
        exit();
    }
}
//Thêm sản phẩm
if (isset($_POST['sbm'])) {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = 'jmg_sanpham/' . $image;

    $price = $_POST['price'];
    $quantity = $_POST['quantity']; 
    $description = $_POST['description'];
    $idbrand = $_POST['idbrand'];

    // Kiểm tra giá trị của description
    if (empty($description)) {
        echo "Mô tả không được để trống!";
    } else {
        // Kiểm tra ảnh đã được tải lên chưa
        if ($_FILES['image']['error'] == 0) {
            // Kiểm tra tải lên ảnh
            if (move_uploaded_file($image_tmp, $image_path)) {
                // Chèn dữ liệu vào cơ sở dữ liệu (Sử dụng prepared statement)
                $stmt = $conn->prepare("INSERT INTO product(name, image, price, quantity, description, idbrand) VALUES (?, ?, ?, ?, ?, ?)");
                // Kiểu dữ liệu bind_param: "ssdiis" -> "s" cho chuỗi (string), "d" cho số thập phân (double), "i" cho số nguyên (integer)
                $stmt->bind_param("ssdiss", $name, $image, $price, $quantity, $description, $idbrand); 

                if ($stmt->execute()) {
                    // Chuyển hướng ngay lập tức sau khi thêm thành công
                    header("Location: " . $_SERVER['PHP_SELF']); 
                    exit(); 
                } else {
                    echo "Lỗi: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Lỗi tải ảnh lên. Vui lòng kiểm tra lại.";
            }
        } else {
            echo "Lỗi tải ảnh: " . $_FILES['image']['error'];
        }
    }
}
    
// Lấy danh sách thương hiệu
$sql_brand = "SELECT * FROM brands";
$query_brand = mysqli_query($conn, $sql_brand);
// Truy vấn tất cả tài khoản
try {
    $stmt = $pdo->query('SELECT id, username, password, role, created_at FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Lỗi truy vấn: ' . $e->getMessage();
}

// Truy vấn tất cả sản phẩm
$sql = "SELECT product.*, brands.namebrand 
        FROM product 
        INNER JOIN brands 
        ON product.idbrand = brands.idbrand";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
    <style>
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        #sidebar a {
            color: white;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
        }
        #sidebar a:hover {
            background-color: #f99bdd;
        }
        #content {
            margin-left: 270px;
            padding: 20px;
        }
        .list-section {
            display: none;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <h3 class="text-center text-white">Quản Lý</h3>
    <a href="javascript:void(0)" id="show_create_account">Tạo Tài Khoản</a>
    <a href="javascript:void(0)" id="show_add_product">Thêm Sản Phẩm</a>
    <a href="javascript:void(0)" id="show_accounts">Danh sách Tài Khoản</a>
    <a href="javascript:void(0)" id="show_products">Danh sách Sản Phẩm</a>
    <a href="javascript:void(0)" id="show_history">Lịch Sử Hành Động</a>
</div>


<!-- Main content -->
<div id="content">
    <!-- Phần Tạo Tài Khoản -->
    <div id="create_account" class="list-section">
        <h2 class="text-center mb-4">Thêm Tài Khoản Mới</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên Người Dùng</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Vai Trò</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="user">Người dùng</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="add_account" class="btn btn-success">Thêm Tài Khoản</button>

        </form>
    </div>
    <!-- Thêm sản phẩm -->
    <div id="add_product" class="list-section">
        <h2 class="text-center mb-4">Thêm Sản Phẩm</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Tên sản phẩm</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Ảnh sản phẩm</label><br>
                <input type="file" id="image" name="image" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Giá sản phẩm</label>
                <input type="number" id="price" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Số lượng sản phẩm</label>
                <input type="number" id="quantity" name="quantity" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Mô Tả sản phẩm</label>
                <textarea id="description" name="description" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label for="idbrand" class="form-label">Hãng hàng</label>
                <select id="idbrand" name="idbrand" class="form-select" required>
                    <?php while ($row_brand = mysqli_fetch_assoc($query_brand)) { ?>
                        <option value="<?php echo $row_brand['idbrand']; ?>"><?php echo $row_brand['namebrand']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <button name="sbm" class="btn btn-success" type="submit">Thêm</button>
        </form>
    </div>
    <!-- Danh sách tài khoản -->
    <div id="account_list" class="list-section">
        <h2 class="text-center mb-4">Danh Sách Tài Khoản</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Người Dùng</th>
                    <th>Mật khẩu</th>
                    <th>Vai trò</th>
                    <th>Ngày Tạo</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Không có tài khoản nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <span id="password-<?php echo $user['id']; ?>" class="password-text" data-password="<?php echo htmlspecialchars($user['password']); ?>">******</span>
                                <button type="button" class="btn btn-info btn-sm" onclick="togglePassword(<?php echo $user['id']; ?>)">
                                    <i class="bi bi-eye"></i> Xem
                                </button>
                            </td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="admin/sua_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('Bạn có chắc muốn Sửa tài khoản này không?')">Chỉnh sửa</a>
                                <a href="admin/xoa_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn Xóa tài khoản này không?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Danh sách sản phẩm -->
    <div id="product_list" class="list-section">
        <h2 class="text-center mb-4">Danh Sách Sản Phẩm</h2>
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
                        <td><a onclick="return confirm('Bạn có muốn SỬA sản phẩm này không');" href="index1.php?page_layout=sua&id=<?php echo $row['id'];?>" class="btn btn-warning">Sửa</a></td>
                        <td><a onclick="return confirm('Bạn có muốn XÓA sản phẩm này không');" href="index1.php?page_layout=xoa&id=<?php echo $row['id'];?>" class="btn btn-danger">Xóa</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <!-- Lịch sử đăng nhập và chỉnh sửa -->
    <div id="history_section" class="list-section">
        <h2 class="text-center mb-4">Lịch Sử Đăng Nhập và Chỉnh Sửa</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Người Dùng</th>
                    <th>Hành Động</th>
                    <th>Mô Tả</th>
                    <th>Thời Gian</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->prepare('SELECT u.id AS user_id, u.username, h.action_type, h.action_description, h.action_time
                                        FROM history h
                                        JOIN users u ON h.user_id = u.id
                                        ORDER BY h.action_time DESC');
                    $stmt->execute();
                    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($history)) {
                        echo '<tr><td colspan="5" class="text-center">Không có lịch sử nào.</td></tr>';
                    } else {
                        foreach ($history as $entry) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($entry['user_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($entry['username']) . '</td>';
                            echo '<td>' . ucfirst($entry['action_type']) . '</td>';
                            echo '<td>' . htmlspecialchars($entry['action_description']) . '</td>';
                            echo '<td>' . date('d/m/Y H:i:s', strtotime($entry['action_time'])) . '</td>';
                            echo '</tr>';
                        }
                    }
                } catch (PDOException $e) {
                    echo '<tr><td colspan="5" class="text-danger">Lỗi truy vấn lịch sử: ' . $e->getMessage() . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Thêm Bootstrap JS và Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<!-- JavaScript để hiển thị mật khẩu -->
<script>
function togglePassword(userId) {
    const passwordSpan = document.getElementById('password-' + userId);
    const realPassword = passwordSpan.getAttribute('data-password');
    
    if (passwordSpan.textContent === '******') {
        passwordSpan.textContent = realPassword;
    } else {
        passwordSpan.textContent = '******';
    }
}

// JavaScript để điều khiển việc hiển thị danh sách
    document.getElementById('show_create_account').addEventListener('click', function() {
    document.getElementById('create_account').style.display = 'block';
    document.getElementById('account_list').style.display = 'none';
    document.getElementById('product_list').style.display = 'none';
});

    document.getElementById('show_accounts').addEventListener('click', function() {
    document.getElementById('create_account').style.display = 'none';
    document.getElementById('account_list').style.display = 'block';
    document.getElementById('product_list').style.display = 'none';
});

    document.getElementById('show_products').addEventListener('click', function() {
    document.getElementById('create_account').style.display = 'none';
    document.getElementById('account_list').style.display = 'none';
    document.getElementById('product_list').style.display = 'block';
});
    document.getElementById('show_create_account').addEventListener('click', function() {
        showSection('create_account');
    });
    document.getElementById('show_accounts').addEventListener('click', function() {
        showSection('account_list');
    });
    document.getElementById('show_products').addEventListener('click', function() {
        showSection('product_list');
    });
    document.getElementById('show_history').addEventListener('click', function() {
        showSection('history_section');
    });

    function showSection(sectionId) {
        const sections = document.querySelectorAll('.list-section');
        sections.forEach(section => section.style.display = 'none');
        document.getElementById(sectionId).style.display = 'block';
    }
    document.getElementById('show_add_product').addEventListener('click', function() {
    showSection('add_product');
});

function showSection(sectionId) {
    const sections = document.querySelectorAll('.list-section');
    sections.forEach(section => section.style.display = 'none');
    document.getElementById(sectionId).style.display = 'block';
    }

</script>

</
