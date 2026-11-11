<?php require_once 'views/layouts/header.php'; ?>

<!-- 1. HERO BANNER -->
<div class="container-fluid p-0 mb-5">
    <div class="bg-light text-center py-5 border-bottom" style="background: #faf8f5;">
        <div class="py-4">
            <span class="text-uppercase tracking-wide text-muted small fw-bold">New Collection 2026</span>
            <h1 class="display-4 fw-bold mt-2 mb-3" style="letter-spacing: 2px;">RUBIES STUDIO</h1>
            <p class="text-secondary col-md-6 mx-auto mb-0">Khám phá các thiết kế thời trang nữ thanh lịch, hiện đại và tinh tế mới nhất.</p>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. PHÂN LOẠI 1: SẢN PHẨM BÁN CHẠY NHẤT -->
<!-- ========================================== -->
<div class="container mb-5" id="best-sellers">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-uppercase" style="letter-spacing: 1.5px; color: var(--primary-color, #8B1A2F);">
            Sản Phẩm Bán Chạy Nhất
        </h3>
        <p class="text-muted small">Những mẫu thiết kế được khách hàng đặt mua nhiều nhất</p>
        <div class="mx-auto" style="width: 50px; height: 2px; background-color: var(--primary-color, #8B1A2F);"></div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($bestSellers)): ?>
            <?php foreach ($bestSellers as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $hasDiscount = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0);
                ?>
                <div class="col">
                    <div class="card card-product h-100 position-relative border-0 shadow-sm">
                        <!-- Nhãn giảm giá hoặc Hot -->
                        <?php if ($hasDiscount): ?>
                            <?php $percent = round((1 - ($p['sale_price'] / $p['price'])) * 100); ?>
                            <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 small fw-bold m-2 rounded z-1">
                                -<?= $percent ?>%
                            </span>
                        <?php else: ?>
                            <span class="position-absolute top-0 start-0 bg-dark text-white px-2 py-1 small fw-bold m-2 rounded z-1">
                                Hot
                            </span>
                        <?php endif; ?>

                        <!-- Ảnh sản phẩm -->
                        <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="overflow-hidden rounded">
                            <img src="uploads/<?= htmlspecialchars($thumb) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 style="height: 330px; object-fit: cover;">
                        </a>

                        <div class="card-body px-1 py-3 text-center">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($p['color'] ?? '') ?></div>
                            <h6 class="card-title mb-2">
                                <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h6>
                            <div class="price-box mb-1">
                                <?php if ($hasDiscount): ?>
                                    <span class="text-danger fw-bold"><?= number_format($p['sale_price']) ?>đ</span>
                                    <span class="text-decoration-line-through text-muted small ms-2"><?= number_format($p['price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="text-dark fw-bold"><?= number_format($p['price']) ?>đ</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($p['total_sold']) && $p['total_sold'] > 0): ?>
                                <small class="text-muted">Đã bán: <strong><?= (int)$p['total_sold'] ?></strong></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <p class="text-muted">Chưa có dữ liệu bán chạy.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<hr class="container my-5 text-secondary">

<!-- ========================================== -->
<!-- 3. PHÂN LOẠI 2: SẢN PHẨM MỚI NHẤT -->
<!-- ========================================== -->
<div class="container mb-5" id="new-arrivals">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-uppercase" style="letter-spacing: 1.5px;">
            Sản Phẩm Mới Nhất
        </h3>
        <p class="text-muted small">Cập nhật những mẫu trang phục vừa lên kệ tại cửa hàng</p>
        <div class="mx-auto" style="width: 50px; height: 2px; background-color: #333;"></div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($newestProducts)): ?>
            <?php foreach ($newestProducts as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $hasDiscount = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0);
                ?>
                <div class="col">
                    <div class="card card-product h-100 position-relative border-0 shadow-sm">
                        <!-- Nhãn New -->
                        <span class="position-absolute top-0 start-0 bg-success text-white px-2 py-1 small fw-bold m-2 rounded z-1">
                            New
                        </span>

                        <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="overflow-hidden rounded">
                            <img src="uploads/<?= htmlspecialchars($thumb) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 style="height: 330px; object-fit: cover;">
                        </a>

                        <div class="card-body px-1 py-3 text-center">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($p['color'] ?? '') ?></div>
                            <h6 class="card-title mb-2">
                                <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h6>
                            <div class="price-box">
                                <?php if ($hasDiscount): ?>
                                    <span class="text-danger fw-bold"><?= number_format($p['sale_price']) ?>đ</span>
                                    <span class="text-decoration-line-through text-muted small ms-2"><?= number_format($p['price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="text-dark fw-bold"><?= number_format($p['price']) ?>đ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4">
                <p class="text-muted">Chưa có sản phẩm mới nào.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>