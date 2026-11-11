<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Danh mục -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3 border-bottom pb-2">DANH MỤC SẢN PHẨM</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="index.php?url=category/index" class="text-decoration-none <?= ($currentId == 0) ? 'fw-bold text-danger' : 'text-dark' ?>">
                            Tất cả sản phẩm
                        </a>
                    </li>
                    <?php 
                    $parents = array_filter($categories, fn($c) => empty($c['parent_id']));
                    foreach ($parents as $p): 
                        $children = array_filter($categories, fn($c) => $c['parent_id'] == $p['id']);
                    ?>
                        <li class="mb-2">
                            <a href="index.php?url=category/index/<?= $p['id'] ?>" class="text-decoration-none <?= ($currentId == $p['id']) ? 'fw-bold text-danger' : 'text-dark fw-semibold' ?>">
                                <?= htmlspecialchars($p['name']) ?>
                            </a>
                            <?php if (!empty($children)): ?>
                                <ul class="list-unstyled ps-3 mt-1 small">
                                    <?php foreach ($children as $sub): ?>
                                        <li class="mb-1">
                                            <a href="index.php?url=category/index/<?= $sub['id'] ?>" class="text-decoration-none <?= ($currentId == $sub['id']) ? 'fw-bold text-danger' : 'text-secondary' ?>">
                                                - <?= htmlspecialchars($sub['name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-uppercase mb-0"><?= htmlspecialchars($catName) ?></h4>
                <span class="text-muted small">Hiển thị <strong><?= count($products) ?></strong> sản phẩm</span>
            </div>

            <div class="row row-cols-2 row-cols-md-3 g-4">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
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
                                         style="height: 320px; object-fit: cover;">
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
                        <p class="text-muted">Chưa có sản phẩm nào thuộc danh mục này.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>