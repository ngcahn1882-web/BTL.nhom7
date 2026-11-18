<?php
// models/ReviewModel.php
require_once 'core/Database.php';

class ReviewModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Kiểm tra khách hàng đã mua sản phẩm này và đơn hàng đã "Hoàn thành" (status = 'done') hay chưa
    public function hasPurchased(int $userId, int $productId): bool {
        if ($userId <= 0 || $productId <= 0) return false;
        $sql = "
            SELECT COUNT(*) 
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'done'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // 2. Kiểm tra xem khách hàng đã từng đánh giá sản phẩm này chưa
    public function hasReviewed(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }

    // 3. Thêm đánh giá mới kèm ảnh
    public function addReview(int $userId, int $productId, int $rating, string $comment, string $image = ''): bool {
        $stmt = $this->db->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment, image, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $productId, $rating, $comment, $image]);
    }

    // 4. Lấy danh sách tất cả đánh giá của sản phẩm kèm thông tin người mua
    public function getReviewsByProduct(int $productId): array {
        $sql = "
            SELECT r.*, u.name as user_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Tính điểm đánh giá trung bình
    public function getAvgRating(int $productId): ?float {
        $stmt = $this->db->prepare("SELECT AVG(rating) FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $avg = $stmt->fetchColumn();
        return ($avg !== null && $avg !== false) ? round((float)$avg, 1) : null;
    }
    // Lấy danh sách ID các sản phẩm mà khách hàng này đã từng đánh giá
    public function getReviewedProductIds(int $userId): array {
        if ($userId <= 0) return [];
        $stmt =$this->db->prepare("SELECT product_id FROM reviews WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}