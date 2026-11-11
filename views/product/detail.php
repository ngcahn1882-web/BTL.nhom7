<?php require_once 'views/layouts/header.php'; ?>

<div class="container my-5">
    <div class="row product-detail g-5">
        <!-- Cột ảnh sản phẩm -->
        <div class="col-md-6">
            <?php 
                $images = explode(',', $product['image']); 
                $mainImg = trim($images[0] ?? 'default.jpg');
            ?>
            <div class="main-image-container border rounded bg-light text-center mb-3 position-relative" style="height: 540px; overflow: hidden;">
                <img src="uploads/<?= htmlspecialchars($mainImg) ?>" id="main-product-img" style="width:100%; height:100%; object-fit: contain;">
            </div>
            <div class="d-flex gap-2 overflow-auto pb-2">
                <?php foreach($images as $img): ?>
                    <img src="uploads/<?= htmlspecialchars(trim($img)) ?>" 
                         onclick="document.getElementById('main-product-img').src=this.src"
                         style="width: 75px; height: 95px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; border-radius: 4px;">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột thông tin sản phẩm -->
        <div class="col-md-6">
            <!-- Tên sản phẩm + Nút Yêu thích ở tiêu đề -->
            <div class="d-flex justify-content-between align-items-start mb-1">
                <h2 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($product['name']) ?></h2>
                <button type="button" 
                        class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center p-0" 
                        id="btn-fav-top"
                        onclick="toggleFavorite(<?= $product['id'] ?>)" 
                        title="Thêm vào yêu thích"
                        style="width: 44px; height: 44px; flex-shrink: 0;">
                    <i class="<?= !empty($isFavorite) ? 'fa-solid text-danger' : 'fa-regular text-secondary' ?> fa-heart fs-4" id="fav-icon"></i>
                </button>
            </div>

            <p class="text-muted small">Mã sản phẩm: <?= $product['id'] ?></p>

            <!-- Hiển thị tình trạng kho hàng -->
            <div id="variant-status" class="fw-semibold fs-6 mb-3">
                <span class="text-muted">Tổng tồn kho toàn bộ phân loại: <strong><?= (int)$product['stock'] ?></strong> sản phẩm</span>
                <div class="text-secondary small mt-1">Vui lòng chọn Màu và Size bên dưới</div>
            </div>

            <!-- Giá bán -->
            <div class="mb-3">
                <?php if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0): ?>
                    <span class="text-danger fw-bold fs-3"><?= number_format($product['sale_price']) ?>đ</span>
                    <span class="text-decoration-line-through text-muted ms-2 fs-5"><?= number_format($product['price']) ?>đ</span>
                <?php else: ?>
                    <span class="text-danger fw-bold fs-3"><?= number_format($product['price']) ?>đ</span>
                <?php endif; ?>
            </div>

            <p class="text-secondary" style="line-height: 1.7;"><?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả')) ?></p>

            <!-- Chọn màu -->
            <?php if (!empty($colors)): ?>
                <h6 class="fw-bold mt-4">Chọn màu:</h6>
                <div class="d-flex gap-2 mb-3">
                    <input type="hidden" id="selected_color" value="">
                    <?php foreach ($colors as $c): ?>
                        <button type="button" class="btn btn-outline-dark color-btn btn-sm px-3" onclick="selectOption('color', this, '<?= htmlspecialchars($c, ENT_QUOTES) ?>')">
                            <?= htmlspecialchars($c) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Chọn size -->
            <?php if (!empty($sizes)): ?>
                <h6 class="fw-bold">Chọn size:</h6>
                <div class="d-flex gap-2 mb-4">
                    <input type="hidden" id="selected_size" value="">
                    <?php foreach ($sizes as $s): ?>
                        <button type="button" class="btn btn-outline-dark size-btn btn-sm px-3" onclick="selectOption('size', this, '<?= htmlspecialchars($s, ENT_QUOTES) ?>')">
                            <?= htmlspecialchars($s) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Số lượng -->
            <div class="d-flex align-items-center mb-4">
                <span class="me-3 fw-semibold">Số lượng:</span>
                <input type="number" id="quantity" class="form-control text-center" value="1" min="1" style="width: 80px;">
            </div>

            <!-- Cụm nút Mua Hàng & Nút Yêu Thích -->
            <div class="d-flex gap-2 align-items-center">
                <button type="button" id="btn-add-cart" class="btn btn-outline-danger flex-grow-1 py-3 fw-bold" onclick="handleBuy('add')">
                    Thêm Vào Giỏ Hàng
                </button>
                <button type="button" id="btn-buy-now" class="btn btn-danger flex-grow-1 py-3 fw-bold" onclick="handleBuy('buy')">
                    Mua Ngay
                </button>
                <button type="button" 
                        class="btn btn-outline-secondary py-3 px-3 d-flex align-items-center justify-content-center" 
                        onclick="toggleFavorite(<?= $product['id'] ?>)" 
                        title="Lưu yêu thích">
                    <i class="<?= !empty($isFavorite) ? 'fa-solid text-danger' : 'fa-regular' ?> fa-heart fs-5" id="fav-icon-bottom"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const variantsList = <?= json_encode($variants ?? [], JSON_UNESCAPED_UNICODE); ?>;
