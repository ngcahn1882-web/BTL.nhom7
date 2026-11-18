<?php
// models/UserModel.php
require_once 'core/Database.php';

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Tìm user theo Email (dùng khi đăng nhập / kiểm tra trùng)
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // 2. Tìm user theo ID (sửa lỗi undefined method findById)
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // 3. Đăng ký tài khoản mới
    public function register(string $name, string $email, string $rawPassword): bool {
        $hashed = password_hash($rawPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, is_active) 
            VALUES (?, ?, ?, 'user', 1)
        ");
        return $stmt->execute([$name, $email, $hashed]);
    }

    // 4. Cập nhật họ tên và email
    public function updateProfile(int $id, string $name, string $email): bool {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $id]);
    }

    // 5. Đổi mật khẩu (mã hóa bcrypt an toàn)
    public function changePassword(int $id, string $newPassword): bool {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed, $id]);
    }

    // 6. Lấy danh sách toàn bộ người dùng (dành cho Admin)
    public function getAllUsers(): array {
        return $this->db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
    }

    // 7. Khóa / Mở khóa tài khoản (Admin)
    public function toggleActive(int $id, int $newStatus): bool {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}