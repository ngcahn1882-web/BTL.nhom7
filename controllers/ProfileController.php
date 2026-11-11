<?php
// controllers/ProfileController.php
require_once 'core/Controller.php';

class ProfileController extends Controller {
    private $userModel;
    private $orderModel;
    private $requestModel;
    private $reviewModel;
    private $favModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?url=auth/login");
            exit;
        }
        $this->userModel    = $this->model('UserModel');
        $this->orderModel   = $this->model('OrderModel');
        $this->requestModel = $this->model('RequestModel');
        $this->reviewModel  = $this->model('ReviewModel');
        $this->favModel     = $this->model('FavoriteModel');
    }

    public function index(): void {
        $db = Database::getConnection();
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        // Khởi tạo các biến mặc định tránh lỗi Undefined variable
        $tab = $_GET['tab'] ?? 'orders';
        $msg = '';
        $msg_type = '';

        // 1. Cập nhật thông tin tài khoản
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if (!empty($name) && !empty($email)) {
                $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk->execute([$email, $userId]);
                if ($chk->fetch()) {
                    $msg = 'Email này đã được sử dụng bởi tài khoản khác!';
                    $msg_type = 'danger';
                } else {
                    $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?")->execute([$name, $email, $userId]);
                    $_SESSION['user']['name']  = $name;
                    $_SESSION['user']['email'] = $email;
                    $msg = 'Cập nhật thông tin thành công!';
                    $msg_type = 'success';
                }
            }
            $tab = 'info';
        }

        // 2. Yêu cầu hủy đơn hàng
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_cancel'])) {
            $orderId = (int)$_POST['order_id'];
            $reason  = trim($_POST['reason'] ?? '');
            $chkOrd = $db->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
            $chkOrd->execute([$orderId, $userId]);
            $ord = $chkOrd->fetch();

            if ($ord && in_array($ord['status'], ['pending', 'confirmed']) && $reason !== '') {
                $chkReq = $db->prepare("SELECT id FROM order_cancellations WHERE order_id = ? AND status = 'pending'");
                $chkReq->execute([$orderId]);
                if (!$chkReq->fetch()) {
                    $this->requestModel->createCancelRequest($orderId, $userId, $reason);
                    $msg = 'Đã gửi yêu cầu hủy đơn, vui lòng chờ admin xét duyệt.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Bạn đã gửi yêu cầu hủy đơn này rồi, vui lòng chờ duyệt.';
                    $msg_type = 'danger';
                }
            } else {
                $msg = 'Không thể gửi yêu cầu hủy cho đơn hàng này.';
                $msg_type = 'danger';
            }
            $tab = 'orders';
        }

        // 3. Yêu cầu hoàn hàng
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_return'])) {
            $orderId = (int)$_POST['order_id'];
            $reason  = trim($_POST['reason'] ?? '');
            $imageName = null;

            if (!empty($_FILES['return_image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['return_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed) && $_FILES['return_image']['size'] <= 5 * 1024 * 1024) {
                    $imageName = 'return_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['return_image']['tmp_name'], 'uploads/' . $imageName);
                }
            }

            $this->requestModel->createReturnRequest($orderId, $userId, $reason, $imageName);
            $msg = 'Đã gửi yêu cầu hoàn hàng, vui lòng chờ admin xét duyệt.';
            $msg_type = 'success';
            $tab = 'orders';
        }

        // 4. Đổi mật khẩu
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
            $oldPass = $_POST['old_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $cfmPass = $_POST['confirm_password'] ?? '';

            $user = $this->userModel->findById($userId);
            if (!password_verify($oldPass, $user['password'])) {
                $msg = 'Mật khẩu hiện tại không đúng.';
                $msg_type = 'danger';
            } elseif (strlen($newPass) < 6) {
                $msg = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
                $msg_type = 'danger';
            } elseif ($newPass !== $cfmPass) {
                $msg = 'Xác nhận mật khẩu không khớp.';
                $msg_type = 'danger';
            } else {
                $this->userModel->changePassword($userId, $newPass);
                $msg = 'Đổi mật khẩu thành công!';
                $msg_type = 'success';
            }
            $tab = 'password';
        }

        // 5. Lấy danh sách đơn hàng & trạng thái kèm theo
        $orders = $this->orderModel->getOrdersByUserId($userId);
        $cancelMap = [];
        $returnMap = [];
        $reviewedMap = [];

        foreach ($db->query("SELECT order_id, status FROM order_cancellations WHERE user_id = {$userId}")->fetchAll() as $c) {
            $cancelMap[$c['order_id']] = $c['status'];
        }
        foreach ($db->query("SELECT order_id, status FROM order_returns WHERE user_id = {$userId}")->fetchAll() as $r) {
            $returnMap[$r['order_id']] = $r['status'];
        }
        foreach ($db->query("SELECT product_id, order_id FROM reviews WHERE user_id = {$userId}")->fetchAll() as $rv) {
            $reviewedMap[$rv['product_id'] . '_' . ($rv['order_id'] ?? 0)] = true;
        }

        // Danh sách ID sản phẩm đã đánh giá (phục vụ kiểm tra 1 lần)
        $reviewedProductIds = $this->reviewModel->getReviewedProductIds($userId);
        $user = $this->userModel->findById($userId);

        // Truyền đầy đủ dữ liệu một lần duy nhất sang View
        $this->view('profile/index', [
            'tab'                => $tab,
            'msg'                => $msg,
            'msg_type'           => $msg_type,
            'user'               => $user,
            'orders'             => $orders,
            'cancelMap'          => $cancelMap,
            'returnMap'          => $returnMap,
            'reviewedMap'        => $reviewedMap,
            'reviewedProductIds' => $reviewedProductIds
        ]);
    }

    public function submitReview(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
            $productId = (int)$_POST['product_id'];
            $rating    = max(1, min(5, (int)$_POST['rating']));
            $comment   = trim($_POST['comment'] ?? '');
            $userId    = (int)$_SESSION['user']['id'];
            $imageName = '';

            if (!empty($_FILES['review_image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION));
                $imageName = 'rev_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['review_image']['tmp_name'], 'uploads/' . $imageName);
            }

            if (!$this->reviewModel->hasReviewed($userId, $productId)) {
                $this->reviewModel->addReview($userId, $productId, $rating, $comment, $imageName);
            }
            $this->redirect('profile/index&tab=orders');
        }
    }

    public function favorites(): void {
        $userId    = (int)$_SESSION['user']['id'];
        $favorites = $this->favModel->getUserFavorites($userId);
        $this->view('profile/favorites', ['favorites' => $favorites]);
    }
}