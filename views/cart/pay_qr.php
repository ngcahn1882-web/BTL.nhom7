<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5 text-center">
    <div class="card border-0 shadow-sm p-4 mx-auto" style="max-width: 520px;">
        <h4 class="fw-bold text-success mb-2"><i class="fa-solid fa-circle-check me-2"></i>ĐẶT HÀNG THÀNH CÔNG</h4>
        <p class="text-secondary small">Mã đơn hàng của bạn: <strong>#<?= $orderId ?></strong></p>

        <div class="my-3 p-3 bg-light rounded border">
            <img src="<?= $qrUrl ?>" alt="VietQR" class="img-fluid rounded mb-3" style="max-width: 280px;">
            <p class="mb-1 fw-bold fs-5 text-danger"><?= number_format($order['total']) ?>đ</p>
            <p class="small text-muted mb-0">Nội dung: <strong>RUBIES<?= $orderId ?></strong></p>
            <p class="small text-muted">Ngân hàng MB Bank - Số TK: <strong><?= $accountNo ?></strong> (<?= $accountName ?>)</p>
        </div>

        <a href="index.php?url=profile/index" class="btn btn-dark w-100 py-2 fw-semibold">
            Theo dõi đơn hàng của bạn
        </a>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>