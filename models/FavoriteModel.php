<?php
// models/FavoriteModel.php
require_once 'core/Database.php';

class FavoriteModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Kiểm tra xem user đã thích sản phẩm chưa
    public function isFavorite(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }

    // 2. Thêm hoặc Bỏ thích (Toggle)
    public function toggleFavorite(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $fav = $stmt->fetch();

        if ($fav) {
            // Nếu đã thích -> Xóa (Bỏ thích)
            $del = $this->db->prepare("DELETE FROM favorites WHERE id = ?");
            $del->execute([$fav['id']]);
            return false; // Trả về false nghĩa là đã bỏ thích
        } else {
            // Nếu chưa thích -> Thêm mới
            $ins = $this->db->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
            $ins->execute([$userId, $productId]);
            return true; // Trả về true nghĩa là đã thêm thích
        }
    }

    // 3. Lấy danh sách sản phẩm yêu thích của user
    public function getUserFavorites(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT p.* FROM favorites f
            JOIN products p ON f.product_id = p.id
            WHERE f.user_id = ?
            ORDER BY f.id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}