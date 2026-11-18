<?php
// controllers/AdminController.php
require_once 'core/Controller.php';

class AdminController extends Controller {
    private PDO $db;
    private $orderModel;
    private $requestModel;
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: index.php?url=auth/login");
            exit;
        }
        $this->db           = Database::getConnection();
        $this->orderModel   = $this->model('OrderModel');
        $this->requestModel = $this->model('RequestModel');
        $this->userModel    = $this->model('UserModel');
    }

    public function index(): void {
        $user = $_SESSION['user'];
        $tab  = $_GET['tab'] ?? 'dashboard';
        $msg  = '';
        $msg_type = '';
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        // 1. ĐƠN HÀNG: đổi trạng thái (tự động hoàn kho nếu hủy)
        if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
            $order_id   = (int)$_GET['id'];
            $new_status = $_GET['status'] ?? '';
            $allowed    = ['pending', 'confirmed', 'shipping', 'done', 'cancelled'];
            if (in_array($new_status, $allowed)) {
                $this->orderModel->updateStatus($order_id, $new_status);
                $msg = 'Cập nhật trạng thái thành công!';
                $msg_type = 'success';
            }
            $tab = 'orders';
        }

        // 2. YÊU CẦU HỦY: duyệt/từ chối (tự động hoàn kho nếu duyệt)
        if (isset($_GET['action']) && $_GET['action'] === 'review_cancel') {
            $cid    = (int)$_GET['id'];
            $result = $_GET['result'] ?? '';
            if (in_array($result, ['approved', 'rejected'])) {
                $this->db->prepare("UPDATE order_cancellations SET status = ? WHERE id = ?")->execute([$result, $cid]);
                if ($result === 'approved') {
                    $row = $this->db->prepare("SELECT order_id FROM order_cancellations WHERE id = ?");
                    $row->execute([$cid]);
                    $r = $row->fetch();
                    if ($r) {
                        $this->orderModel->updateStatus((int)$r['order_id'], 'cancelled');
                    }
                }
                $msg = ($result === 'approved') ? 'Đã duyệt hủy đơn và hoàn lại tồn kho.' : 'Đã từ chối yêu cầu hủy.';
                $msg_type = 'success';
            }
            $tab = 'requests';
        }

        // 3. YÊU CẦU HOÀN: duyệt/từ chối
        if (isset($_GET['action']) && $_GET['action'] === 'review_return') {
            $rid    = (int)$_GET['id'];
            $result = $_GET['result'] ?? '';
            if (in_array($result, ['approved', 'rejected'])) {
                $stmt = $this->db->prepare("UPDATE order_returns SET status = ? WHERE id = ?");
                $stmt->execute([$result, $rid]);
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

        // 5. SẢN PHẨM: thêm mới
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
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowedExts)) {
                            $fname = uniqid('product_') . '.' . $ext;
                            move_uploaded_file($tmp, 'uploads/' . $fname);
                            $image_names[] = $fname;
                        }
                    }
                }
            }
            $image_name = implode(',', $image_names);

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

            $this->db->prepare("INSERT INTO products (category_id, name, price, sale_price, stock, color, size, description, image) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$category_id, $name, $price, $sale_price, $total_stock, $color, $size, $description, $image_name]);
            $new_product_id = (int)$this->db->lastInsertId();

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

        // 6. SẢN PHẨM: sửa
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

            $delete_imgs = $_POST['delete_images'] ?? [];
            foreach ($delete_imgs as $del) {
                $del = basename($del);
                $old_imgs = array_filter($old_imgs, fn($x) => trim($x) !== $del);
                if (file_exists('uploads/' . $del)) @unlink('uploads/' . $del);
            }

            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                    if ($tmp && $_FILES['images']['error'][$i] === 0) {
                        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowedExts)) {
                            $fname = uniqid('product_') . '.' . $ext;
                            move_uploaded_file($tmp, 'uploads/' . $fname);
                            $old_imgs[] = $fname;
                        }
                    }
                }
            }
            $image_name = implode(',', array_filter($old_imgs));

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

            $this->db->prepare("UPDATE products SET category_id=?, name=?, price=?, sale_price=?, stock=?, color=?, size=?, description=?, image=? WHERE id=?")
                ->execute([$category_id, $name, $price, $sale_price, $total_new_stock, $color, $size, $description, $image_name, $id]);

            if (!empty($v_colors) && count($v_colors) === count($v_stocks)) {
                $this->db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
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
                $targetUser = $this->userModel->findById($uid);
                if ($targetUser) {
                    $new_active = $targetUser['is_active'] ? 0 : 1;
                    $this->userModel->toggleActive($uid, $new_active);
                    $msg = $new_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.';
                    $msg_type = 'success';
                }
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

        // LẤY DỮ LIỆU HIỂN THỊ CÓ PHÂN TRANG (8 DÒNG / TRANG)
        $orders = $products = $users_list = $cancellations = $returns = [];
        $order_items_map = [];
        $totalCancels    = 0;
        $totalReturns    = 0;

        $adminLimit      = 8; // 8 dòng mỗi trang cho các bảng quản trị
        $adminPage       = max(1, (int)($_GET['page'] ?? 1));
        $adminTotalPages = 1;

        // Biến phân trang riêng cho 2 bảng ở tab Yêu cầu hủy / hoàn
        $cancelPage       = max(1, (int)($_GET['cpage'] ?? 1));
        $cancelTotalPages = 1;
        $returnPage       = max(1, (int)($_GET['rpage'] ?? 1));
        $returnTotalPages = 1;

        // 1. Tab Tổng quan & Đơn hàng
        if ($tab === 'dashboard') {
            $orders = $this->db->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
        } elseif ($tab === 'orders') {
            $totalRows       = (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $adminTotalPages = max(1, (int)ceil($totalRows / $adminLimit));
            $adminPage       = min($adminPage, $adminTotalPages);
            $offset          = ($adminPage - 1) * $adminLimit;

            $stmtOrd = $this->db->prepare("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?");
            $stmtOrd->bindValue(1, $adminLimit, PDO::PARAM_INT);
            $stmtOrd->bindValue(2, $offset, PDO::PARAM_INT);
            $stmtOrd->execute();
            $orders = $stmtOrd->fetchAll();
        }

        if ($tab === 'dashboard' || $tab === 'orders') {
            $items_raw = $this->db->query("
                SELECT oi.*, p.name as product_name, p.image as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
            ")->fetchAll();
            foreach ($items_raw as $item) {
                $order_items_map[$item['order_id']][] = $item;
            }
        }

        // 2. Tab Sản phẩm
        if ($tab === 'dashboard') {
            $products = $this->db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 8")->fetchAll();
        } elseif ($tab === 'products') {
            $totalRows       = (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $adminTotalPages = max(1, (int)ceil($totalRows / $adminLimit));
            $adminPage       = min($adminPage, $adminTotalPages);
            $offset          = ($adminPage - 1) * $adminLimit;

            $stmtProd = $this->db->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT ? OFFSET ?");
            $stmtProd->bindValue(1, $adminLimit, PDO::PARAM_INT);
            $stmtProd->bindValue(2, $offset, PDO::PARAM_INT);
            $stmtProd->execute();
            $products = $stmtProd->fetchAll();
        } elseif (in_array($tab, ['add_product', 'edit_product'])) {
            $products = $this->db->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
        }

        // 3. Tab Quản trị người dùng
        if ($tab === 'users') {
            $totalUsers      = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $adminTotalPages = max(1, (int)ceil($totalUsers / $adminLimit));
            $adminPage       = min($adminPage, $adminTotalPages);
            $offset          = ($adminPage - 1) * $adminLimit;

            $stmtUser = $this->db->prepare("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
            $stmtUser->bindValue(1, $adminLimit, PDO::PARAM_INT);
            $stmtUser->bindValue(2, $offset, PDO::PARAM_INT);
            $stmtUser->execute();
            $users_list = $stmtUser->fetchAll();
        }

        // 4. Tab Yêu cầu hủy / hoàn hàng (phân trang độc lập cho 2 bảng)
        if ($tab === 'requests') {
            // Bảng yêu cầu hủy đơn
            $totalCancels     = (int)$this->db->query("SELECT COUNT(*) FROM order_cancellations")->fetchColumn();
            $cancelTotalPages = max(1, (int)ceil($totalCancels / $adminLimit));
            $cancelPage       = min($cancelPage, $cancelTotalPages);
            $cOffset          = ($cancelPage - 1) * $adminLimit;

            $stmtCancel = $this->db->prepare("
                SELECT oc.*, u.name as user_name, o.fullname, o.total as order_total, o.status as order_status 
                FROM order_cancellations oc 
                LEFT JOIN users u ON oc.user_id = u.id 
                LEFT JOIN orders o ON oc.order_id = o.id 
                ORDER BY oc.id DESC LIMIT ? OFFSET ?
            ");
            $stmtCancel->bindValue(1, $adminLimit, PDO::PARAM_INT);
            $stmtCancel->bindValue(2, $cOffset, PDO::PARAM_INT);
            $stmtCancel->execute();
            $cancellations = $stmtCancel->fetchAll();

            // Bảng yêu cầu hoàn hàng
            $totalReturns     = (int)$this->db->query("SELECT COUNT(*) FROM order_returns")->fetchColumn();
            $returnTotalPages = max(1, (int)ceil($totalReturns / $adminLimit));
            $returnPage       = min($returnPage, $returnTotalPages);
            $rOffset          = ($returnPage - 1) * $adminLimit;

            $stmtReturn = $this->db->prepare("
                SELECT orr.*, u.name as user_name, o.fullname, o.total as order_total 
                FROM order_returns orr 
                LEFT JOIN users u ON orr.user_id = u.id 
                LEFT JOIN orders o ON orr.order_id = o.id 
                ORDER BY orr.id DESC LIMIT ? OFFSET ?
            ");
            $stmtReturn->bindValue(1, $adminLimit, PDO::PARAM_INT);
            $stmtReturn->bindValue(2, $rOffset, PDO::PARAM_INT);
            $stmtReturn->execute();
            $returns = $stmtReturn->fetchAll();
        }

        $categories = $this->db->query("SELECT * FROM categories ORDER BY parent_id, name")->fetchAll();

        $stat_orders   = (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stat_revenue  = (float)($this->db->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?? 0);
        $stat_products = (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $stat_users    = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stat_pending  = (int)$this->db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

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
            'cancellations'      => $cancellations,
            'returns'            => $returns,
            'totalCancels'       => $totalCancels,
            'totalReturns'       => $totalReturns,
            'stat_orders'        => $stat_orders,
            'stat_revenue'       => $stat_revenue,
            'stat_products'      => $stat_products,
            'stat_users'         => $stat_users,
            'stat_pending'       => $stat_pending,
            'edit_product_data'  => $edit_product_data,
            'edit_category_data' => $edit_category_data,
            'adminPage'          => $adminPage,
            'adminTotalPages'    => $adminTotalPages,
            'cancelPage'         => $cancelPage,
            'cancelTotalPages'   => $cancelTotalPages,
            'returnPage'         => $returnPage,
            'returnTotalPages'   => $returnTotalPages,
            'pdo'                => $this->db
        ]);
    }
}