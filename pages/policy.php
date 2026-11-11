<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5" style="min-height: 520px;">
    <!-- Tabs chuyển đổi giữa các chính sách[cite: 5] -->
    <div class="d-flex flex-wrap gap-2 mb-4 border-bottom pb-3">
        <a href="index.php?url=page/policy&type=loyalty" class="btn btn-sm <?= ($type === 'loyalty') ? 'btn-dark' : 'btn-outline-dark' ?>">Chính sách KH Thân Thiết</a>
        <a href="index.php?url=page/policy&type=return" class="btn btn-sm <?= ($type === 'return') ? 'btn-dark' : 'btn-outline-dark' ?>">Chính sách Đổi & Trả hàng</a>
        <a href="index.php?url=page/policy&type=warranty" class="btn btn-sm <?= ($type === 'warranty') ? 'btn-dark' : 'btn-outline-dark' ?>">Chính sách Bảo Hành</a>
        <a href="index.php?url=page/policy&type=usage" class="btn btn-sm <?= ($type === 'usage') ? 'btn-dark' : 'btn-outline-dark' ?>">Hướng dẫn Sử Dụng</a>
        <a href="index.php?url=page/policy&type=about" class="btn btn-sm <?= ($type === 'about') ? 'btn-dark' : 'btn-outline-dark' ?>">Về Chúng Tôi</a>
    </div>

    <h4 class="fw-bold text-uppercase mb-4 text-danger"><?= htmlspecialchars($title) ?></h4>
    <div class="policy-content bg-light p-4 rounded border" style="line-height: 1.8; font-size: 15px;">
        <?= $content ?>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>