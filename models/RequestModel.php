<?php
// models/RequestModel.php
require_once 'core/Database.php';

class RequestModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Tạo yêu cầu hủy đơn hàng
    public function createCancelRequest(int $orderId, int $userId, string $reason): bool {
        $stmt = $this->db->prepare("
            INSERT INTO order_cancellations (order_id, user_id, reason, status, created_at) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        return $stmt->execute([$orderId, $userId, $reason]);
    }

    // Tạo yêu cầu hoàn hàng (kèm ảnh minh chứng)
    public function createReturnRequest(int $orderId, int $userId, string $reason, ?string $image): bool {
        $stmt = $this->db->prepare("
            INSERT INTO order_returns (order_id, user_id, reason, image, status, created_at) 
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        return $stmt->execute([$orderId, $userId, $reason, $image]);
    }

    // Lấy toàn bộ danh sách yêu cầu hủy (cho Admin)
    public function getAllCancellations(): array {
        return $this->db->query("
            SELECT oc.*, o.fullname, o.total, u.name AS user_name
            FROM order_cancellations oc
            JOIN orders o ON oc.order_id = o.id
            JOIN users u ON oc.user_id = u.id
            ORDER BY oc.created_at DESC
        ")->fetchAll();
    }

    // Lấy toàn bộ danh sách yêu cầu hoàn hàng (cho Admin)
    public function getAllReturns(): array {
        return $this->db->query("
            SELECT or2.*, o.fullname, o.total, u.name AS user_name
            FROM order_returns or2
            JOIN orders o ON or2.order_id = o.id
            JOIN users u ON or2.user_id = u.id
            ORDER BY or2.created_at DESC
        ")->fetchAll();
    }

    // Duyệt / từ chối yêu cầu hủy đơn
    public function reviewCancel(int $id, string $result): bool {
        $stmt = $this->db->prepare("UPDATE order_cancellations SET status = ? WHERE id = ?");
        $ok = $stmt->execute([$result, $id]);
        if ($ok && $result === 'approved') {
            $q = $this->db->prepare("SELECT order_id FROM order_cancellations WHERE id = ?");
            $q->execute([$id]);
            $orderId = $q->fetchColumn();
            if ($orderId) {
                $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$orderId]);
            }
        }
        return $ok;
    }

    // Duyệt / từ chối yêu cầu hoàn hàng
    public function reviewReturn(int $id, string $result): bool {
        $stmt = $this->db->prepare("UPDATE order_returns SET status = ? WHERE id = ?");
        return $stmt->execute([$result, $id]);
    }
}