<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold text-center mb-4">ĐĂNG KÝ TÀI KHOẢN</h4>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success py-2 small">
                        <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($success) ?>
                        <div class="mt-2"><a href="index.php?url=auth/login" class="fw-bold text-success text-decoration-none">Bấm vào đây để đăng nhập</a></div>
                    </div>
                <?php endif; ?>

                <form action="index.php?url=auth/register" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($name ?? '') ?>" required placeholder="Ví dụ: Nguyễn Thị Hoa">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                        <input type="password" name="repassword" class="form-control" required placeholder="Nhập lại mật khẩu">
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase mb-3">
                        Đăng ký
                    </button>
                </form>

                <div class="text-center border-top pt-3">
                    <p class="text-secondary small mb-1">Đã có tài khoản?</p>
                    <a href="index.php?url=auth/login" class="text-dark fw-bold text-decoration-none">
                        Đăng nhập ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layouts/footer.php'; ?>