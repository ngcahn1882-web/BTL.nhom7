<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h3 class="fw-bold text-uppercase" style="letter-spacing: 2px; color: var(--primary-color, #8B1A2F);">CHƯƠNG TRÌNH KHUYẾN MẠI</h3>
        <p class="text-muted small">Cơ hội sở hữu các thiết kế thời trang nữ thanh lịch với mức giá ưu đãi nhất</p>
        <div class="mx-auto" style="width: 50px; height: 2px; background-color: var(--primary-color, #8B1A2F);"></div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $percent = round((1 - ($p['sale_price'] / $p['price'])) * 100);
                ?>
                <div class="col">
                    <div class="card card-product h-100 position-relative border-0 shadow-sm">
                        <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 small fw-bold m-2 rounded">
                            -<?= $percent ?>%
                        </span>

                        <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="overflow-hidden rounded">
                            <img src="uploads/<?= htmlspecialchars($thumb) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 style="height: 340px; object-fit: cover;">
                        </a>

                        <div class="card-body px-1 py-3 text-center">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($p['color'] ?? '') ?></div>
                            <h6 class="card-title mb-2">
                                <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h6>
                            <div class="price-box">
                                <span class="text-danger fw-bold"><?= number_format($p['sale_price']) ?>đ</span>
                                <span class="text-decoration-line-through text-muted small ms-2"><?= number_format($p['price']) ?>đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Hiện chưa có sản phẩm nào đang giảm giá.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>