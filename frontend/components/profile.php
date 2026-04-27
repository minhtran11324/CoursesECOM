<?php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php');
    exit;
}

$db = $pdo;
$error = '';
$success = '';

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($fullname)) {
            $error = 'Vui lòng nhập họ tên.';
        } else {
            try {
                $stmt = $db->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?');
                $updated = $stmt->execute([$fullname, $phone, $address, $_SESSION['user_id']]);

                if ($updated) {
                    $success = 'Cập nhật hồ sơ thành công!';
                    $_SESSION['user_name'] = $fullname; // Update session name

                    // Xử lý Upload Avatar
                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../../backend/upload/avatar/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                            // Xóa ảnh cũ theo pattern để tránh mọc ra các file phụ dư thừa khi user up lại
                            $oldFiles = glob($uploadDir . 'avatar_' . $_SESSION['user_id'] . '.*');
                            if ($oldFiles) {
                                foreach ($oldFiles as $oldFile) {
                                    unlink($oldFile);
                                }
                            }
                            // Lưu ảnh mới với format avatar_<user_id>.ext
                            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . 'avatar_' . $_SESSION['user_id'] . '.' . $fileExt);
                        } else {
                            $error = 'Hồ sơ đã lưu nhưng Ảnh Avatar bị từ chối (Chỉ cho phép JPG, PNG, GIF).';
                        }
                    }
                } else {
                    $error = 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
                }
            } catch (PDOException $e) {
                // Ignore missing columns if phone or address do not exist
                $error = 'Có lỗi hệ thống trong quá trình cập nhật, bạn có thể kiểm tra lại database schema.';
            }
        }
    }

    try {
        $stmt = $db->prepare('SELECT username, email, fullname, phone, address FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Không tìm thấy tài khoản của bạn.';
            $user = ['username' => '', 'email' => '', 'fullname' => '', 'phone' => '', 'address' => ''];
        }
    } catch (PDOException $e) {
        $user = ['username' => '', 'email' => '', 'fullname' => '', 'phone' => '', 'address' => ''];
        $error = 'Có lỗi hệ thống, vui lòng thử lại sau.';
    }
}

// Xử lý load Avatar URL
$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode(!empty($user['fullname']) ? $user['fullname'] : ($user['username'] ?? 'User')) . '&background=random&color=fff&size=100&bold=true';
if (isset($_SESSION['user_id'])) {
    $avatarGlob = glob(__DIR__ . '/../../backend/upload/avatar/avatar_' . $_SESSION['user_id'] . '.*');
    if (!empty($avatarGlob)) {
        // Đính kèm ?t=time() để trình duyệt không load cache cũ khi user vừa tải ảnh mới lên
        $avatarUrl = '/PetsAccessories/backend/upload/avatar/' . basename($avatarGlob[0]) . '?t=' . time(); 
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-view .info-group {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .profile-view .info-group label {
            display: block;
            font-size: 14px;
            color: #888;
            margin-bottom: 5px;
        }
        .profile-view .info-group p {
            font-size: 16px;
            color: #333;
            font-weight: 600;
            margin: 0;
        }
        .btn-edit-mode {
            background-color: #ff6f61;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .btn-edit-mode:hover {
            background-color: #e05e50;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<?php $showEditForm = ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) ? true : false; ?>

<main class="auth-container">
    <div class="auth-box profile-container">
        <h2 style="text-align: center; margin-bottom: 10px;">Hồ sơ cá nhân</h2>
        
        <div class="profile-avatar-container" style="text-align: center; margin-bottom: 25px;">
            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ff6f61; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        </div>

        <?php if (!empty($error)): ?>
            <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- CHẾ ĐỘ XEM THÔNG TIN (VIEW) -->
        <div id="profile-view" class="profile-view" style="display: <?php echo $showEditForm ? 'none' : 'block'; ?>;">
            <div class="info-group">
                <label>Tên đăng nhập</label>
                <p><?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
            </div>
            <div class="info-group">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            </div>
            <div class="info-group">
                <label>Họ và tên</label>
                <p><?php echo htmlspecialchars($user['fullname'] ?? 'Chưa thiết lập'); ?></p>
            </div>
            <div class="info-group">
                <label>Số điện thoại</label>
                <p><?php echo htmlspecialchars(!empty($user['phone']) ? $user['phone'] : 'Chưa thiết lập'); ?></p>
            </div>
            <div class="info-group">
                <label>Địa chỉ</label>
                <p><?php echo htmlspecialchars(!empty($user['address']) ? $user['address'] : 'Chưa thiết lập'); ?></p>
            </div>

            <button type="button" class="btn-edit-mode" onclick="toggleProfileMode('edit')">Chỉnh sửa</button>
            <p class="index-link" style="margin-top: 15px; text-align: center;">
                <a href="/PetsAccessories/public/index.php">Quay về trang chủ</a>
            </p>
        </div>

        <!-- CHẾ ĐỘ CHỈNH SỬA (EDIT) -->
        <div id="profile-edit" style="display: <?php echo $showEditForm ? 'block' : 'none'; ?>;">
            <form action="profile.php" method="POST" class="auth-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="avatar">Ảnh đại diện (Avatar)</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="fullname">Họ và tên</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>

                <button type="submit" class="btn-auth">Cập nhật hồ sơ</button>

                <p class="index-link" style="margin-top: 15px; text-align: center;">
                    <a href="javascript:void(0)" onclick="toggleProfileMode('view')">Quay lại thông tin cá nhân</a>
                </p>
            </form>
        </div>
    </div>
</main>

<script>
function toggleProfileMode(mode) {
    const viewSection = document.getElementById('profile-view');
    const editSection = document.getElementById('profile-edit');
    
    if (mode === 'edit') {
        viewSection.style.display = 'none';
        editSection.style.display = 'block';
    } else {
        viewSection.style.display = 'block';
        editSection.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>
</html>