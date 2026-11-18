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
            SELECT p.*, COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN oi.quantity ELSE 0 END), 0) AS total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            GROUP BY p.id
            ORDER BY total_sold DESC, p.id DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2. Top sản phẩm mới nhất: sắp xếp theo id hoặc thời gian nhập mới nhất giảm dần
    public function getNewestProducts(int $limit = 8): array {
        $sql = "SELECT * FROM products ORDER BY id DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Đếm tổng số kết quả tìm kiếm để phân trang
    public function countSearch(string $keyword, string $priceRange = ''): int {
        $keyword = trim($keyword);
        if ($keyword === '' && $priceRange === '') return 0;

        $conditions = [];
        $params     = [];

        if ($keyword !== '') {
            $clean        = $this->removeVietnameseAccents($keyword);
            $conditions[] = "(p.name LIKE ? OR LOWER(p.name) LIKE ?)";
            $params[]     = "%{$keyword}%";
            $params[]     = "%{$clean}%";
        }

        $effectivePrice = "COALESCE(NULLIF(p.sale_price, 0), p.price)";
        switch ($priceRange) {
            case 'under_500':
                $conditions[] = "{$effectivePrice} < 500000";
                break;
            case '500_1000':
                $conditions[] = "{$effectivePrice} BETWEEN 500000 AND 1000000";
                break;
            case 'over_1000':
                $conditions[] = "{$effectivePrice} > 1000000";
                break;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $conditions);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products p {$whereSql}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Tìm kiếm gần đúng có phân trang (không tính mô tả)
    public function search(string $keyword, string $priceRange = '', string $sort = 'newest', int $limit = 8, int $offset = 0): array {
        $keyword = trim($keyword);
        if ($keyword === '' && $priceRange === '') return [];

        $conditions = [];
        $params     = [];

        if ($keyword !== '') {
            $clean        = $this->removeVietnameseAccents($keyword);
            $conditions[] = "(p.name LIKE ? OR LOWER(p.name) LIKE ?)";
            $params[]     = "%{$keyword}%";
            $params[]     = "%{$clean}%";
        }

        $effectivePrice = "COALESCE(NULLIF(p.sale_price, 0), p.price)";
        switch ($priceRange) {
            case 'under_500':
                $conditions[] = "{$effectivePrice} < 500000";
                break;
            case '500_1000':
                $conditions[] = "{$effectivePrice} BETWEEN 500000 AND 1000000";
                break;
            case 'over_1000':
                $conditions[] = "{$effectivePrice} > 1000000";
                break;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $conditions);

        if ($sort === 'price_asc') {
            $orderSql = "ORDER BY {$effectivePrice} ASC, p.id DESC";
        } elseif ($sort === 'price_desc') {
            $orderSql = "ORDER BY {$effectivePrice} DESC, p.id DESC";
        } else {
            if ($keyword !== '') {
                $orderSql = "ORDER BY (CASE WHEN p.name LIKE ? THEN 0 ELSE 1 END), p.id DESC";
                $params[] = "%{$keyword}%";
            } else {
                $orderSql = "ORDER BY p.id DESC";
            }
        }

        $sql = "SELECT p.*, c.name AS cat_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                {$whereSql} 
                {$orderSql}
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function removeVietnameseAccents(string $str): string {
        $accents = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ'
        ];
        foreach ($accents as $non => $accent) {
            $str = preg_replace("/($accent)/i", $non, $str);
        }
        return mb_strtolower($str, 'UTF-8');
    }

    public function getAllCategories(): array {
        return $this->db->query("SELECT * FROM categories ORDER BY parent_id, name")->fetchAll();
    }

    // Đếm tổng số sản phẩm đang giảm giá
    public function countSaleProducts(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM products WHERE sale_price IS NOT NULL AND sale_price > 0");
        return (int)$stmt->fetchColumn();
    }

    // Lấy danh sách sản phẩm đang giảm giá có phân trang
    public function getSaleProducts(int $limit = 8, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS cat_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.sale_price IS NOT NULL AND p.sale_price > 0 
            ORDER BY p.id DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}