<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <h3 class="fw-bold mb-4 text-center">THANH TOÁN ĐƠN HÀNG</h3>

    <form action="index.php?url=cart/processOrder" method="POST">
        <div class="row g-4">
            <!-- Form thông tin giao hàng -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Thông tin nhận hàng</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Số điện thoại (10 chữ số) <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control" placeholder="0986xxxxxx" pattern="[0][0-9]{9}" required>
                    </div>

                    <!-- Cascading Dropdown Tỉnh -> Huyện -> Xã[cite: 7] -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                            <select id="select-province" name="city" class="form-select" required>
                                <option value="">-- Chọn Tỉnh --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quận / Huyện <span class="text-danger">*</span></label>
                            <select id="select-district" name="district" class="form-select" disabled required>
                                <option value="">-- Quận / Huyện --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phường / Xã <span class="text-danger">*</span></label>
                            <select id="select-ward" name="ward" class="form-select" disabled required>
                                <option value="">-- Phường / Xã --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ cụ thể (Số nhà, ngõ, đường) <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control" required placeholder="Ví dụ: Số 12 ngõ 45...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Ghi chú giao hàng</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Giao giờ hành chính..."></textarea>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2">Phương thức thanh toán</h5>
                    <div class="form-check p-3 border rounded mb-2 bg-light">
                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="p_cod" value="COD" checked>
                        <label class="form-check-label fw-semibold" for="p_cod">Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="form-check p-3 border rounded bg-light">
                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="p_bank" value="BANKING">
                        <label class="form-check-label fw-semibold" for="p_bank">Chuyển khoản VietQR (Tự động hiển thị mã QR)</label>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4 bg-light">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">SẢN PHẨM ĐẶT MUA</h5>
                    <div class="mb-3" style="max-height: 280px; overflow-y: auto;">
                        <?php foreach ($items as $it): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 bg-white p-2 rounded border">
                            <div class="d-flex align-items-center gap-2">
                                <img src="uploads/<?= htmlspecialchars($it['image']) ?>" style="width: 45px; height: 60px; object-fit: cover;" class="rounded border">
                                <div>
                                    <div class="fw-bold small text-truncate" style="max-width: 170px;"><?= htmlspecialchars($it['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($it['color']) ?> / <?= htmlspecialchars($it['size']) ?> x <?= $it['qty'] ?></small>
                                </div>
                            </div>
                            <span class="fw-bold text-danger"><?= number_format($it['price'] * $it['qty']) ?>đ</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Tiền hàng:</span>
                        <span class="fw-semibold"><?= number_format($subtotal) ?>đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span>Phí vận chuyển:</span>
                        <span class="fw-semibold <?= $shipFee == 0 ? 'text-success' : '' ?>">
                            <?= $shipFee == 0 ? 'Miễn phí' : number_format($shipFee) . 'đ' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 fw-bold">Tổng thanh toán:</span>
                        <span class="text-danger fw-bold fs-4"><?= number_format($total) ?>đ</span>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-3 fw-bold text-uppercase shadow-sm">
                        ĐẶT HÀNG
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- SCRIPT FETCH OPEN API PROVINCES THEO ĐÚNG TÀI LIỆU BTL[cite: 7] -->
<script>
const provinceSelect = document.getElementById('select-province');
const districtSelect = document.getElementById('select-district');
const wardSelect     = document.getElementById('select-ward');

// Load Tỉnh/Thành
fetch('https://provinces.open-api.vn/api/?depth=1')
    .then(r => r.json())
    .then(data => {
        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.dataset.code = p.code;
            opt.textContent = p.name;
            provinceSelect.appendChild(opt);
        });
    });

// Chọn Tỉnh -> Load Huyện
provinceSelect.addEventListener('change', function () {
    const code = this.options[this.selectedIndex].dataset.code;
    districtSelect.innerHTML = '<option value="">-- Quận / Huyện --</option>';
    districtSelect.disabled = true;
    wardSelect.innerHTML = '<option value="">-- Phường / Xã --</option>';
    wardSelect.disabled = true;

    if (!code) return;

    fetch(`https://provinces.open-api.vn/api/p/${code}?depth=2`)
        .then(r => r.json())
        .then(data => {
            data.districts.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.name;
                opt.dataset.code = d.code;
                opt.textContent = d.name;
                districtSelect.appendChild(opt);
            });
            districtSelect.disabled = false;
        });
});

// Chọn Huyện -> Load Xã
districtSelect.addEventListener('change', function () {
    const code = this.options[this.selectedIndex].dataset.code;
    wardSelect.innerHTML = '<option value="">-- Phường / Xã --</option>';
    wardSelect.disabled = true;

    if (!code) return;

    fetch(`https://provinces.open-api.vn/api/d/${code}?depth=2`)
        .then(r => r.json())
        .then(data => {
            data.wards.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.name;
                opt.dataset.code = w.code;
                opt.textContent = w.name;
                wardSelect.appendChild(opt);
            });
            wardSelect.disabled = false;
        });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>