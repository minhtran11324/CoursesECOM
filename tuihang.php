<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #cart-list .d-flex {
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
        }

        #cart-list .d-flex img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        #cart-list .d-flex span {
            margin-left: 15px;
        }

        /* Nút thanh toán */
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }

        /* Nút xóa */
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>

<body>

<div class="container mt-5">
    <h2 class="text-center">Giỏ Hàng</h2>
    <div id="cart-list"></div>

    <!-- Tổng tiền -->
    <div id="total-price" class="mt-3">
        Tổng Tiền: 0 VND
    </div>

    <!-- Nút Thanh toán -->
    <button type="button" class="btn btn-primary" onclick="checkout()">Xác Nhận Thanh Toán</button>
    <a href="index.php" class="btn btn-secondary">Quay Lại Trang Chủ</a>

    <!-- Modal Hiển thị hóa đơn -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Hóa Đơn Chi Tiết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="redirectToCart()"></button>
                </div>
                <div class="modal-body">
                    <div id="receipt-details"></div>
                    <hr>
                    <h5>Thông tin khách hàng</h5>
                    <form id="customer-form">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Họ và Tên</label>
                            <input type="text" class="form-control" id="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa Chỉ Giao Hàng</label>
                            <textarea class="form-control" id="address" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email (tùy chọn)</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="redirectToCart()">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="submitOrder()">Xác Nhận & Gửi Đơn Hàng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateCart() {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartList = document.getElementById('cart-list');
        const totalPrice = document.getElementById('total-price');
        cartList.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            cartList.innerHTML = `<p class="text-center">Giỏ hàng của bạn hiện tại trống!</p>`;
        } else {
            cart.forEach((item, index) => {
                const itemElement = document.createElement('div');
                itemElement.classList.add('d-flex', 'justify-content-between', 'mb-3', 'border-bottom', 'pb-2');
                itemElement.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="jmg_sanpham/${item.image}" alt="" style="width: 100px; height: 100px; object-fit: cover;">
                        <span class="ms-3">${item.name}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-3">${(item.price * item.quantity).toLocaleString()} VND</span>

                        <div class="input-group input-group-sm me-3" style="width: 120px;">
                            <button class="btn btn-outline-secondary btn-sm" onclick="changeQuantity(${index}, -1)">-</button>
                            <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                            <button class="btn btn-outline-secondary btn-sm" onclick="changeQuantity(${index}, 1)">+</button>
                        </div>

                        <button class="btn btn-danger btn-sm" onclick="removeItem(${index})">Xóa</button>
                    </div>
                `;
                cartList.appendChild(itemElement);
                total += item.price * item.quantity;
            });
        }

        totalPrice.innerText = "Tổng Tiền: " + total.toLocaleString() + " VND";
    }

    function changeQuantity(index, delta) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart[index]) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) {
                if (confirm("Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?")) {
                    cart.splice(index, 1);
                } else {
                    cart[index].quantity = 1;
                }
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCart();
        }
    }

    function removeItem(index) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCart();
    }

    function checkout() {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart.length === 0) {
            alert("Giỏ hàng của bạn trống. Vui lòng thêm sản phẩm vào giỏ.");
        } else {
            let receiptDetails = '<h4>Chi tiết sản phẩm:</h4><ul>';
            let total = 0;
            cart.forEach(item => {
                receiptDetails += `<li>${item.name} - ${item.quantity} x ${item.price.toLocaleString()} VND = ${(item.quantity * item.price).toLocaleString()} VND</li>`;
                total += item.price * item.quantity;
            });
            receiptDetails += `</ul><h5>Tổng cộng: ${total.toLocaleString()} VND</h5>`;
            document.getElementById('receipt-details').innerHTML = receiptDetails;
            const myModal = new bootstrap.Modal(document.getElementById('receiptModal'));
            myModal.show();
        }
    }

    function redirectToCart() {
        window.location.href = "tuihang.php";
    }

    window.onload = updateCart;

    function submitOrder() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];

        if (cart.length === 0) {
            alert("Giỏ hàng của bạn trống. Vui lòng thêm sản phẩm vào giỏ.");
            return;
        }

        // Lấy thông tin khách hàng
        const fullname = document.getElementById("fullname").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const address = document.getElementById("address").value.trim();
        const email = document.getElementById("email").value.trim();

        if (!fullname || !phone || !address) {
            alert("Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Địa chỉ.");
            return;
        }

        const orderData = {
            customer: { fullname, phone, address, email },
            cart: cart
        };

        fetch('process_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Thanh toán thành công! Cảm ơn bạn đã đặt hàng.");
                localStorage.removeItem('cart');
                window.location.href = "index.php";
            } else {
                alert("Lỗi khi thanh toán: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Đã xảy ra lỗi khi thanh toán.");
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