let currentStock = 0;

function selectOption(type, btn, val) {
    document.querySelectorAll('.' + type + '-btn').forEach(b => b.classList.remove('btn-dark', 'text-white'));
    btn.classList.add('btn-dark', 'text-white');
    document.getElementById('selected_' + type).value = val;
    checkStock();
}

function checkStock() {
    const color = document.getElementById('selected_color')?.value.trim();
    const size  = document.getElementById('selected_size')?.value.trim();
    const statusEl = document.getElementById('variant-status');
    const btnAdd = document.getElementById('btn-add-cart');
    const btnBuy = document.getElementById('btn-buy-now');

    if (!color || !size) return;

    const matched = variantsList.find(v => v.color.trim() === color && v.size.trim() === size);
    if (!matched || parseInt(matched.stock) <= 0) {
        statusEl.innerHTML = `<span class="badge bg-danger p-2 fs-6">❌ Màu ${color} / Size ${size} hiện đã hết hàng</span>`;
        currentStock = 0;
        btnAdd.disabled = true;
        btnBuy.disabled = true;
    } else {
        currentStock = parseInt(matched.stock);
        statusEl.innerHTML = `
            <span class="badge bg-success p-2 fs-6">✅ Màu ${color} / Size ${size}: Còn ${currentStock} sản phẩm</span>
            <span class="text-muted small ms-2">(Kho tổng: <?= (int)$product['stock'] ?>)</span>
        `;
        btnAdd.disabled = false;
        btnBuy.disabled = false;
    }
}

function handleBuy(action) {
    const color = document.getElementById('selected_color')?.value;
    const size  = document.getElementById('selected_size')?.value;
    const qty   = document.getElementById('quantity')?.value || 1;

    if (!color) { alert('Vui lòng chọn màu sắc!'); return; }
    if (!size)  { alert('Vui lòng chọn kích cỡ!'); return; }
    if (currentStock <= 0) { alert('Sản phẩm tạm thời hết hàng!'); return; }

    window.location.href = `index.php?url=cart/add&id=<?= $product['id'] ?>&color=${encodeURIComponent(color)}&size=${encodeURIComponent(size)}&qty=${qty}&action=${action}`;
}

