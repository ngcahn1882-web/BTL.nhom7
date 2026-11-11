<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5" style="min-height: 500px;">
    <!-- Tiêu đề trang -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--primary-color, #8B1A2F);">SẢN PHẨM YÊU THÍCH</h4>
            <p class="text-muted small mb-0">Danh sách các mẫu thiết kế bạn đã lưu để theo dõi</p>
        </div>
        <a href="index.php?url=home/index" class="btn btn-outline-dark btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Tiếp tục xem sản phẩm
        </a>
    </div>

    <!-- Danh sách sản phẩm yêu thích -->
    <?php if (empty($favorites)): ?>
        <div class="text-center py-5 bg-light rounded border">
            <i class="fa-regular fa-heart text-muted mb-3" style="font-size: 50px;"></i>
            <h5 class="fw-bold text-secondary">Danh sách yêu thích đang trống</h5>
            <p class="text-muted small mb-3">Hãy chọn những mẫu bạn yêu thích để dễ dàng tìm lại khi mua sắm nhé!</p>
            <a href="index.php?url=category/index" class="btn btn-dark btn-sm px-4 py-2">Khám phá sản phẩm ngay</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($favorites as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $hasDiscount = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0);
                ?>
                <div class="col" id="fav-item-<?= $p['id'] ?>">
                    <div class="card card-product h-100 position-relative border-0 shadow-sm">
                        <!-- Nhãn giảm giá -->
                        <?php if ($hasDiscount): ?>
                            <?php $percent = round((1 - ($p['sale_price'] / $p['price'])) * 100); ?>
                            <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 small fw-bold m-2 rounded z-1">
                                -<?= $percent ?>%
                            </span>
                        <?php endif; ?>

                        <!-- Nút Bỏ thích nhanh -->
                        <button type="button" 
                                class="btn btn-light btn-sm position-absolute top-0 end-0 m-2 rounded-circle shadow-sm z-1 d-flex align-items-center justify-content-center"
                                style="width: 34px; height: 34px;"
                                onclick="removeFavorite(<?= $p['id'] ?>)" 
                                title="Bỏ yêu thích">
                            <i class="fa-solid fa-heart text-danger"></i>
                        </button>

                        <!-- Ảnh sản phẩm -->
                        <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="overflow-hidden rounded">
                            <img src="uploads/<?= htmlspecialchars($thumb) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 style="height: 320px; object-fit: cover;">
                        </a>

                        <!-- Thông tin sản phẩm -->
                        <div class="card-body px-2 py-3 text-center">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($p['color'] ?? '') ?></div>
                            <h6 class="card-title mb-2">
                                <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </h6>
                            <div class="price-box mb-3">
                                <?php if ($hasDiscount): ?>
                                    <span class="text-danger fw-bold"><?= number_format($p['sale_price']) ?>đ</span>
                                    <span class="text-decoration-line-through text-muted small ms-2"><?= number_format($p['price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="text-dark fw-bold"><?= number_format($p['price']) ?>đ</span>
                                <?php endif; ?>
                            </div>

                            <a href="index.php?url=product/detail/<?= $p['id'] ?>" class="btn btn-dark btn-sm w-100 py-2">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Hàm xóa sản phẩm khỏi danh sách yêu thích
function removeFavorite(productId) {
    if (!confirm('Bạn có chắc muốn bỏ sản phẩm này khỏi danh sách yêu thích?')) return;

    fetch(`index.php?url=product/toggleFav&id=${productId}`)
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                const el = document.getElementById(`fav-item-${productId}`);
                if (el) {
                    el.remove();
                }
                // Nếu xóa hết thì reload lại để hiện khung rỗng
                if (document.querySelectorAll('.card-product').length === 0) {
                    location.reload();
                }
            } else {
                location.reload();
            }
        })
        .catch(() => location.reload());
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>