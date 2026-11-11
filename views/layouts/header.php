<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RUBIES SHOP - Cửa hàng thời trang nữ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

<!-- TOP NAVIGATION -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3 text-danger" href="index.php?url=home/index">RUBIES</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <!-- Center Menu -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-3">
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="index.php?url=home/index">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="index.php?url=category/index">Thời trang nữ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="index.php?url=page/sale">Khuyến mại</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-dark" href="#" data-bs-toggle="dropdown">
                            Trợ giúp
                        </a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item py-2" href="index.php?url=page/policy&type=about">Về chúng tôi</a></li>
                            <li><a class="dropdown-item py-2" href="index.php?url=page/policy&type=loyalty">Chính sách KH thân thiết</a></li>
                            <li><a class="dropdown-item py-2" href="index.php?url=page/policy&type=return">Chính sách Đổi & Trả hàng</a></li>
                            <li><a class="dropdown-item py-2" href="index.php?url=page/policy&type=warranty">Chính sách Bảo hành</a></li>
                            <li><a class="dropdown-item py-2" href="index.php?url=page/policy&type=usage">Hướng dẫn sử dụng</a></li>
                        </ul>
                    </li>
                </ul>

            <!-- Search & Actions -->
            <div class="d-flex align-items-center gap-3">
                <!-- Search Form[cite: 7] -->
                <form action="index.php" method="GET" class="d-flex align-items-center position-relative">
                    <input type="hidden" name="url" value="page/search">
                    <input type="text" name="q" class="form-control form-control-sm pe-4 rounded-pill" 
                           placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="btn btn-sm position-absolute end-0 border-0 me-1 text-secondary">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <!-- Yêu thích[cite: 7] -->
                <a href="index.php?url=profile/favorites" class="text-dark" title="Sản phẩm yêu thích">
                    <i class="fa-regular fa-heart fs-5"></i>
                </a>

                <!-- Giỏ hàng -->
                <a href="index.php?url=cart/index" class="text-dark position-relative" title="Giỏ hàng">
                    <i class="fa-solid fa-cart-shopping fs-5"></i>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                        <?= !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                    </span>
                </a>

                <!-- Tài khoản -->
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <a class="dropdown-toggle text-dark text-decoration-none fw-bold" href="#" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($currentUser['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            <li><a class="dropdown-item" href="index.php?url=profile/index">Đơn hàng của bạn</a></li>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item text-danger fw-bold" href="admin.php">Trang Quản Trị</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-secondary" href="index.php?url=auth/logout">Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="index.php?url=auth/login" class="text-dark" title="Đăng nhập"><i class="fa-regular fa-user fs-5"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- BREADCRUMB PHÂN CẤP[cite: 7] -->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php?url=home/index" class="text-decoration-none text-secondary">Trang chủ</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">
                    <?= htmlspecialchars(ucfirst(str_replace(['/', '_'], ' ', $_GET['url'] ?? 'Danh mục'))) ?>
                </li>
            </ol>
        </nav>
    </div>
</div>