// Xử lý Thả tim yêu thích qua AJAX
function toggleFavorite(productId) {
    fetch(`index.php?url=product/toggleFav&id=${productId}`)
        .then(res => res.json())
        .then(data => {
            if (data.need_login) {
                alert(data.msg);
                window.location.href = 'index.php?url=auth/login';
                return;
            }
            if (data.ok) {
                const iconTop = document.getElementById('fav-icon');
                const iconBottom = document.getElementById('fav-icon-bottom');
                if (data.isAdded) {
                    if (iconTop) iconTop.className = 'fa-solid fa-heart text-danger fs-4';
                    if (iconBottom) iconBottom.className = 'fa-solid fa-heart text-danger fs-5';
                } else {
                    if (iconTop) iconTop.className = 'fa-regular fa-heart text-secondary fs-4';
                    if (iconBottom) iconBottom.className = 'fa-regular fa-heart fs-5';
                }
            }
        })
        .catch(err => console.error(err));
}
</script>
<!-- ============================================================= -->
<!-- KHỐI 1: ĐÁNH GIÁ SẢN PHẨM (REVIEWS) -->
<!-- ============================================================= -->
<div class="container my-5 pt-4 border-top" id="review-section">
    <!-- Giới hạn chiều rộng ở giữa để giao diện không bị bè sang hai mép màn hình -->
    <div style="max-width: 900px; margin: 0 auto;">

        <!-- Tiêu đề căn giữa chuẩn phong cách Rubies -->
        <div class="text-center mb-4">
            <h4 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1.5px;">
                Đánh Giá Sản Phẩm (<?= count($reviews) ?>)
            </h4>
            
            <?php if ($avgRating !== null): ?>
                <!-- ĐÃ CÓ ĐÁNH GIÁ: Tính sao trung bình -->
                <div class="text-warning fs-5 mt-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fa-solid fa-star <?= ($i <= round($avgRating)) ? 'text-warning' : 'text-secondary opacity-25' ?>"></i>
                    <?php endfor; ?>
                    <span class="text-dark fw-bold fs-6 ms-2"><?= number_format($avgRating, 1) ?>/5.0</span>
                </div>
            <?php else: ?>
                <!-- CHƯA CÓ ĐÁNH GIÁ -->
                <div class="text-muted small mt-1">
                    <i class="fa-regular fa-star me-1 text-secondary"></i>Chưa có đánh giá
                </div>
            <?php endif; ?>
            <div class="mx-auto mt-2" style="width: 50px; height: 2px; background-color: var(--primary-color, #8B1A2F);"></div>
        </div>

        <!-- FORM GỬI ĐÁNH GIÁ -->
        <div class="card border-0 bg-light p-4 rounded-3 mb-5 shadow-sm">
            <?php if (!isset($_SESSION['user'])): ?>
                <!-- 1. CHƯA ĐĂNG NHẬP -->
                <div class="text-center py-2">
                    <i class="fa-regular fa-comment-dots fs-3 text-secondary mb-2"></i>
                    <p class="mb-2 text-muted">Vui lòng đăng nhập tài khoản đã mua sản phẩm này để gửi đánh giá.</p>
                    <a href="index.php?url=auth/login" class="btn btn-dark btn-sm px-4">Đăng nhập ngay</a>
                </div>

            <?php elseif ($hasReviewed): ?>
                <!-- 2. ĐÃ ĐÁNH GIÁ RỒI: Khóa form và hiện thông báo xác nhận -->
                <div class="text-center py-3">
                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                    <h6 class="fw-bold text-success mb-1">Bạn đã gửi đánh giá cho sản phẩm này</h6>
                    <p class="text-muted small mb-0">Cảm ơn bạn đã chia sẻ cảm nhận thực tế giúp cộng đồng mua sắm của Rubies!</p>
                </div>

            <?php elseif ($canReview): ?>
                <!-- 3. ĐÃ MUA VÀ CHƯA ĐÁNH GIÁ: Hiển thị form -->
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-pen-to-square me-2 text-danger"></i>Viết nhận xét của bạn</h6>
                <form action="index.php?url=product/addReview" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mức độ hài lòng:</label>
                        <div class="rating-stars d-flex gap-2 text-secondary fs-4" style="cursor: pointer;" id="star-picker">
                            <i class="fa-solid fa-star text-warning" data-val="1"></i>
                            <i class="fa-solid fa-star text-warning" data-val="2"></i>
                            <i class="fa-solid fa-star text-warning" data-val="3"></i>
                            <i class="fa-solid fa-star text-warning" data-val="4"></i>
                            <i class="fa-solid fa-star text-warning" data-val="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating-val" value="5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nội dung đánh giá:</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Chất liệu vải thế nào? Form dáng mặc vừa vặn không? Hãy chia sẻ trải nghiệm của bạn nhé..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tải lên hình ảnh thực tế (nếu có):</label>
                        <input type="file" name="review_image" class="form-control form-control-sm" accept="image/*" id="review-img-input" style="max-width: 350px;">
                        <div id="review-img-preview" class="mt-2"></div>
                    </div>

                    <button type="submit" class="btn btn-danger px-4 fw-semibold" style="background-color: #8B1A2F !important; border: none;">
                        Gửi Đánh Giá
                    </button>
                </form>

            <?php else: ?>
                <!-- 4. CHƯA TỪNG MUA SẢN PHẨM NÀY HOẶC ĐƠN CHƯA GIAO THÀNH CÔNG -->
                <div class="text-center py-3 text-muted">
                    <i class="fa-solid fa-shield-halved text-secondary fs-4 mb-2 d-block"></i>
                    <p class="mb-0 small">Chỉ những khách hàng <strong>đã mua sản phẩm này và nhận hàng thành công</strong> mới có thể gửi đánh giá.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- DANH SÁCH BÌNH LUẬN ĐÃ CÓ -->
        <div class="review-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="border-bottom pb-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="fw-bold me-2"><?= htmlspecialchars($rev['user_name']) ?></span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small fw-normal">
                                    <i class="fa-solid fa-circle-check me-1"></i>Đã mua hàng
                                </span>
                            </div>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></small>
                        </div>

                        <div class="text-warning small mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star <?= ($i <= $rev['rating']) ? 'text-warning' : 'text-secondary opacity-25' ?>"></i>
                            <?php endfor; ?>
                        </div>

                        <p class="mb-2 text-dark" style="line-height: 1.6; font-size: 14px;">
                            <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                        </p>

                        <?php if (!empty($rev['image'])): ?>
                            <div class="mt-2">
                                <img src="uploads/<?= htmlspecialchars($rev['image']) ?>" 
                                     class="rounded border shadow-sm" 
                                     style="width: 90px; height: 90px; object-fit: cover; cursor: pointer;" 
                                     onclick="window.open('uploads/<?= htmlspecialchars($rev['image']) ?>')" 
                                     title="Xem ảnh đầy đủ">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên trải nghiệm và chia sẻ!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ============================================================= -->
