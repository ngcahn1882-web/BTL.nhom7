<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold text-center mb-4">ĐĂNG NHẬP</h4>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?url=auth/login" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ Email</label>
                        <!-- Giữ lại giá trị email vừa nhập nếu sai mật khẩu -->
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu" autofocus>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold text-uppercase mb-3">
                        Đăng nhập
                    </button>
                </form>

                <div class="text-center border-top pt-3">
                    <p class="text-secondary small mb-1">Chưa có tài khoản?</p>
                    <a href="index.php?url=auth/register" class="text-danger fw-bold text-decoration-none">
                        Tạo tài khoản mới ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>