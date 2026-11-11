<?php
// controllers/AdminController.php
require_once 'core/Controller.php';

class AdminController extends Controller {
    private PDO $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra quyền Admin
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?url=auth/login");
            exit;
        }
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $user = $_SESSION['user'];
        $tab  = $_GET['tab'] ?? 'dashboard';
        $msg  = '';
        $msg_type = '';

        // ============================================================
        // XỬ LÝ ACTIONS (GET / POST)
        // ============================================================

        // 1. ĐƠN HÀNG: đổi trạng thái
        if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
            $order_id   = (int)$_GET['id'];
            $new_status = $_GET['status'] ?? '';
            $allowed    = ['pending', 'confirmed', 'shipping', 'done', 'cancelled'];
            if (in_array($new_status, $allowed)) {
                $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$new_status, $order_id]);
                $msg = 'Cập nhật trạng thái thành công!';
                $msg_type = 'success';
            }
            $tab = 'orders';
        }

        // 2. YÊU CẦU HỦY: duyệt/từ chối
        if (isset($_GET['action']) && $_GET['action'] === 'review_cancel') {
            $cid    = (int)$_GET['id'];
            $result = $_GET['result'] ?? '';
            if (in_array($result, ['approved', 'rejected'])) {
                $this->db->prepare("UPDATE order_cancellations SET status = ? WHERE id = ?")->execute([$result, $cid]);
                if ($result === 'approved') {
                    $row = $this->db->prepare("SELECT order_id FROM order_cancellations WHERE id = ?");
                    $row->execute([$cid]);
                    $r = $row->fetch();
                    if ($r) $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$r['order_id']]);
                }
                $msg = ($result === 'approved') ? 'Đã duyệt hủy đơn.' : 'Đã từ chối yêu cầu hủy.';
                $msg_type = 'success';
            }
            $tab = 'requests';
        }

        // 3. YÊU CẦU HOÀN: duyệt/từ chối
        if (isset($_GET['action']) && $_GET['action'] === 'review_return') {
            $rid    = (int)$_GET['id'];
            $result = $_GET['result'] ?? '';
            if (in_array($result, ['approved', 'rejected'])) {
                $this->db->prepare("UPDATE order_returns SET status = ? WHERE id = ?")->execute([$result, $rid]);
                $msg = ($result === 'approved') ? 'Đã duyệt hoàn hàng.' : 'Đã từ chối yêu cầu hoàn.';
                $msg_type = 'success';
            }
            $tab = 'requests';
        }

        // 4. SẢN PHẨM: xóa
        if (isset($_GET['action']) && $_GET['action'] === 'delete_product') {
            $prodId = (int)$_GET['id'];
            $this->db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$prodId]);
            $this->db->prepare("DELETE FROM products WHERE id = ?")->execute([$prodId]);
            $msg = 'Đã xóa sản phẩm.';
            $msg_type = 'success';
            $tab = 'products';
        }

        // 5. SẢN PHẨM: thêm mới (Đã đóng ngoặc đầy đủ và phân bổ đều kho biến thể)
