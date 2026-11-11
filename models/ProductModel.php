<?php
// models/ProductModel.php
require_once 'core/Database.php';

class ProductModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS cat_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getVariants(int $productId): array {
        $stmt = $this->db->prepare("SELECT id, color, size, stock FROM product_variants WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getVariantStock(int $productId, string $color, string $size): int {
        $stmt = $this->db->prepare("SELECT stock FROM product_variants WHERE product_id = ? AND color = ? AND size = ?");
        $stmt->execute([$productId, $color, $size]);
        return (int)$stmt->fetchColumn();
    }

    // Lấy toàn bộ sản phẩm cùng danh mục con (ngoại trừ chính sản phẩm đang xem)
        public function getRelated(int $categoryId, int $currentId): array {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY id DESC");
            $stmt->execute([$categoryId, $currentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
// 1. Top sản phẩm bán chạy nhất: tính tổng số lượng đã đặt trong order_items, sắp xếp giảm dần
    public function getBestSellers(int $limit = 8): array {
        $sql = "
            SELECT p.*, COALESCE(SUM(oi.quantity), 0) AS total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id
            ORDER BY total_sold DESC, p.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2. Top sản phẩm mới nhất: sắp xếp theo id hoặc thời gian nhập mới nhất giảm dần[cite: 1]
    public function getNewestProducts(int $limit = 8): array {
        $sql = "SELECT * FROM products ORDER BY id DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lọc trang Khuyến Mại (có sale_price hợp lệ)
    public function getSaleProducts(): array {
        $stmt = $this->db->query("SELECT * FROM products WHERE sale_price IS NOT NULL AND sale_price > 0 ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    // Tìm kiếm sản phẩm hỗ trợ tiếng Việt không dấu (gõ "quan", "quấn" vẫn ra "quần")
    public function search(string $keyword): array {
        $keyword = trim($keyword);
        if ($keyword === '') return [];

        // Chuyển từ khóa có dấu thành không dấu để so khớp
        $clean = $this->removeVietnameseAccents($keyword);
        $searchTerm = "%{$keyword}%";
        $cleanTerm  = "%{$clean}%";

        $sql = "SELECT p.*, c.name as cat_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE ? 
                   OR p.description LIKE ?
                   OR LOWER(p.name) LIKE ?
                ORDER BY p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $cleanTerm]);
        return $stmt->fetchAll();
    }

    private function removeVietnameseAccents(string $str): string {
        $accents = [
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ'
        ];
        foreach ($accents as $non => $accent) {
            $str = preg_replace("/($accent)/i", $non, $str);
        }
        return mb_strtolower($str, 'UTF-8');
    }

    public function getAllCategories(): array {
        return $this->db->query("SELECT * FROM categories ORDER BY parent_id, name")->fetchAll();
    }
}