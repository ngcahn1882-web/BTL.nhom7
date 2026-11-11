<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <h3 class="fw-bold mb-4">GIỎ HÀNG CỦA BẠN</h3>

    <?php if (empty($items)): ?>
        <div class="text-center py-5 bg-light rounded border">
            <i class="fa-solid fa-bag-shopping text-secondary mb-3" style="font-size: 48px;"></i>
            <p class="text-secondary fs-5">Giỏ hàng của bạn đang trống!</p>
            <a href="index.php?url=home/index" class="btn btn-dark px-4 py-2 mt-2">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form action="index.php?url=cart/prepareCheckout" method="POST">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom ps-2">
                            <input class="form-check-input me-2" type="checkbox" id="selectAll" checked>
                            <label class="form-check-label fw-bold" for="selectAll">Chọn tất cả sản phẩm</label>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    <?php foreach ($items as $key => $item): ?>
                                    <tr>
                                        <td style="width: 30px;">
                                            <input class="form-check-input cart-item-check" type="checkbox" 
                                                   name="selected_items[]" value="<?= htmlspecialchars($key) ?>" checked>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" 
                                                     class="rounded border" style="width: 65px; height: 85px; object-fit: cover;">
                                                <div>
                                                    <a href="index.php?url=product/detail/<?= $item['id'] ?>" class="text-dark fw-bold text-decoration-none">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                    <div class="text-muted small">Phân loại: <?= htmlspecialchars($item['color']) ?> / <?= htmlspecialchars($item['size']) ?></div>
                                                    <div class="text-danger fw-semibold"><?= number_format($item['price']) ?>đ</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="width: 120px;">
                                            <input type="number" value="<?= (int)$item['qty'] ?>" min="1" 
                                                   class="form-control form-control-sm text-center" 
                                                   onchange="updateCartQty('<?= htmlspecialchars($key) ?>', this.value)">
                                        </td>
                                        <td class="text-end fw-bold">
                                            <?= number_format($item['price'] * $item['qty']) ?>đ
                                        </td>
                                        <td class="text-end">
                                            <a href="index.php?url=cart/remove/<?= urlencode($key) ?>" class="text-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">TỔNG ĐƠN HÀNG</h5>
                        <p class="text-muted small mb-3">Đơn hàng từ 1.000.000đ sẽ được <strong>Miễn phí vận chuyển</strong> toàn quốc.</p>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fs-5 fw-bold">Tạm tính:</span>
                            <span class="text-danger fw-bold fs-4"><?= number_format($total) ?>đ</span>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold text-uppercase shadow-sm">
                            Tiến hành thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.cart-item-check').forEach(c => c.checked = this.checked);
});

function updateCartQty(key, val) {
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'index.php?url=cart/update';
    f.innerHTML = `<input type="hidden" name="key" value="${key}"><input type="hidden" name="qty" value="${val}">`;
    document.body.appendChild(f);
    f.submit();
}
</script>

<?php require_once 'views/layouts/footer.php'; ?>