// 5. SẢN PHẨM: thêm mới (Lưu trực tiếp số lượng từng biến thể)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
            $name        = trim($_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $price       = (int)$_POST['price'];
            $sale_price  = !empty($_POST['sale_price']) ? (int)$_POST['sale_price'] : null;
            $color       = trim($_POST['color']);
            $size        = trim($_POST['size']);
            $description = trim($_POST['description']);
            $image_names = [];

            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                    if ($tmp && $_FILES['images']['error'][$i] === 0) {
                        $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $fname = uniqid('product_') . '.' . $ext;
                        move_uploaded_file($tmp, 'uploads/' . $fname);
                        $image_names[] = $fname;
                    }
                }
            }
            $image_name = implode(',', $image_names);

            // Tính tổng tồn kho từ các biến thể được nhập riêng
            $total_stock = 0;
            $v_colors = $_POST['v_colors'] ?? [];
            $v_sizes  = $_POST['v_sizes'] ?? [];
            $v_stocks = $_POST['v_stocks'] ?? [];

            if (!empty($v_stocks)) {
                foreach ($v_stocks as $stk) {
                    $total_stock += max(0, (int)$stk);
                }
            } else {
                $total_stock = max(0, (int)($_POST['stock'] ?? 0));
            }

            // Lưu vào bảng products với tổng tồn kho chính xác
            $this->db->prepare("INSERT INTO products (category_id, name, price, sale_price, stock, color, size, description, image) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$category_id, $name, $price, $sale_price, $total_stock, $color, $size, $description, $image_name]);
            $new_product_id = (int)$this->db->lastInsertId();

            // Lưu từng biến thể kèm số lượng riêng biệt
            if (!empty($v_colors) && count($v_colors) === count($v_stocks)) {
                $stmtVar = $this->db->prepare("INSERT INTO product_variants (product_id, color, size, stock) VALUES (?, ?, ?, ?)");
                for ($i = 0; $i < count($v_colors); $i++) {
                    $c = trim($v_colors[$i]);
                    $s = trim($v_sizes[$i]);
                    $stk = max(0, (int)$v_stocks[$i]);
                    $stmtVar->execute([$new_product_id, $c, $s, $stk]);
                }
            }

            $msg = 'Thêm sản phẩm thành công!';
            $msg_type = 'success';
            $tab = 'products';
        }

        // 6. SẢN PHẨM: sửa (Đồng bộ lại danh sách biến thể và tổng kho)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
            $id          = (int)$_POST['product_id'];
            $name        = trim($_POST['name']);
            $category_id = (int)$_POST['category_id'];
            $price       = (int)$_POST['price'];
            $sale_price  = !empty($_POST['sale_price']) ? (int)$_POST['sale_price'] : null;
            $color       = trim($_POST['color']);
            $size        = trim($_POST['size']);
            $description = trim($_POST['description']);

            $old = $this->db->prepare("SELECT image FROM products WHERE id = ?");
            $old->execute([$id]);
            $old_imgs = array_filter(explode(',', (string)$old->fetchColumn()));

            // Xóa ảnh được chọn
            $delete_imgs = $_POST['delete_images'] ?? [];
            foreach ($delete_imgs as $del) {
                $del = basename($del);
                $old_imgs = array_filter($old_imgs, fn($x) => trim($x) !== $del);
                if (file_exists('uploads/' . $del)) @unlink('uploads/' . $del);
            }

            // Upload ảnh mới thêm
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                    if ($tmp && $_FILES['images']['error'][$i] === 0) {
                        $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                        $fname = uniqid('product_') . '.' . $ext;
                        move_uploaded_file($tmp, 'uploads/' . $fname);
                        $old_imgs[] = $fname;
                    }
                }
            }
            $image_name = implode(',', array_filter($old_imgs));

            // Lấy dữ liệu các biến thể từ bảng
            $v_colors = $_POST['v_colors'] ?? [];
            $v_sizes  = $_POST['v_sizes'] ?? [];
            $v_stocks = $_POST['v_stocks'] ?? [];

            $total_new_stock = 0;
            if (!empty($v_stocks)) {
                foreach ($v_stocks as $stk) {
                    $total_new_stock += max(0, (int)$stk);
                }
            } else {
                $total_new_stock = (int)($_POST['stock'] ?? 0);
            }

            // 1. Cập nhật bảng products
            $this->db->prepare("UPDATE products SET category_id=?, name=?, price=?, sale_price=?, stock=?, color=?, size=?, description=?, image=? WHERE id=?")
                ->execute([$category_id, $name, $price, $sale_price, $total_new_stock, $color, $size, $description, $image_name, $id]);

            // 2. Làm mới danh sách biến thể trong product_variants
            if (!empty($v_colors) && count($v_colors) === count($v_stocks)) {
                // Xóa các biến thể cũ của sản phẩm này
                $this->db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);

                // Chèn lại danh sách biến thể cập nhật mới nhất
                $stmtInsertVar = $this->db->prepare("INSERT INTO product_variants (product_id, color, size, stock) VALUES (?, ?, ?, ?)");
                for ($i = 0; $i < count($v_colors); $i++) {
                    $c   = trim($v_colors[$i]);
                    $s   = trim($v_sizes[$i]);
                    $stk = max(0, (int)$v_stocks[$i]);
                    $stmtInsertVar->execute([$id, $c, $s, $stk]);
                }
            }

            $msg = 'Cập nhật sản phẩm thành công!';
            $msg_type = 'success';
            $tab = 'products';
        }

        // 7. DANH MỤC: Thêm / Sửa / Xóa
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
            $name      = trim($_POST['cat_name']);
            $parent_id = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
            $this->db->prepare("INSERT INTO categories (name, parent_id) VALUES (?,?)")->execute([$name, $parent_id]);
            $msg = 'Thêm danh mục thành công!';
            $msg_type = 'success';
            $tab = 'categories';
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete_category') {
            $this->db->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['id']]);
            $msg = 'Đã xóa danh mục.';
            $msg_type = 'success';
            $tab = 'categories';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
            $id        = (int)$_POST['cat_id'];
            $name      = trim($_POST['cat_name']);
            $parent_id = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
            $this->db->prepare("UPDATE categories SET name=?, parent_id=? WHERE id=?")->execute([$name, $parent_id, $id]);
            $msg = 'Cập nhật danh mục thành công!';
            $msg_type = 'success';
            $tab = 'categories';
        }

        // 8. NGƯỜI DÙNG: Khóa / Mở khóa & Đổi vai trò
        if (isset($_GET['action']) && $_GET['action'] === 'toggle_active') {
            $uid = (int)$_GET['id'];
            if ($uid !== (int)$user['id']) {
                $stmt = $this->db->prepare("SELECT is_active FROM users WHERE id = ?");
                $stmt->execute([$uid]);
                $current = $stmt->fetchColumn();
                $new_active = $current ? 0 : 1;
                $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$new_active, $uid]);
                $msg = $new_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.';
                $msg_type = 'success';
            } else {
                $msg = 'Không thể khóa tài khoản đang đăng nhập!';
                $msg_type = 'danger';
            }
            $tab = 'users';
        }

        if (isset($_GET['action']) && $_GET['action'] === 'update_role') {
            $uid      = (int)$_GET['id'];
            $new_role = $_GET['role'] ?? '';
            if (in_array($new_role, ['admin', 'user']) && $uid !== (int)$user['id']) {
                $this->db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $uid]);
                $msg = 'Đã cập nhật vai trò.';
                $msg_type = 'success';
            }
            $tab = 'users';
        }

        // ============================================================
        // LẤY DỮ LIỆU HIỂN THỊ
        // ============================================================
        $orders = $products = $users_list = [];
        $order_items_map = [];

        if ($tab === 'dashboard' || $tab === 'orders') {
            $orders = $this->db->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();

            $items_raw = $this->db->query("
                SELECT oi.*, p.name as product_name, p.image as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
            ")->fetchAll();
            foreach ($items_raw as $item) {
                $order_items_map[$item['order_id']][] = $item;
            }
        }

        if (in_array($tab, ['products', 'dashboard', 'add_product', 'edit_product'])) {
            $products = $this->db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
        }

        $categories = $this->db->query("SELECT * FROM categories ORDER BY parent_id, name")->fetchAll();

        if ($tab === 'users') {
            $users_list = $this->db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
        }

        // Thống kê dashboard
        $stat_orders   = (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stat_revenue  = (float)($this->db->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?? 0);
        $stat_products = (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $stat_users    = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stat_pending  = (int)$this->db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

        // Dữ liệu sửa sản phẩm / danh mục
        $edit_product_data = null;
        if ($tab === 'edit_product' && isset($_GET['id'])) {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
            $edit_product_data = $stmt->fetch();
        }

        $edit_category_data = null;
        if ($tab === 'edit_category' && isset($_GET['id'])) {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([(int)$_GET['id']]);
            $edit_category_data = $stmt->fetch();
        }

        $this->view('admin/index', [
            'user'               => $user,
            'tab'                => $tab,
            'msg'                => $msg,
            'msg_type'           => $msg_type,
            'orders'             => $orders,
            'order_items_map'    => $order_items_map,
            'products'           => $products,
            'categories'         => $categories,
            'users_list'         => $users_list,
            'stat_orders'        => $stat_orders,
            'stat_revenue'       => $stat_revenue,
            'stat_products'      => $stat_products,
            'stat_users'         => $stat_users,
            'stat_pending'       => $stat_pending,
            'edit_product_data'  => $edit_product_data,
            'edit_category_data' => $edit_category_data,
            'pdo'                => $this->db
        ]);
    }
}