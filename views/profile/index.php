<?php 
require_once 'views/layouts/header.php'; 

function getStatusBadge($status) {
    switch ($status) {
        case 'pending':   return ['label' => 'Chờ xác nhận', 'color' => '#f39c12'];
        case 'confirmed': return ['label' => 'Đã xác nhận',  'color' => '#2980b9'];
        case 'shipping':  return ['label' => 'Đang giao',    'color' => '#8e44ad'];
        case 'done':      return ['label' => 'Hoàn thành',   'color' => '#27ae60'];
        case 'cancelled': return ['label' => 'Đã hủy',       'color' => '#e74c3c'];
        default:          return ['label' => $status,         'color' => '#999'];
    }
}
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- SIDEBAR -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" 
                     style="width: 65px; height: 65px; font-size: 24px; background-color: #8B1A2F !important;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h6 class="fw-bold mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                <small class="text-secondary"><?= htmlspecialchars($user['email']) ?></small>
                
                <div class="list-group list-group-flush mt-4 text-start">
                    <a href="index.php?url=profile/index&tab=orders" class="list-group-item list-group-item-action border-0 py-2 <?= $tab === 'orders' ? 'fw-bold text-danger bg-light' : '' ?>">
                        <i class="fa-regular fa-rectangle-list me-2"></i> Đơn hàng của bạn
                    </a>
                    <a href="index.php?url=profile/index&tab=info" class="list-group-item list-group-item-action border-0 py-2 <?= $tab === 'info' ? 'fw-bold text-danger bg-light' : '' ?>">
                        <i class="fa-regular fa-user me-2"></i> Thông tin tài khoản
                    </a>
                    <a href="index.php?url=profile/index&tab=password" class="list-group-item list-group-item-action border-0 py-2 <?= $tab === 'password' ? 'fw-bold text-danger bg-light' : '' ?>">
                        <i class="fa-regular fa-lock me-2"></i> Đổi mật khẩu
                    </a>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm p-4">
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-<?= $msg_type ?> py-2 small mb-4">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <!-- TAB ĐƠN HÀNG CỦA BẠN -->
                <?php if ($tab === 'orders'): ?>
                    <h5 class="fw-bold mb-4 border-bottom pb-2">ĐƠN HÀNG CỦA BẠN</h5>
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-bag-shopping fs-1 text-muted mb-3"></i>
                            <p class="text-muted">Bạn chưa có đơn hàng nào.</p>
                            <a href="index.php?url=home/index" class="btn btn-dark btn-sm">Mua sắm ngay</a>
                        </div>
                    <?php else: ?>
                        <?php 
                        $db = Database::getConnection();

                        // Lấy danh sách ID các sản phẩm người dùng này đã từng đánh giá (đảm bảo đồng bộ với trang chi tiết)
                        $reviewedProductIds = [];
                        if (!empty($user['id'])) {
                            $stmtRev = $db->prepare("SELECT product_id FROM reviews WHERE user_id = ?");
                            $stmtRev->execute([(int)$user['id']]);
                            $reviewedProductIds = $stmtRev->fetchAll(PDO::FETCH_COLUMN);
                        }

                        foreach ($orders as $order): 
                            $badge = getStatusBadge($order['status']);
                            $stmtItems = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmtItems->execute([$order['id']]);
                            $items = $stmtItems->fetchAll();
                        ?>
                        <div class="card mb-3 border">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <span class="fw-bold text-danger">Đơn #<?= $order['id'] ?></span>
                                    <span class="text-muted small ms-2"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                                <span class="badge" style="background-color: <?= $badge['color'] ?>;">
                                    <?= $badge['label'] ?>
                                </span>
                            </div>

                            <div class="card-body p-3">
                                <?php foreach ($items as $it): 
                                    $thumb = trim(explode(',', $it['image'])[0]);
                                    $isReviewed = in_array((int)$it['product_id'], array_map('intval', $reviewedProductIds));
                                ?>
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="uploads/<?= htmlspecialchars($thumb) ?>" class="rounded border" style="width: 55px; height: 70px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold small"><?= htmlspecialchars($it['name']) ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($it['color']) ?> / <?= htmlspecialchars($it['size']) ?> x <?= $it['quantity'] ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold"><?= number_format($it['price'] * $it['quantity']) ?>đ</div>
                                        
                                        <!-- CHỈ ĐƠN HOÀN THÀNH MỚI XÉT ĐÁNH GIÁ -->
                                        <?php if ($order['status'] === 'done'): ?>
                                            <?php if ($isReviewed): ?>
                                                <!-- ĐÃ ĐÁNH GIÁ RỒI (Ở TRANG NÀY HOẶC TRANG CHI TIẾT SẢN PHẨM) -->
                                                <span class="badge bg-light text-secondary border py-1 px-2 fw-normal mt-1">
                                                    <i class="fa-solid fa-circle-check text-success me-1"></i>Đã đánh giá
                                                </span>
                                            <?php else: ?>
                                                <!-- CHƯA ĐÁNH GIÁ: MỞ MODAL ĐÁNH GIÁ -->
                                                <button type="button" class="btn btn-sm btn-outline-warning mt-1 py-0 px-2 text-dark fw-semibold"
                                                        onclick="openReviewModal(<?= (int)$it['product_id'] ?>, '<?= htmlspecialchars(addslashes($it['name'])) ?>', 'uploads/<?= htmlspecialchars($thumb) ?>')">
                                                    <i class="fa-regular fa-star text-warning me-1"></i>Đánh giá
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="small text-muted"><?= $order['payment_method'] === 'COD' ? 'Thanh toán COD' : 'Chuyển khoản VietQR' ?></span>
                                <div class="fw-bold">
                                    Tổng thanh toán: <span class="text-danger fs-5"><?= number_format($order['total']) ?>đ</span>
                                </div>
                            </div>

                            <!-- Nút Hủy / Hoàn hàng -->
                            <div class="p-2 border-top bg-white d-flex justify-content-end align-items-center gap-2">
                                <?php if (isset($cancelMap[$order['id']])): ?>
                                    <?php if ($cancelMap[$order['id']] === 'pending'): ?>
                                        <span class="small text-warning fw-semibold"><i class="fa-solid fa-clock me-1"></i>Đang chờ duyệt hủy</span>
                                    <?php elseif ($cancelMap[$order['id']] === 'approved'): ?>
                                        <span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Đã duyệt hủy</span>
                                    <?php elseif ($cancelMap[$order['id']] === 'rejected'): ?>
                                        <span class="small text-danger fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i>Yêu cầu hủy bị từ chối</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!isset($cancelMap[$order['id']]) && in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="openRequestModal('cancel', <?= $order['id'] ?>)">
                                        <i class="fa-solid fa-xmark me-1"></i> Yêu cầu hủy đơn
                                    </button>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'done'): ?>
                                    <?php if (!isset($returnMap[$order['id']])): ?>
                                        <button class="btn btn-sm btn-outline-purple" style="border-color:#8e44ad; color:#8e44ad;"
                                                onclick="openRequestModal('return', <?= $order['id'] ?>)">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Yêu cầu hoàn hàng
                                        </button>
                                    <?php elseif ($returnMap[$order['id']] === 'pending'): ?>
                                        <span class="small text-purple fw-semibold" style="color:#8e44ad;"><i class="fa-solid fa-clock me-1"></i>Đang chờ duyệt hoàn hàng</span>
                                    <?php elseif ($returnMap[$order['id']] === 'approved'): ?>
                                        <span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Đã duyệt hoàn hàng</span>
                                    <?php elseif ($returnMap[$order['id']] === 'rejected'): ?>
                                        <span class="small text-danger fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i>Yêu cầu hoàn bị từ chối</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <!-- TAB THÔNG TIN CÁ NHÂN -->
                <?php elseif ($tab === 'info'): ?>
                    <h5 class="fw-bold mb-4 border-bottom pb-2">THÔNG TIN TÀI KHOẢN</h5>
                    <form method="POST" action="index.php?url=profile/index" style="max-width: 440px;">
                        <input type="hidden" name="update_info" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-dark px-4 py-2">Lưu thay đổi</button>
                    </form>

                <!-- TAB ĐỔI MẬT KHẨU -->
                <?php elseif ($tab === 'password'): ?>
                    <h5 class="fw-bold mb-4 border-bottom pb-2">ĐỔI MẬT KHẨU</h5>
                    <form method="POST" action="index.php?url=profile/index" style="max-width: 440px;">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark px-4 py-2">Cập nhật mật khẩu</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Yêu cầu Hủy / Hoàn hàng -->
