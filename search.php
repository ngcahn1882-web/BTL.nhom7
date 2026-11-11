<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <h4 class="fw-bold mb-2">KẾT QUẢ TÌM KIẾM</h4>
    <p class="text-secondary small mb-4">Tìm thấy <strong><?= count($results) ?></strong> sản phẩm cho từ khóa "<em><?= htmlspecialchars($keyword) ?></em>"</p>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $hasDiscount = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0);
                ?>
                <div class="col">
                    <div class="card card-product h-100 position-relative border-0 shadow-sm">
                        <?php if ($hasDiscount): ?>
                            <?php $percent = round((1 - ($p['sale_price'] / $p['price'])) * 100); ?>
                            <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 small fw-bold m-2 rounded">
                                -<?= $percent ?>%
                            </span>
                        <?php endif; ?>

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
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-magnifying-glass fs-1 text-muted mb-3"></i>
                <p class="text-muted">Không tìm thấy sản phẩm nào phù hợp với từ khóa.</p>
                <a href="index.php?url=home/index" class="btn btn-dark btn-sm">Xem tất cả sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>