<!-- KHỐI 2: SẢN PHẨM LIÊN QUAN (CÙNG DANH MỤC CON) -->
<!-- ============================================================= -->
<div class="container my-5 pt-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold text-uppercase" style="letter-spacing: 1.5px;">Sản Phẩm Cùng Danh Mục</h4>
        <p class="text-muted small">Tất cả thiết kế cùng phân loại trong bộ sưu tập (<?= count($related) ?> sản phẩm)</p>
        <div class="mx-auto" style="width: 50px; height: 2px; background-color: #333;"></div>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if (!empty($related)): ?>
            <?php foreach ($related as $rel): ?>
                <?php 
                    $relImgs = explode(',', $rel['image'] ?? '');
                    $relThumb = trim($relImgs[0] ?? 'default.jpg');
                    $hasRelDiscount = (!empty($rel['sale_price']) && (float)$rel['sale_price'] > 0);
                ?>
                <div class="col">
                    <div class="card card-product h-100 border-0 shadow-sm position-relative">
                        <?php if ($hasRelDiscount): ?>
                            <?php $percent = round((1 - ($rel['sale_price'] / $rel['price'])) * 100); ?>
                            <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 small fw-bold m-2 rounded z-1">
                                -<?= $percent ?>%
                            </span>
                        <?php endif; ?>

                        <a href="index.php?url=product/detail/<?= $rel['id'] ?>" class="overflow-hidden rounded">
                            <img src="uploads/<?= htmlspecialchars($relThumb) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($rel['name']) ?>" 
                                 style="height: 330px; object-fit: cover;">
                        </a>

                        <div class="card-body px-1 py-3 text-center">
                            <div class="text-muted small mb-1"><?= htmlspecialchars($rel['color'] ?? '') ?></div>
                            <h6 class="card-title mb-2">
                                <a href="index.php?url=product/detail/<?= $rel['id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($rel['name']) ?>
                                </a>
                            </h6>
                            <div class="price-box">
                                <?php if ($hasRelDiscount): ?>
                                    <span class="text-danger fw-bold"><?= number_format($rel['sale_price']) ?>đ</span>
                                    <span class="text-decoration-line-through text-muted small ms-2"><?= number_format($rel['price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="text-dark fw-bold"><?= number_format($rel['price']) ?>đ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-4 text-muted">
                <p>Không có sản phẩm nào khác trong danh mục này.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================= -->
<!-- SCRIPT CHỌN SỐ SAO VÀ XEM TRƯỚC ẢNH ĐÁNH GIÁ -->
<!-- ============================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Xử lý click chọn số sao đánh giá
    const starPicker = document.getElementById('star-picker');
    const ratingInput = document.getElementById('rating-val');

    if (starPicker && ratingInput) {
        const stars = starPicker.querySelectorAll('i');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = parseInt(this.getAttribute('data-val'));
                ratingInput.value = val;
                stars.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-val'));
                    if (sVal <= val) {
                        s.className = 'fa-solid fa-star text-warning';
                    } else {
                        s.className = 'fa-solid fa-star text-secondary opacity-25';
                    }
                });
            });
        });
    }

    // 2. Xem trước ảnh đánh giá khi người dùng tải file lên
    const imgInput = document.getElementById('review-img-input');
    const imgPreview = document.getElementById('review-img-preview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', function(e) {
            imgPreview.innerHTML = '';
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    imgPreview.innerHTML = `
                        <img src="${ev.target.result}" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        <small class="text-muted ms-2">Ảnh đã chọn</small>
                    `;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>