<?php
// views/admin/index.php
function statusLabel($status) {
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - RUBIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./style.css?time=<?= time(); ?>">
    <style>
        .admin-wrap { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: #1e2022; color: #cfd8dc; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 20px; text-align: center; border-bottom: 1px solid #333; }
        .sidebar-logo h2 { margin: 0; color: #fff; font-size: 22px; font-weight: bold; letter-spacing: 2px; }
        .sidebar-logo span { font-size: 11px; color: #888; }
        .sidebar-nav { flex: 1; padding: 15px 0; }
        .nav-section { padding: 8px 20px; font-size: 11px; text-transform: uppercase; color: #777; font-weight: bold; }
        .sidebar-nav a { display: flex; align-items: center; padding: 10px 20px; color: #cfd8dc; text-decoration: none; font-size: 14px; gap: 10px; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #8B1A2F; color: #fff; }
        .sidebar-footer { padding: 15px 20px; border-top: 1px solid #333; }
        .sidebar-footer a { display: block; color: #aaa; text-decoration: none; font-size: 13px; margin-bottom: 8px; }
        .sidebar-footer a:hover { color: #fff; }
        .admin-main { flex: 1; background: #f8f9fa; display: flex; flex-direction: column; overflow-x: hidden; }
        .admin-topbar { background: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e9ecef; }
        .admin-body { padding: 30px; flex: 1; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid #eef2f5; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-card h3 { margin: 0; font-size: 20px; font-weight: bold; }
        .stat-card p { margin: 0; font-size: 13px; color: #888; }
        .admin-card { background: #fff; border-radius: 10px; border: 1px solid #eef2f5; padding: 20px; margin-bottom: 25px; }
        .admin-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { background: #f8f9fa; padding: 12px; font-size: 13px; border-bottom: 1px solid #dee2e6; text-align: left; }
        .admin-table td { padding: 12px; font-size: 13px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .product-thumb { width: 45px; height: 55px; object-fit: cover; border-radius: 6px; }

        /* Badge trạng thái và dropdown rộng rãi, không rớt dòng */
        .status-badge { 
            display: inline-block !important; 
            padding: 6px 14px !important; 
            border-radius: 20px !important; 
            color: #fff !important; 
            font-size: 12px !important; 
            font-weight: 600 !important; 
            white-space: nowrap !important; 
            text-align: center !important; 
            min-width: 110px !important; 
        }
        .status-select { 
            min-width: 145px !important; 
            padding: 6px 10px !important; 
            font-size: 13px !important; 
            border-radius: 6px !important; 
            border: 1px solid #ced4da !important; 
            cursor: pointer !important; 
            background-color: #fff !important; 
        }
        .nowrap-col { white-space: nowrap !important; }

        .btn-admin-primary { background: #8B1A2F; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; }
        .btn-admin-primary:hover { background: #6b1424; color: #fff; }
        .btn-admin-secondary { background: #e9ecef; color: #333; border: none; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; }
        .btn-edit-soft { background: #e8f4fd; color: #2980b9; }
        .btn-danger-soft { background: #fde8e8; color: #e74c3c; }
        .admin-alert { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .admin-alert.success { background: #d4edda; color: #155724; }
        .admin-alert.danger { background: #f8d7da; color: #721c24; }

        /* Live chat UI */
        .livechat-wrap { display: flex; height: 580px; background: #fff; border-radius: 10px; border: 1px solid #eef2f5; overflow: hidden; }
        .livechat-users { width: 300px; border-right: 1px solid #eee; display: flex; flex-direction: column; }
        .livechat-users-header { padding: 15px 20px; font-weight: bold; border-bottom: 1px solid #eee; }
        #chat-users-body { flex: 1; overflow-y: auto; }
        .chat-user-item { padding: 12px 18px; border-bottom: 1px solid #f9f9f9; display: flex; align-items: center; gap: 12px; cursor: pointer; }
        .chat-user-item:hover, .chat-user-item.active { background: #faf4f5; }
        .chat-user-avatar { width: 38px; height: 38px; border-radius: 50%; background: #8B1A2F; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .chat-user-name { font-weight: 600; font-size: 13px; margin-bottom: 2px; }
        .chat-user-preview { font-size: 12px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .chat-user-time { font-size: 11px; color: #aaa; margin-left: auto; }
        .chat-user-unread { background: #e74c3c; color: #fff; border-radius: 10px; padding: 1px 6px; font-size: 11px; font-weight: bold; }
        .livechat-main { flex: 1; display: flex; flex-direction: column; background: #fafafa; }
        .livechat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #aaa; }
        .livechat-chat-header { padding: 15px 20px; background: #fff; border-bottom: 1px solid #eee; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        .livechat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }
        .lc-msg { max-width: 70%; display: flex; flex-direction: column; }
        .lc-msg.lc-admin { align-self: flex-end; align-items: flex-end; }
        .lc-msg.lc-user { align-self: flex-start; align-items: flex-start; }
        .lc-bubble { padding: 10px 14px; border-radius: 12px; font-size: 13px; line-height: 1.5; }
        .lc-admin .lc-bubble { background: #8B1A2F; color: #fff; border-bottom-right-radius: 2px; }
        .lc-user .lc-bubble { background: #fff; border: 1px solid #eee; color: #333; border-bottom-left-radius: 2px; }
        .lc-time { font-size: 10px; color: #aaa; margin-top: 3px; }
        .lc-sender-label { font-size: 11px; color: #888; margin-bottom: 2px; font-weight: 500; }
        .livechat-input-row { padding: 15px 20px; background: #fff; border-top: 1px solid #eee; display: flex; gap: 10px; align-items: center; }
        .livechat-input-row textarea { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 10px 14px; font-size: 13px; resize: none; outline: none; }
        .livechat-input-row button { background: #8B1A2F; color: #fff; border: none; width: 44px; height: 44px; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>

<div class="admin-wrap">
    <!-- ===== SIDEBAR ===== -->
    <div class="admin-sidebar">
        <div class="sidebar-logo">
            <h2>RUBIES</h2>
            <span>ADMIN PANEL</span>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section">Tổng quan</div>
            <a href="index.php?url=admin/index&tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : ''; ?>">
                <i class="fa-regular fa-chart-bar"></i> Dashboard
            </a>

            <div class="nav-section">Quản lý</div>
            <a href="index.php?url=admin/index&tab=orders" class="<?= $tab === 'orders' ? 'active' : ''; ?>">
                <i class="fa-regular fa-rectangle-list"></i> Đơn hàng
                <?php if ($stat_pending > 0): ?>
                    <span style="margin-left:auto;background:#e74c3c;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;"><?= $stat_pending; ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php?url=admin/index&tab=products" class="<?= in_array($tab, ['products','add_product','edit_product']) ? 'active' : ''; ?>">
                <i class="fa-regular fa-shirt"></i> Sản phẩm
            </a>
            <a href="index.php?url=admin/index&tab=categories" class="<?= in_array($tab, ['categories','edit_category']) ? 'active' : ''; ?>">
                <i class="fa-regular fa-folder"></i> Danh mục
            </a>
            <a href="index.php?url=admin/index&tab=users" class="<?= $tab === 'users' ? 'active' : ''; ?>">
                <i class="fa-regular fa-users"></i> Người dùng
            </a>
            <?php
            $stat_requests = $pdo->query("SELECT (SELECT COUNT(*) FROM order_cancellations WHERE status='pending') + (SELECT COUNT(*) FROM order_returns WHERE status='pending')")->fetchColumn();
            ?>
            <a href="index.php?url=admin/index&tab=requests" class="<?= $tab === 'requests' ? 'active' : ''; ?>">
                <i class="fa-regular fa-hand"></i> Yêu cầu hủy/hoàn
                <?php if ($stat_requests > 0): ?>
                    <span style="margin-left:auto;background:#e74c3c;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;"><?= $stat_requests; ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php?url=admin/index&tab=livechat" class="<?= $tab === 'livechat' ? 'active' : ''; ?>" id="sidebar-chat-link">
                <i class="fa-regular fa-message"></i> Chat với khách
                <span id="sidebar-unread-badge" style="display:none;margin-left:auto;background:#e74c3c;color:#fff;font-size:11px;padding:1px 7px;border-radius:10px;"></span>
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="index.php?url=home/index" target="_blank">
                <i class="fa-regular fa-arrow-up-right-from-square"></i> Xem cửa hàng
            </a>
            <a href="index.php?url=auth/logout">
                <i class="fa-regular fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
    </div>

    <!-- ===== MAIN ===== -->
    <div class="admin-main">
        <div class="admin-topbar">
            <h5 class="fw-bold mb-0">
                <?php
                $titles = [
                    'dashboard'     => 'Dashboard',
                    'orders'        => 'Quản lý đơn hàng',
                    'products'      => 'Quản lý sản phẩm',
                    'add_product'   => 'Thêm sản phẩm',
                    'edit_product'  => 'Sửa sản phẩm',
                    'categories'    => 'Quản lý danh mục',
                    'edit_category' => 'Sửa danh mục',
                    'users'         => 'Quản lý người dùng',
                    'requests'      => 'Duyệt yêu cầu hủy / hoàn',
                    'livechat'      => 'Chat với khách hàng',
                ];
                echo $titles[$tab] ?? 'Admin';
                ?>
            </h5>
            <div class="admin-user">
                <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($user['name']); ?>
            </div>
        </div>

        <div class="admin-body">
            <?php if ($msg): ?>
            <div class="admin-alert <?= $msg_type; ?>">
                <i class="fa-solid fa-circle-check me-2"></i><?= $msg; ?>
            </div>
            <?php endif; ?>

            <!-- =============================== 1. DASHBOARD =============================== -->
            <?php if ($tab === 'dashboard'): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#fff3e0;">
                            <i class="fa-solid fa-bag-shopping" style="color:#f39c12;"></i>
                        </div>
                        <div>
                            <h3><?= $stat_orders; ?></h3>
                            <p>Tổng đơn hàng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#e8f8f5;">
                            <i class="fa-solid fa-money-bill-wave" style="color:#27ae60;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:18px;"><?= number_format($stat_revenue); ?>đ</h3>
                            <p>Doanh thu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#eaf4fb;">
                            <i class="fa-solid fa-shirt" style="color:#2980b9;"></i>
                        </div>
                        <div>
                            <h3><?= $stat_products; ?></h3>
                            <p>Sản phẩm</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon" style="background:#f5eef8;">
                            <i class="fa-solid fa-users" style="color:#8e44ad;"></i>
                        </div>
                        <div>
                            <h3><?= $stat_users; ?></h3>
                            <p>Người dùng</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="fw-bold mb-0">Đơn hàng gần đây</h5>
                    <a href="index.php?url=admin/index&tab=orders" class="btn-admin-secondary" style="padding:6px 14px;font-size:13px;">Xem tất cả</a>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Khách hàng</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th>Ngày</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach (array_slice($orders, 0, 8) as $o):
                        $s = statusLabel($o['status']); ?>
                    <tr>
                        <td>#<?= $o['id']; ?></td>
                        <td><?= htmlspecialchars($o['fullname']); ?></td>
                        <td class="fw-bold"><?= number_format($o['total']); ?>đ</td>
                        <td><?= $o['payment_method']; ?></td>
                        <td><span class="status-badge" style="background:<?= $s['color']; ?>"><?= $s['label']; ?></span></td>
                        <td style="color:#aaa;"><?= date('d/m H:i', strtotime($o['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- =============================== 2. ĐƠN HÀNG =============================== -->
            <?php elseif ($tab === 'orders'): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="fw-bold mb-0">Tất cả đơn hàng (<?= count($orders); ?>)</h5>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Khách hàng</th><th>SĐT</th><th>Địa chỉ</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th><th>Ngày</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $o):
                        $s = statusLabel($o['status']); ?>
                    <tr>
                        <td><strong>#<?= $o['id']; ?></strong></td>
                        <td>
                            <div><?= htmlspecialchars($o['fullname']); ?></div>
                            <div style="font-size:12px;color:#aaa;"><?= htmlspecialchars($o['user_name'] ?? ''); ?></div>
                        </td>
                        <td><?= $o['phone']; ?></td>
                        <td style="max-width:180px;font-size:12px;color:#666;"><?= htmlspecialchars($o['address']); ?></td>
                        <td>
                            <?php $items = $order_items_map[$o['id']] ?? []; ?>
                            <?php if (empty($items)): ?>
                                <span style="color:#bbb;font-size:12px;">—</span>
                            <?php else: ?>
                                <button type="button" class="btn-admin-secondary" style="padding:4px 12px;font-size:12px;cursor:pointer;" onclick="showOrderItems(<?= $o['id']; ?>)">
                                    <i class="fa-solid fa-box-open" style="margin-right:4px;"></i><?= count($items); ?> SP
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= number_format($o['total']); ?>đ</td>
                        <td><?= $o['payment_method']; ?></td>
                        <td class="nowrap-col">
                            <span class="status-badge" style="background:<?= $s['color']; ?>">
                                <?= $s['label']; ?>
                            </span>
                        </td>
                        <td style="color:#aaa;font-size:12px;" class="nowrap-col">
                            <?= date('d/m/Y H:i', strtotime($o['created_at'])); ?>
                        </td>
                        <td class="nowrap-col">
                            <select class="status-select form-select form-select-sm" 
                                    onchange="if(this.value) window.location='index.php?url=admin/index&tab=orders&action=update_status&id=<?= $o['id']; ?>&status='+this.value">
                                <option value="">Đổi trạng thái</option>
                                <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?= $o['status'] === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="shipping" <?= $o['status'] === 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                                <option value="done" <?= $o['status'] === 'done' ? 'selected' : ''; ?>>Hoàn thành</option>
                                <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- =============================== 3. SẢN PHẨM =============================== -->
            <?php elseif ($tab === 'products'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Sản phẩm (<?= count($products); ?>)</h5>
                <a href="index.php?url=admin/index&tab=add_product" class="btn-admin-primary">
                    <i class="fa-solid fa-plus me-2"></i>Thêm sản phẩm
                </a>
            </div>

            <div class="admin-card">
                <table class="admin-table">
                    <thead><tr><th>Ảnh</th><th>Tên sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Kho</th><th>Màu / Size</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $p):
                        $imgs = explode(',', $p['image']);
                        $thumb = trim($imgs[0]);
                    ?>
                    <tr>
                        <td>
                            <?php if ($thumb): ?>
                                <img src="uploads/<?= $thumb; ?>" class="product-thumb">
                            <?php else: ?>
                                <div class="product-thumb d-flex align-items-center justify-content-center bg-light text-secondary" style="font-size:11px;">No img</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($p['name']); ?></div>
                            <div style="font-size:12px;color:#aaa;">ID: <?= $p['id']; ?></div>
                        </td>
                        <td style="font-size:13px;"><?= htmlspecialchars($p['cat_name'] ?? '—'); ?></td>
                        <td class="fw-bold"><?= number_format($p['price']); ?>đ</td>
                        <td><?= $p['stock']; ?></td>
                        <td style="font-size:12px;color:#888;"><?= $p['color']; ?> / <?= $p['size']; ?></td>
                        <td>
                            <a href="index.php?url=admin/index&tab=edit_product&id=<?= $p['id']; ?>" class="btn-icon btn-edit-soft"><i class="fa-solid fa-pen"></i></a>
                            <a href="index.php?url=admin/index&tab=products&action=delete_product&id=<?= $p['id']; ?>" class="btn-icon btn-danger-soft" onclick="return confirm('Xóa sản phẩm này?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- =============================== 4. THÊM SẢN PHẨM =============================== -->
            <?php elseif ($tab === 'add_product'): ?>
            <div class="admin-card">
                <h5 class="fw-bold mb-3">Thêm sản phẩm mới</h5>
                <form method="POST" action="index.php?url=admin/index" enctype="multipart/form-data">
                    <input type="hidden" name="add_product" value="1">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Đầm Ngắn Maline Dress">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giá gốc (đ)</label>
                            <input type="number" name="price" class="form-control" required placeholder="530000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giá sale (đ) <span style="font-size:11px;color:#aaa;">— để trống nếu không giảm</span></label>
                            <input type="number" name="sale_price" class="form-control" placeholder="480000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tổng tồn kho (Tự động cộng dồn)</label>
                            <input type="number" name="stock" id="add_total_stock" class="form-control bg-light" value="0" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Màu sắc (phân cách bằng dấu phẩy)</label>
                            <input type="text" name="color" id="add_color_input" class="form-control" placeholder="Đen, Trắng" oninput="generateVariantTable()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Size (phân cách bằng dấu phẩy)</label>
                            <input type="text" name="size" id="add_size_input" class="form-control" placeholder="S, M, L" oninput="generateVariantTable()">
                        </div>

                        <!-- Bảng nhập số lượng tồn kho từng Màu / Size -->
                        <div class="col-12" id="variant_table_container" style="display: none;">
                            <label class="form-label fw-bold text-danger">Nhập số lượng tồn kho cho từng phân loại:</label>
                            <div class="table-responsive border rounded p-2 bg-light">
                                <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Màu sắc</th>
                                            <th>Kích cỡ</th>
                                            <th style="width: 180px;">Số lượng tồn</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variant_table_body"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả chi tiết</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ảnh sản phẩm</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <div id="preview-add" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn-admin-primary">Thêm sản phẩm</button>
                            <a href="index.php?url=admin/index&tab=products" class="btn-admin-secondary">Hủy</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- =============================== 5. SỬA SẢN PHẨM =============================== -->
            <?php elseif ($tab === 'edit_product' && $edit_product_data): ?>
            <?php
            // Lấy danh sách biến thể hiện có trong CSDL để JavaScript giữ nguyên số lượng
            $stmt_admin_vars = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
            $stmt_admin_vars->execute([$edit_product_data['id']]);
            $current_variants = $stmt_admin_vars->fetchAll(PDO::FETCH_ASSOC);

            $existingVariantMap = [];
            foreach ($current_variants as $cv) {
                $key = mb_strtolower(trim($cv['color']), 'UTF-8') . '_' . mb_strtolower(trim($cv['size']), 'UTF-8');
                $existingVariantMap[$key] = (int)$cv['stock'];
            }
            ?>
            <div class="admin-card">
                <h5 class="fw-bold mb-3">Sửa sản phẩm #<?= $edit_product_data['id']; ?></h5>
                <form method="POST" action="index.php?url=admin/index" enctype="multipart/form-data">
                    <input type="hidden" name="edit_product" value="1">
                    <input type="hidden" name="product_id" value="<?= $edit_product_data['id']; ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_product_data['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id']; ?>" <?= $c['id'] == $edit_product_data['category_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giá gốc (đ)</label>
                            <input type="number" name="price" class="form-control" value="<?= $edit_product_data['price']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Giá sale (đ)</label>
                            <input type="number" name="sale_price" class="form-control" value="<?= $edit_product_data['sale_price']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tổng tồn kho (Tự động tính)</label>
                            <input type="number" name="stock" id="edit_total_stock" class="form-control bg-light" value="<?= $edit_product_data['stock']; ?>" readonly>
                        </div>

                        <!-- Bảng chi tiết tồn kho tự động cập nhật linh hoạt -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-danger">Chi tiết tồn kho từng Màu / Size:</label>
                            <div class="table-responsive border rounded p-2 bg-light">
                                <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Màu sắc</th>
                                            <th>Kích cỡ</th>
                                            <th style="width: 180px;">Số lượng tồn</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_variant_table_body">
                                        <!-- JavaScript tự động vẽ các hàng phân loại tại đây -->
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">Khi bạn thêm/xóa màu hoặc size bên dưới, bảng trên sẽ tự động cập nhật và cộng dồn vào Tổng tồn kho.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Màu sắc (phân cách bằng dấu phẩy)</label>
                            <input type="text" name="color" id="edit_color_input" class="form-control" 
                                   value="<?= htmlspecialchars($edit_product_data['color']); ?>" 
                                   placeholder="Đen, Trắng" oninput="updateEditVariantTable()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Size (phân cách bằng dấu phẩy)</label>
                            <input type="text" name="size" id="edit_size_input" class="form-control" 
                                   value="<?= htmlspecialchars($edit_product_data['size']); ?>" 
                                   placeholder="S, M, L, XL" oninput="updateEditVariantTable()">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_product_data['description']); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ảnh hiện tại</label>
                            <?php
                            $current_imgs = array_filter(explode(',', $edit_product_data['image']));
                            if ($current_imgs): ?>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                                <?php foreach ($current_imgs as $img): $img = trim($img); ?>
                                <div style="position:relative;">
                                    <img src="uploads/<?= htmlspecialchars($img); ?>" style="width:80px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #eee;">
                                    <label style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,.85);border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;" title="Xóa ảnh này">
                                        <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($img); ?>" style="display:none;" onchange="this.closest('div[style]').style.opacity=this.checked?'0.4':'1'">
                                        <svg width="12" height="12" fill="#fff" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p style="font-size:12px;color:#aaa;margin-bottom:8px;">Click icon ✕ đỏ để đánh dấu xóa ảnh</p>
                            <?php endif; ?>
                            <label class="form-label fw-semibold">Thêm ảnh mới</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <div id="preview-edit" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
                            <a href="index.php?url=admin/index&tab=products" class="btn-admin-secondary">Hủy</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- =============================== 6. DANH MỤC =============================== -->
            <?php elseif ($tab === 'categories'): ?>
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="admin-card">
                        <div class="admin-card-header"><h5 class="fw-bold mb-0">Danh sách danh mục</h5></div>
                        <table class="admin-table">
                            <thead><tr><th>ID</th><th>Tên</th><th>Danh mục cha</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($categories as $c):
                                $parent_name = '—';
                                foreach ($categories as $cc) {
                                    if ($cc['id'] == $c['parent_id']) { $parent_name = $cc['name']; break; }
                                }
                            ?>
                            <tr>
                                <td style="color:#aaa;"><?= $c['id']; ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($c['name']); ?></td>
                                <td style="font-size:13px;color:#888;"><?= $parent_name; ?></td>
                                <td>
                                    <a href="index.php?url=admin/index&tab=edit_category&id=<?= $c['id']; ?>" class="btn-icon btn-edit-soft"><i class="fa-solid fa-pen"></i></a>
                                    <a href="index.php?url=admin/index&tab=categories&action=delete_category&id=<?= $c['id']; ?>" class="btn-icon btn-danger-soft" onclick="return confirm('Xóa danh mục này?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="admin-card">
                        <h5 class="fw-bold mb-3">Thêm danh mục mới</h5>
                        <form method="POST" action="index.php?url=admin/index">
                            <input type="hidden" name="add_category" value="1">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tên danh mục</label>
                                <input type="text" name="cat_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Danh mục cha (nếu có)</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- Không có (đây là danh mục cha) --</option>
                                    <?php foreach ($categories as $c): ?>
                                        <?php if ($c['parent_id'] === null || $c['parent_id'] === ''): ?>
                                            <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-admin-primary">Thêm danh mục</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- =============================== 7. SỬA DANH MỤC =============================== -->
            <?php elseif ($tab === 'edit_category' && $edit_category_data): ?>
            <div class="admin-card" style="max-width: 500px;">
                <h5 class="fw-bold mb-3">Sửa danh mục</h5>
                <form method="POST" action="index.php?url=admin/index">
                    <input type="hidden" name="edit_category" value="1">
                    <input type="hidden" name="cat_id" value="<?= $edit_category_data['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên danh mục</label>
                        <input type="text" name="cat_name" class="form-control" value="<?= htmlspecialchars($edit_category_data['name']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Danh mục cha</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Không có --</option>
                            <?php foreach ($categories as $c):
                                if ($c['id'] == $edit_category_data['id']) continue; ?>
                                <option value="<?= $c['id']; ?>" <?= $c['id'] == $edit_category_data['parent_id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-admin-primary">Lưu thay đổi</button>
                        <a href="index.php?url=admin/index&tab=categories" class="btn-admin-secondary">Hủy</a>
                    </div>
                </form>
            </div>

            <!-- =============================== 8. YÊU CẦU HỦY / HOÀN =============================== -->
            <?php elseif ($tab === 'requests'): ?>
            <?php
            $cancels = $pdo->query("
                SELECT oc.*, o.fullname, o.total, u.name as user_name
                FROM order_cancellations oc
                JOIN orders o ON oc.order_id = o.id
                JOIN users u ON oc.user_id = u.id
                ORDER BY oc.created_at DESC
            ")->fetchAll();

            $returns = $pdo->query("
                SELECT or2.*, o.fullname, o.total, u.name as user_name
                FROM order_returns or2
                JOIN orders o ON or2.order_id = o.id
                JOIN users u ON or2.user_id = u.id
                ORDER BY or2.created_at DESC
            ")->fetchAll();
            $colors = ['pending'=>'#f39c12','approved'=>'#27ae60','rejected'=>'#e74c3c'];
            $labels = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
            ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="fw-bold mb-0">Yêu cầu hủy đơn (<?= count($cancels); ?>)</h5></div>
                <table class="admin-table">
                    <thead><tr><th>Đơn #</th><th>Khách hàng</th><th>Lý do</th><th>Tổng tiền</th><th>Ngày gửi</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($cancels as $c): ?>
                    <tr>
                        <td><strong>#<?= $c['order_id']; ?></strong></td>
                        <td><?= htmlspecialchars($c['fullname']); ?><br><span style="font-size:12px;color:#aaa;"><?= htmlspecialchars($c['user_name']); ?></span></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($c['reason']); ?></td>
                        <td><?= number_format($c['total']); ?>đ</td>
                        <td style="font-size:12px;color:#aaa;"><?= date('d/m/Y H:i', strtotime($c['created_at'])); ?></td>
                        <td><span class="status-badge" style="background:<?= $colors[$c['status']]; ?>"><?= $labels[$c['status']]; ?></span></td>
                        <td>
                            <?php if ($c['status'] === 'pending'): ?>
                            <a href="index.php?url=admin/index&tab=requests&action=review_cancel&id=<?= $c['id']; ?>&result=approved"
                               class="btn-icon btn-edit-soft" title="Duyệt" onclick="return confirm('Duyệt hủy đơn #<?= $c['order_id']; ?>?')">
                               <i class="fa-solid fa-check"></i>
                            </a>
                            <a href="index.php?url=admin/index&tab=requests&action=review_cancel&id=<?= $c['id']; ?>&result=rejected"
                               class="btn-icon btn-danger-soft" title="Từ chối" onclick="return confirm('Từ chối yêu cầu hủy này?')">
                               <i class="fa-solid fa-xmark"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cancels)): ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">Chưa có yêu cầu hủy nào.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-card">
                <div class="admin-card-header"><h5 class="fw-bold mb-0">Yêu cầu hoàn hàng (<?= count($returns); ?>)</h5></div>
                <table class="admin-table">
                    <thead><tr><th>Đơn #</th><th>Khách hàng</th><th>Lý do</th><th>Ảnh</th><th>Tổng tiền</th><th>Ngày gửi</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($returns as $r): ?>
                    <tr>
                        <td><strong>#<?= $r['order_id']; ?></strong></td>
                        <td><?= htmlspecialchars($r['fullname']); ?><br><span style="font-size:12px;color:#aaa;"><?= htmlspecialchars($r['user_name']); ?></span></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($r['reason']); ?></td>
                        <td>
                            <?php if (!empty($r['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($r['image']); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #eee;cursor:zoom-in;" onclick="window.open('uploads/<?= htmlspecialchars($r['image']); ?>')">
                            <?php else: ?>
                                <span style="color:#bbb;font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($r['total']); ?>đ</td>
                        <td style="font-size:12px;color:#aaa;"><?= date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
                        <td><span class="status-badge" style="background:<?= $colors[$r['status']]; ?>"><?= $labels[$r['status']]; ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                            <a href="index.php?url=admin/index&tab=requests&action=review_return&id=<?= $r['id']; ?>&result=approved"
                               class="btn-icon btn-edit-soft" title="Duyệt" onclick="return confirm('Duyệt hoàn hàng đơn #<?= $r['order_id']; ?>?')">
                               <i class="fa-solid fa-check"></i>
                            </a>
                            <a href="index.php?url=admin/index&tab=requests&action=review_return&id=<?= $r['id']; ?>&result=rejected"
                               class="btn-icon btn-danger-soft" title="Từ chối" onclick="return confirm('Từ chối yêu cầu hoàn hàng này?')">
                               <i class="fa-solid fa-xmark"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returns)): ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:24px;">Chưa có yêu cầu hoàn hàng nào.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- =============================== 9. LIVE CHAT =============================== -->
            <?php elseif ($tab === 'livechat'): ?>
            <div class="livechat-wrap">
                <div class="livechat-users" id="chat-user-list">
                    <div class="livechat-users-header">
                        <i class="fa-solid fa-users" style="color:#8B1A2F;"></i> Khách hàng
                    </div>
                    <div id="chat-users-body">
                        <div style="text-align:center;padding:32px 0;color:#bbb;font-size:13px;">
                            <i class="fa-solid fa-spinner fa-spin"></i><br>Đang tải...
                        </div>
                    </div>
                </div>
                <div class="livechat-main" id="chat-main">
                    <div class="livechat-empty" id="chat-empty-state">
                        <i class="fa-regular fa-comment-dots" style="font-size:48px;color:#ddd;display:block;margin-bottom:14px;"></i>
                        Chọn một khách hàng để bắt đầu trả lời
                    </div>
                    <div id="chat-active" style="display:none;height:100%;flex-direction:column;">
                        <div class="livechat-chat-header" id="chat-header-name">—</div>
                        <div class="livechat-messages" id="chat-messages"></div>
                        <div class="livechat-input-row">
                            <textarea id="admin-chat-input" placeholder="Nhập tin nhắn..." rows="1"></textarea>
                            <button id="admin-chat-send"><i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            (function() {
                const API = 'chat_api.php';
                let activeUserId   = null;
                let activeUserName = '';
                let lastMsgId      = 0;
                let pollTimer      = null;

                async function loadUsers() {
                    try {
                        const res  = await fetch(`${API}?action=get_users`);
                        const data = await res.json();
                        if (!data.ok) return;
                        const body = document.getElementById('chat-users-body');
                        if (!data.users.length) {
                            body.innerHTML = '<div style="text-align:center;padding:32px 0;color:#bbb;font-size:13px;">Chưa có cuộc trò chuyện nào</div>';
                            return;
                        }
                        let totalUnread = 0;
                        body.innerHTML = data.users.map(u => {
                            const unread  = parseInt(u.unread) || 0;
                            totalUnread  += unread;
                            const initial = (u.name || '?')[0].toUpperCase();
                            const preview = u.last_msg ? u.last_msg.substring(0, 36) + (u.last_msg.length > 36 ? '…' : '') : '';
                            const time    = u.last_time ? formatTime(u.last_time) : '';
                            const isActive = u.id == activeUserId ? 'active' : '';
                            const badge   = unread > 0 ? `<span class="chat-user-unread">${unread}</span>` : '';
                            return `
                                <div class="chat-user-item ${isActive}" onclick="selectUser(${u.id}, '${escHtml(u.name)}')">
                                    <div class="chat-user-avatar">${initial}</div>
                                    <div style="flex:1;min-width:0;">
                                        <div class="chat-user-name">${escHtml(u.name)}</div>
                                        <div class="chat-user-preview">${escHtml(preview)}</div>
                                    </div>
                                    ${badge}
                                    <span class="chat-user-time">${time}</span>
                                </div>`;
                        }).join('');

                        const badge = document.getElementById('sidebar-unread-badge');
                        if (badge) {
                            badge.textContent   = totalUnread;
                            badge.style.display = totalUnread > 0 ? 'inline-block' : 'none';
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }

                window.selectUser = async function(userId, userName) {
                    activeUserId   = userId;
                    activeUserName = userName;
                    lastMsgId      = 0;
                    document.getElementById('chat-empty-state').style.display = 'none';
                    const chatActive = document.getElementById('chat-active');
                    chatActive.style.display = 'flex';
                    document.getElementById('chat-header-name').innerHTML =
                        `<div class="chat-user-avatar" style="width:30px;height:30px;font-size:12px;">${userName[0].toUpperCase()}</div> ${escHtml(userName)}`;
                    document.getElementById('chat-messages').innerHTML = '';
                    document.getElementById('admin-chat-input').focus();
                    await loadMessages();
                    loadUsers();
                    clearInterval(pollTimer);
                    pollTimer = setInterval(loadMessages, 3000);
                };

                async function loadMessages() {
                    if (!activeUserId) return;
                    try {
                        const res  = await fetch(`${API}?action=get_messages&user_id=${activeUserId}&since_id=${lastMsgId}`);
                        const data = await res.json();
                        if (!data.ok || !data.messages.length) return;
                        const box = document.getElementById('chat-messages');
                        data.messages.forEach(m => {
                            const isAdmin = m.sender === 'admin';
                            const div     = document.createElement('div');
                            div.className = `lc-msg ${isAdmin ? 'lc-admin' : 'lc-user'}`;
                            div.innerHTML = `
                                <div class="lc-sender-label">${isAdmin ? 'Bạn (Admin)' : escHtml(activeUserName)}</div>
                                <div class="lc-bubble">${escHtml(m.message)}</div>
                                <div class="lc-time">${formatTime(m.created_at)}</div>`;
                            box.appendChild(div);
                            lastMsgId = Math.max(lastMsgId, parseInt(m.id));
                        });
                        box.scrollTop = box.scrollHeight;
                        loadUsers();
                    } catch (e) {
                        console.error(e);
                    }
                }

                async function sendMessage() {
                    const input = document.getElementById('admin-chat-input');
                    const msg   = input.value.trim();
                    if (!msg || !activeUserId) return;
                    input.value = '';
                    input.style.height = 'auto';
                    await fetch(API + '?action=send', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ message: msg, user_id: activeUserId })
                    });
                    await loadMessages();
                }

                document.getElementById('admin-chat-send').addEventListener('click', sendMessage);
                document.getElementById('admin-chat-input').addEventListener('keydown', e => {
                    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
                });
                document.getElementById('admin-chat-input').addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });

                function formatTime(dt) {
                    const d   = new Date(dt);
                    const now = new Date();
                    const hm  = d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
                    return d.toDateString() === now.toDateString() ? hm : `${d.getDate()}/${d.getMonth()+1} ${hm}`;
                }

                loadUsers();
                setInterval(loadUsers, 8000);
            })();
            </script>

            <!-- =============================== 10. NGƯỜI DÙNG =============================== -->
            <?php elseif ($tab === 'users'): ?>
            <div class="admin-card">
                <div class="admin-card-header"><h5 class="fw-bold mb-0">Người dùng (<?= count($users_list); ?>)</h5></div>
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Tên</th><th>Email</th><th>Vai trò</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($users_list as $u): ?>
                    <tr>
                        <td style="color:#aaa;"><?= $u['id']; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($u['name']); ?></td>
                        <td><?= htmlspecialchars($u['email']); ?></td>
                        <td>
                            <select class="form-select form-select-sm" style="width: 120px;"
                                onchange="if(this.value) window.location='index.php?url=admin/index&tab=users&action=update_role&id=<?= $u['id']; ?>&role='+this.value">
                                <option value=""><?= $u['role'] === 'admin' ? 'Admin' : '👤 User'; ?></option>
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <option value="admin">→ Admin</option>
                                <?php else: ?>
                                    <option value="user">→ User</option>
                                <?php endif; ?>
                            </select>
                        </td>
                        <td>
                            <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                                <?php if ($u['is_active']): ?>
                                <a href="index.php?url=admin/index&tab=users&action=toggle_active&id=<?= $u['id']; ?>" class="btn-icon btn-danger-soft" title="Khóa tài khoản" onclick="return confirm('Khóa tài khoản <?= htmlspecialchars($u['name']); ?>?')">
                                    <i class="fa-solid fa-lock"></i>
                                </a>
                                <?php else: ?>
                                <a href="index.php?url=admin/index&tab=users&action=toggle_active&id=<?= $u['id']; ?>" class="btn-icon btn-edit-soft" title="Mở khóa tài khoản" onclick="return confirm('Mở khóa tài khoản <?= htmlspecialchars($u['name']); ?>?')">
                                    <i class="fa-solid fa-lock-open"></i>
                                </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="font-size:11px;color:#bbb;">Bạn</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal chi tiết đơn hàng -->
<div class="modal fade" id="orderItemsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h6 class="modal-title fw-bold">Chi tiết đơn hàng <span id="modal-order-id" class="text-danger"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <table class="admin-table">
          <thead>
            <tr><th>Ảnh</th><th>Tên sản phẩm</th><th>Màu / Size</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr>
          </thead>
          <tbody id="modal-items-body"></tbody>
          <tfoot id="modal-items-foot" class="fw-bold"></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== CÁC HÀM XỬ LÝ TOÀN CỤC (GLOBAL SCOPE) =====
function escHtml(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// 1. TỰ ĐỘNG SINH BẢNG KHI THÊM SẢN PHẨM MỚI
function generateVariantTable() {
    const colorVal = document.getElementById('add_color_input')?.value || '';
    const sizeVal  = document.getElementById('add_size_input')?.value || '';
    const container = document.getElementById('variant_table_container');
    const tbody     = document.getElementById('variant_table_body');

    if (!container || !tbody) return;

    const colors = colorVal.split(',').map(s => s.trim()).filter(s => s !== '');
    const sizes  = sizeVal.split(',').map(s => s.trim()).filter(s => s !== '');

    if (colors.length === 0 || sizes.length === 0) {
        container.style.display = 'none';
        tbody.innerHTML = '';
        const totalEl = document.getElementById('add_total_stock');
        if (totalEl) totalEl.value = 0;
        return;
    }

    container.style.display = 'block';
    let rows = '';

    colors.forEach(c => {
        sizes.forEach(s => {
            rows += `
                <tr>
                    <td class="fw-semibold">
                        ${escHtml(c)}
                        <input type="hidden" name="v_colors[]" value="${escHtml(c)}">
                    </td>
                    <td class="fw-bold text-primary">
                        ${escHtml(s)}
                        <input type="hidden" name="v_sizes[]" value="${escHtml(s)}">
                    </td>
                    <td>
                        <input type="number" name="v_stocks[]" class="form-control form-control-sm variant-stock-input" value="10" min="0" oninput="calculateTotalStock()">
                    </td>
                </tr>
            `;
        });
    });

    tbody.innerHTML = rows;
    calculateTotalStock();
}

function calculateTotalStock() {
    let total = 0;
    document.querySelectorAll('.variant-stock-input').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    const totalEl = document.getElementById('add_total_stock');
    if (totalEl) totalEl.value = total;
}

// 2. TỰ ĐỘNG ĐỒNG BỘ BẢNG KHI SỬA SẢN PHẨM (GIỮ NGUYÊN SỐ LƯỢNG VÀ THÊM MỚI TỰ ĐỘNG)
const savedVariants = <?= isset($existingVariantMap) ? json_encode($existingVariantMap, JSON_UNESCAPED_UNICODE) : '{}'; ?>;

function updateEditVariantTable() {
    const colorInput = document.getElementById('edit_color_input');
    const sizeInput  = document.getElementById('edit_size_input');
    const tbody      = document.getElementById('edit_variant_table_body');
    if (!colorInput || !sizeInput || !tbody) return;

    // Lưu các giá trị người dùng đang nhập trên màn hình trước khi vẽ lại
    document.querySelectorAll('.edit-v-stock').forEach(inp => {
        const c = (inp.getAttribute('data-color') || '').trim().toLowerCase();
        const s = (inp.getAttribute('data-size') || '').trim().toLowerCase();
        if (c && s) {
            savedVariants[c + '_' + s] = parseInt(inp.value) || 0;
        }
    });

    const colors = colorInput.value.split(',').map(s => s.trim()).filter(s => s !== '');
    const sizes  = sizeInput.value.split(',').map(s => s.trim()).filter(s => s !== '');

    let rows = '';
    colors.forEach(c => {
        sizes.forEach(s => {
            const key = c.toLowerCase() + '_' + s.toLowerCase();
            const stockVal = (savedVariants[key] !== undefined) ? savedVariants[key] : 10;

            rows += `
                <tr>
                    <td class="align-middle fw-semibold">
                        ${escHtml(c)}
                        <input type="hidden" name="v_colors[]" value="${escHtml(c)}">
                    </td>
                    <td class="align-middle fw-bold text-primary">
                        ${escHtml(s)}
                        <input type="hidden" name="v_sizes[]" value="${escHtml(s)}">
                    </td>
                    <td>
                        <input type="number" name="v_stocks[]" 
                               value="${stockVal}" 
                               class="form-control form-control-sm edit-v-stock" 
                               min="0" 
                               data-color="${escHtml(c)}" 
                               data-size="${escHtml(s)}" 
                               oninput="calcEditTotalStock()">
                    </td>
                </tr>
            `;
        });
    });

    tbody.innerHTML = rows;
    calcEditTotalStock();
}

function calcEditTotalStock() {
    let total = 0;
    document.querySelectorAll('.edit-v-stock').forEach(inp => {
        total += parseInt(inp.value) || 0;
    });
    const totalEl = document.getElementById('edit_total_stock');
    if (totalEl) totalEl.value = total;
}

// Gọi cập nhật ngay khi vừa mở trang Sửa sản phẩm
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('edit_variant_table_body')) {
        updateEditVariantTable();
    }
});

// 3. MODAL CHI TIẾT ĐƠN HÀNG
const orderItemsData = <?= json_encode($order_items_map); ?>;

function showOrderItems(orderId) {
    const items = orderItemsData[orderId] || [];
    document.getElementById('modal-order-id').textContent = '#' + orderId;

    let rows = '';
    let grandTotal = 0;

    items.forEach(item => {
        const imgs = (item.product_image || '').split(',');
        const thumb = imgs[0].trim();
        const imgHtml = thumb
            ? `<img src="uploads/${thumb}" class="product-thumb">`
            : `<div class="product-thumb d-flex align-items-center justify-content-center bg-light text-secondary" style="font-size:10px;">No img</div>`;

        const price = parseInt(item.price) || 0;
        const qty   = parseInt(item.quantity) || 0;
        const sub   = price * qty;
        grandTotal += sub;

        rows += `<tr>
            <td>${imgHtml}</td>
            <td><span class="fw-bold">${item.product_name || '—'}</span></td>
            <td style="color:#888;font-size:13px;">${item.color || '—'} / ${item.size || '—'}</td>
            <td>${price.toLocaleString('vi-VN')}đ</td>
            <td class="fw-bold">${qty}</td>
            <td class="fw-bold">${sub.toLocaleString('vi-VN')}đ</td>
        </tr>`;
    });

    document.getElementById('modal-items-body').innerHTML = rows;
    document.getElementById('modal-items-foot').innerHTML = `
        <tr>
            <td colspan="5" class="text-end">Tổng cộng:</td>
            <td class="text-danger">${grandTotal.toLocaleString('vi-VN')}đ</td>
        </tr>`;

    new bootstrap.Modal(document.getElementById('orderItemsModal')).show();
}

// 4. PREVIEW ẢNH TRƯỚC KHI UPLOAD
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'images[]') {
        const preview = e.target.closest('form').querySelector('[id^="preview-"]');
        if (!preview) return;
        preview.innerHTML = '';
        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.style.cssText = 'width:80px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #eee;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
</body>
</html>