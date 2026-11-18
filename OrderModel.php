<?php
// models/OrderModel.php
require_once 'core/Database.php';

class OrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

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
                UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?
            ");

            foreach ($items as $item) {
                $stmtVarStock->execute([
                    $item['qty'],
                    $item['id'],
                    $item['color'],
                    $item['size'],
                    $item['qty']
                ]);

                if ($stmtVarStock->rowCount() === 0) {
                    throw new Exception("Sản phẩm '{$item['name']}' (Màu: {$item['color']}, Size: {$item['size']}) đã hết hoặc không đủ số lượng!");
                }

                $stmtProdStock->execute([$item['qty'], $item['id']]);
                $stmtItem->execute([
                    $orderId,
                    $item['id'],
                    $item['color'],
                    $item['size'],
                    $item['qty'],
                    $item['price']
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getOrdersByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

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

    public function updateStatus(int $orderId, string $newStatus): bool {
        try {
            $this->db->beginTransaction();

            $stmtOld = $this->db->prepare("SELECT status FROM orders WHERE id = ?");
            $stmtOld->execute([$orderId]);
            $oldStatus = $stmtOld->fetchColumn();

            if (!$oldStatus) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);

            // Hoàn trả tồn kho nếu đơn chuyển sang trạng thái 'cancelled'
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                $items = $this->getOrderDetails($orderId);
                $stmtRestoreVar  = $this->db->prepare("UPDATE product_variants SET stock = stock + ? WHERE product_id = ? AND color = ? AND size = ?");
                $stmtRestoreProd = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");

                foreach ($items as $it) {
                    $stmtRestoreVar->execute([$it['quantity'], $it['product_id'], $it['color'], $it['size']]);
                    $stmtRestoreProd->execute([$it['quantity'], $it['product_id']]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    // Đếm tổng số đơn hàng của 1 user
    public function countOrdersByUserId(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    // Lấy danh sách đơn hàng của user có phân trang
    public function getOrdersByUserIdPaginated(int $userId, int $limit = 5, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}