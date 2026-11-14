<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop API - Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>

{{-- ===== NAVBAR ===== --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#/products"><i class="bi bi-shop"></i> Shop API</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="#/products">Sản phẩm</a></li>
                <li class="nav-item auth-only customer-only"><a class="nav-link" href="#/orders">Đơn hàng của tôi</a></li>
                <li class="nav-item dropdown admin-only">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Quản trị</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#/admin/dashboard"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
                        <li><a class="dropdown-item" href="#/admin/products"><i class="bi bi-box"></i> Sản phẩm</a></li>
                        <li><a class="dropdown-item" href="#/admin/categories"><i class="bi bi-tags"></i> Danh mục</a></li>
                        <li><a class="dropdown-item" href="#/admin/orders"><i class="bi bi-receipt"></i> Đơn hàng</a></li>
                        <li><a class="dropdown-item" href="#/admin/users"><i class="bi bi-people"></i> Người dùng</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="#/cart"><i class="bi bi-cart3"></i> Giỏ hàng <span class="badge bg-warning text-dark" id="cart-count">0</span></a>
                </li>
                <li class="nav-item guest-only"><a class="nav-link" href="#/login">Đăng nhập</a></li>
                <li class="nav-item guest-only"><a class="btn btn-warning btn-sm ms-lg-2" href="#/register">Đăng ký</a></li>
                <li class="nav-item dropdown auth-only">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="bi bi-person-circle"></i> <span id="nav-username"></span></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#/profile">Thông tin cá nhân</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="logout()">Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- ===== NỘI DUNG TRANG (JS sẽ render vào đây) ===== --}}
<main class="container py-4" id="app"></main>

{{-- ===== TOAST THÔNG BÁO ===== --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toasts"></div>

{{-- ===== MODAL DÙNG CHUNG (form thêm/sửa) ===== --}}
<div class="modal fade" id="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
