<?php
// models/OrderModel.php
require_once 'core/Database.php';

class OrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Tạo đơn hàng mới và trừ tồn kho (sử dụng Transaction an toàn)
    public function createOrder(array $info, array $items): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, fullname, phone, address, note, total, payment_method, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $info['user_id'] ?? null,
                $info['fullname'],
                $info['phone'],
                $info['address'],
                $info['note'] ?? '',
                $info['total'],
                $info['payment_method']
            ]);
            $orderId = (int)$this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, color, size, quantity, price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmtVarStock = $this->db->prepare("
                UPDATE product_variants 
                SET stock = stock - ? 
                WHERE product_id = ? AND color = ? AND size = ? AND stock >= ?
            ");
            $stmtProdStock = $this->db->prepare("
                UPDATE products SET stock = stock - ? WHERE id = ?
            ");

            foreach ($items as $item) {
                $stmtItem->execute([
                    $orderId, $item['id'], $item['color'], $item['size'], $item['qty'], $item['price']
                ]);

                $stmtVarStock->execute([$item['qty'], $item['id'], $item['color'], $item['size'], $item['qty']]);
                $stmtProdStock->execute([$item['qty'], $item['id']]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // 2. Lấy danh sách đơn hàng theo user_id (Sửa lỗi undefined method)
    public function getOrdersByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // 3. Lấy chi tiết các sản phẩm trong đơn hàng
    public function getOrderDetails(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name as product_name, p.image as product_image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    // 4. Cập nhật trạng thái đơn hàng (Dùng cho Admin hoặc hệ thống)
    public function updateStatus(int $orderId, string $status): bool {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }
}