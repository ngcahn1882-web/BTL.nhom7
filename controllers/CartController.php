<?php
// controllers/CartController.php
require_once 'core/Controller.php';

class CartController extends Controller {
    private $cartModel;
    private $productModel;

    public function __construct() {
        $this->cartModel    = $this->model('CartModel');
        $this->productModel = $this->model('ProductModel');
    }

    public function index(): void {
        $items = $this->cartModel->getItems();
        $this->view('cart/index', [
            'items' => $items,
            'total' => $this->cartModel->calculateTotal($items)
        ]);
    }

    public function add(): void {
        $productId = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
        $color     = trim($_POST['color'] ?? $_GET['color'] ?? '');
        $size      = trim($_POST['size'] ?? $_GET['size'] ?? '');
        $qty       = max(1, (int)($_POST['qty'] ?? $_GET['qty'] ?? 1));
        $action    = $_POST['action'] ?? $_GET['action'] ?? 'add';

        if (empty($color) || empty($size)) {
            echo "<script>alert('Vui lòng chọn đầy đủ màu sắc và kích cỡ!'); history.back();</script>";
            return;
        }

        $product = $this->productModel->getById($productId);
        if (!$product) $this->redirect('home/index');

        $stock = $this->productModel->getVariantStock($productId, $color, $size);
        if ($stock < $qty) {
            echo "<script>alert('Phân loại này hiện chỉ còn {$stock} sản phẩm!'); history.back();</script>";
            return;
        }

        $finalPrice = (!empty($product['sale_price']) && (float)$product['sale_price'] > 0)
                      ? (float)$product['sale_price']
                      : (float)$product['price'];

        $images = explode(',', $product['image'] ?? '');
        $mainImage = trim($images[0] ?? 'default.jpg');

        $itemData = [
            'id'    => $productId,
            'name'  => $product['name'],
            'price' => $finalPrice,
            'image' => $mainImage,
            'color' => $color,
            'size'  => $size,
            'qty'   => $qty
        ];

        if ($action === 'buy') {
            $this->cartModel->setBuyNow($itemData);
            $this->redirect('cart/checkout');
        } else {
            $this->cartModel->add($productId, $product['name'], $finalPrice, $mainImage, $color, $size, $qty);
            $this->redirect('cart/index');
        }
    }

    public function prepareCheckout(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_items'])) {
            $this->cartModel->setSelectedKeys($_POST['selected_items']);
            $this->redirect('cart/checkout');
        } else {
            echo "<script>alert('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán!'); window.location='index.php?url=cart/index';</script>";
        }
    }

    public function checkout(): void {
        // Yêu cầu đăng nhập trước khi vào trang thanh toán
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) {
            echo "<script>alert('Vui lòng đăng nhập để tiến hành đặt hàng!'); window.location='index.php?url=auth/login';</script>";
            exit;
        }

        $checkoutItems = $this->cartModel->getCheckoutItems();
        if (empty($checkoutItems)) {
            $this->redirect('cart/index');
        }

        $subtotal = $this->cartModel->calculateTotal($checkoutItems);
        $shipFee  = $this->cartModel->calculateShippingFee($subtotal);
        $total    = $subtotal + $shipFee;

        $this->view('cart/checkout', [
            'items'    => $checkoutItems,
            'subtotal' => $subtotal,
            'shipFee'  => $shipFee,
            'total'    => $total,
            'user'     => $_SESSION['user']
        ]);
    }

    public function processOrder(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
            $this->redirect('cart/checkout');
        }

        $checkoutItems = $this->cartModel->getCheckoutItems();
        if (empty($checkoutItems)) $this->redirect('cart/index');

        $phone = trim($_POST['phone'] ?? '');
        // Kiểm tra số điện thoại: đúng 10 số và bắt đầu bằng số 0
        if (!preg_match('/^0[0-9]{9}$/', $phone)) {
            echo "<script>alert('Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số bắt đầu bằng số 0.'); history.back();</script>";
            return;
        }

        $city     = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $ward     = trim($_POST['ward'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $fullAddress = "{$address}, {$ward}, {$district}, {$city}";

        $subtotal = $this->cartModel->calculateTotal($checkoutItems);
        $shipFee  = $this->cartModel->calculateShippingFee($subtotal);
        $total    = $subtotal + $shipFee;

        $orderData = [
            'user_id'        => (int)$_SESSION['user']['id'],
            'fullname'       => trim($_POST['fullname']),
            'phone'          => $phone,
            'address'        => $fullAddress,
            'note'           => trim($_POST['note'] ?? ''),
            'payment_method' => $_POST['payment_method'] ?? 'COD',
            'total'          => $total
        ];

        try {
            $orderModel = $this->model('OrderModel');
            $orderId = $orderModel->createOrder($orderData, $checkoutItems);
            $this->cartModel->clearAfterOrder();

            if ($orderData['payment_method'] === 'BANKING') {
                $this->redirect("cart/payQr/{$orderId}");
            } else {
                echo "<script>alert('Đặt hàng thành công!'); window.location.href='index.php?url=profile/index';</script>";
            }
        } catch (Exception $e) {
            echo "Lỗi khi đặt hàng: " . $e->getMessage();
        }
    }

    // Hiển thị VietQR chuẩn MB Bank theo tài khoản của bạn
    public function payQr(int $orderId): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT total, fullname FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) die("Không tìm thấy đơn hàng.");

        $bankId      = "MB";
        $accountNo   = "0986448310";
        $accountName = "TRAN NGOC ANH";
        $amount      = $order['total'];
        $desc        = "RUBIES" . $orderId;

        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-template.png?amount={$amount}&addInfo={$desc}&accountName=" . urlencode($accountName);

        $this->view('cart/pay_qr', [
            'orderId'     => $orderId,
            'order'       => $order,
            'qrUrl'       => $qrUrl,
            'accountNo'   => $accountNo,
            'accountName' => $accountName
        ]);
    }
}