<?php
// controllers/ProductController.php
require_once 'core/Controller.php';

class ProductController extends Controller {

    // Trang chi tiết sản phẩm
    public function detail(int $id = 0): void {
        if ($id <= 0) $this->redirect('home/index');

        $productModel = $this->model('ProductModel');
        $favModel     = $this->model('FavoriteModel');
        $reviewModel  = $this->model('ReviewModel');

        $product = $productModel->getById($id);
        if (!$product) die("Sản phẩm không tồn tại!");

        // Lấy danh sách biến thể
        $variants = $productModel->getVariants($id);

        // 1. LẤY SẢN PHẨM LIÊN QUAN (CÙNG DANH MỤC CON)
        $related = $productModel->getRelated((int)$product['category_id'], $id);

        // 2. KIỂM TRA ĐIỀU KIỆN ĐÁNH GIÁ (CHỈ ĐƯỢC ĐÁNH GIÁ 1 LẦN)
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $isFavorite  = ($userId > 0) ? $favModel->isFavorite($userId, $id) : false;
        $canReview   = false;
        $hasReviewed = false;

        if ($userId > 0) {
            $hasPurchased = $reviewModel->hasPurchased($userId, $id);
            $hasReviewed  = $reviewModel->hasReviewed($userId, $id);
            
            // Chỉ hiển thị form khi đã mua thành công VÀ chưa từng gửi đánh giá
            $canReview = $hasPurchased && !$hasReviewed;
        }

        // 3. LẤY DANH SÁCH ĐÁNH GIÁ & ĐIỂM SAO TRUNG BÌNH
        $reviews   = $reviewModel->getReviewsByProduct($id);
        $avgRating = $reviewModel->getAvgRating($id);

        $colors = array_values(array_unique(array_filter(array_column($variants, 'color'))));
        $sizes  = array_values(array_unique(array_filter(array_column($variants, 'size'))));

        $stdSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Freesize'];
        usort($sizes, function($a, $b) use ($stdSizes) {
            $posA = array_search(trim($a), $stdSizes);
            $posB = array_search(trim($b), $stdSizes);
            return (($posA === false) ? 99 : $posA) <=> (($posB === false) ? 99 : $posB);
        });

        $this->view('product/detail', [
            'product'     => $product,
            'variants'    => $variants,
            'colors'      => $colors,
            'sizes'       => $sizes,
            'related'     => $related,
            'isFavorite'  => $isFavorite,
            'canReview'   => $canReview,
            'hasReviewed' => $hasReviewed,
            'reviews'     => $reviews,
            'avgRating'   => $avgRating
        ]);
    }

    // Gửi đánh giá sản phẩm (POST)
    public function addReview(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId    = (int)($_SESSION['user']['id'] ?? 0);
            $productId = (int)($_POST['product_id'] ?? 0);
            $rating    = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $comment   = trim($_POST['comment'] ?? '');
            $returnUrl = trim($_POST['return_url'] ?? '');

            if ($userId <= 0) {
                $this->redirect('auth/login');
            }

            $reviewModel = $this->model('ReviewModel');

            // Đúng điều kiện: Đã mua VÀ chưa từng đánh giá mới lưu
            if ($reviewModel->hasPurchased($userId, $productId) && !$reviewModel->hasReviewed($userId, $productId)) {
                $imageName = '';
                if (!empty($_FILES['review_image']['name'])) {
                    $ext = strtolower(pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $imageName = 'rev_' . uniqid() . '.' . $ext;
                        move_uploaded_file($_FILES['review_image']['tmp_name'], 'uploads/' . $imageName);
                    }
                }

                $reviewModel->addReview($userId, $productId, $rating, $comment, $imageName);
            }

            // Nếu gửi từ trang Profile / Đơn hàng thì quay về lại Profile
            if (!empty($returnUrl)) {
                $this->redirect($returnUrl);
            } else {
                $this->redirect('product/detail/' . $productId . '#review-section');
            }
        }
    }

    // Toggle yêu thích
    public function toggleFav(int $paramId = 0): void {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $productId = $paramId > 0 ? $paramId : (int)($_GET['id'] ?? 0);

        if ($userId <= 0) {
            echo json_encode(['ok' => false, 'need_login' => true, 'msg' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích!']);
            exit;
        }

        $favModel = $this->model('FavoriteModel');
        $isAdded = $favModel->toggleFavorite($userId, $productId);
        echo json_encode(['ok' => true, 'isAdded' => $isAdded]);
        exit;
    }
}