<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div>
            <h4 class="fw-bold mb-1">KẾT QUẢ TÌM KIẾM</h4>
            <p class="text-secondary small mb-0">
                Tìm thấy <strong><?= $totalProducts ?? count($results) ?></strong> sản phẩm
                <?php if ($keyword !== ''): ?>
                    cho từ khóa "<em><?= htmlspecialchars($keyword) ?></em>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- THANH BỘ LỌC KHOẢNG GIÁ & SẮP XẾP -->
    <form action="index.php" method="GET" class="card border-0 bg-light p-3 mb-4 rounded-3 shadow-sm">
        <input type="hidden" name="url" value="page/search">
        <input type="hidden" name="q" value="<?= htmlspecialchars($keyword) ?>">

        <div class="row g-2 align-items-end">
            <!-- Lọc Khoảng giá -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1 text-muted">Khoảng giá</label>
                <select name="price" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả mức giá --</option>
                    <option value="under_500" <?= ($priceRange === 'under_500') ? 'selected' : '' ?>>Dưới 500.000đ</option>
                    <option value="500_1000" <?= ($priceRange === '500_1000') ? 'selected' : '' ?>>Từ 500.000đ - 1.000.000đ</option>
                    <option value="over_1000" <?= ($priceRange === 'over_1000') ? 'selected' : '' ?>>Trên 1.000.000đ</option>
                </select>
            </div>

            <!-- Sắp xếp -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1 text-muted">Sắp xếp theo</label>
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                    <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                </select>
            </div>

            <!-- Nút Lọc / Đặt lại -->
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i>Lọc
                </button>
                <?php if ($priceRange !== '' || $sort !== 'newest'): ?>
                    <a href="index.php?url=page/search&q=<?= urlencode($keyword) ?>" class="btn btn-outline-secondary btn-sm">
                        Đặt lại
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- LƯỚI SẢN PHẨM KẾT QUẢ -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $p): ?>
                <?php 
                    $images = explode(',', $p['image'] ?? '');
                    $thumb = trim($images[0] ?? 'default.jpg');
                    $hasDiscount = (!empty($p['sale_price']) && (float)$p['sale_price'] > 0 && (float)$p['price'] > 0);
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
                <p class="text-muted">Không tìm thấy sản phẩm nào phù hợp với điều kiện lọc.</p>
                <a href="index.php?url=category/index" class="btn btn-dark btn-sm">Xem tất cả sản phẩm</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- THANH PHÂN TRANG TÌM KIẾM -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <?php 
            $queryParams = http_build_query([
                'url'   => 'page/search',
                'q'     => $keyword,
                'price' => $priceRange,
                'sort'  => $sort
            ]);
        ?>
        <nav aria-label="Phân trang tìm kiếm" class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="index.php?<?= $queryParams ?>&page=<?= max(1, $currentPage - 1) ?>">
                        <i class="fa-solid fa-chevron-left small"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link <?= ($i == $currentPage) ? 'bg-danger border-danger text-white' : 'text-dark' ?>" 
                           href="index.php?<?= $queryParams ?>&page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link text-dark" href="index.php?<?= $queryParams ?>&page=<?= min($totalPages, $currentPage + 1) ?>">
                        <i class="fa-solid fa-chevron-right small"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once 'views/layouts/footer.php'; ?>