<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom py-3">
        <h6 class="modal-title fw-bold" id="req-modal-title"></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="index.php?url=profile/index" enctype="multipart/form-data">
          <input type="hidden" name="order_id" id="req-order-id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Lý do</label>
            <select id="req-reason-select" class="form-select mb-2" onchange="document.getElementById('req-reason').value=this.value==='other'?'':this.value">
                <option value="">-- Chọn lý do --</option>
                <option value="Đặt nhầm sản phẩm">Đặt nhầm sản phẩm</option>
                <option value="Muốn đổi size / màu khác">Muốn đổi size / màu khác</option>
                <option value="Sản phẩm bị lỗi / hư hỏng">Sản phẩm bị lỗi / hư hỏng</option>
                <option value="Không còn nhu cầu mua">Không còn nhu cầu mua</option>
                <option value="other">Lý do khác...</option>
            </select>
            <textarea id="req-reason" name="reason" class="form-control" rows="2" placeholder="Mô tả chi tiết lý do..." required></textarea>
          </div>
          <div id="req-image-wrap" class="mb-3" style="display:none;">
            <label class="form-label fw-semibold">Ảnh minh chứng <span class="text-muted fw-normal">(tùy chọn)</span></label>
            <input type="file" name="return_image" class="form-control" accept="image/*">
          </div>
          <button type="submit" id="req-submit-btn" name="request_cancel" class="btn btn-danger w-100 py-2">Gửi yêu cầu</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Đánh giá Sản phẩm Chuẩn MVC (Gửi trực tiếp vào ProductController) -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
      <div class="modal-header py-3 text-white" style="background-color: #8B1A2F !important;">
        <div class="d-flex align-items-center gap-2">
          <img id="rv-product-img" src="" class="rounded border border-light" style="width: 45px; height: 55px; object-fit: cover;">
          <div>
            <span class="small text-white-50 d-block">Đánh giá sản phẩm</span>
            <h6 class="modal-title fw-bold mb-0 text-white" id="rv-product-name"></h6>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form action="index.php?url=product/addReview" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="product_id" id="rv-product-id">
          <!-- Return URL để sau khi đánh giá sẽ tự chuyển về đúng tab đơn hàng -->
          <input type="hidden" name="return_url" value="profile/index&tab=orders">

          <!-- Chọn số sao đánh giá -->
          <div class="mb-3 text-center">
            <label class="form-label fw-semibold small d-block mb-1">Mức độ hài lòng của bạn</label>
            <div class="d-flex justify-content-center gap-2 fs-3 text-warning" id="modal-star-picker" style="cursor: pointer;">
                <i class="fa-solid fa-star" data-val="1"></i>
                <i class="fa-solid fa-star" data-val="2"></i>
                <i class="fa-solid fa-star" data-val="3"></i>
                <i class="fa-solid fa-star" data-val="4"></i>
                <i class="fa-solid fa-star" data-val="5"></i>
            </div>
            <input type="hidden" name="rating" id="modal-rating-val" value="5">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Nhận xét thực tế:</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Chất vải, kích cỡ có vừa vặn không? Hãy chia sẻ trải nghiệm thực tế nhé..." required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Tải lên hình ảnh thực tế (nếu có):</label>
            <input type="file" name="review_image" class="form-control form-control-sm" accept="image/*">
          </div>

          <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" style="background-color: #8B1A2F !important; border: none;">
            Gửi Đánh Giá Ngay
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function openRequestModal(type, orderId) {
    document.getElementById('req-order-id').value = orderId;
    document.getElementById('req-modal-title').textContent = (type === 'cancel') ? 'Yêu cầu hủy đơn #' + orderId : 'Yêu cầu hoàn hàng đơn #' + orderId;
    document.getElementById('req-submit-btn').name = (type === 'cancel') ? 'request_cancel' : 'request_return';
    document.getElementById('req-image-wrap').style.display = (type === 'return') ? 'block' : 'none';
    new bootstrap.Modal(document.getElementById('requestModal')).show();
}

function openReviewModal(prodId, prodName, prodImg) {
    document.getElementById('rv-product-id').value = prodId;
    document.getElementById('rv-product-name').textContent = prodName;
    document.getElementById('rv-product-img').src = prodImg;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý click chọn sao trong Modal
    const starPicker = document.getElementById('modal-star-picker');
    const ratingInput = document.getElementById('modal-rating-val');
    if (starPicker && ratingInput) {
        const stars = starPicker.querySelectorAll('i');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = parseInt(this.getAttribute('data-val'));
                ratingInput.value = val;
                stars.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-val'));
                    s.className = (sVal <= val) ? 'fa-solid fa-star' : 'fa-regular fa-star';
                });
            });
        });
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>