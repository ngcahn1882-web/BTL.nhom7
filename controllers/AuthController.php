<?php
// controllers/AuthController.php
require_once 'core/Controller.php';

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->userModel = $this->model('UserModel');
    }

    public function login(): void {
        $error = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Định dạng email không hợp lệ!';
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user && password_verify($pass, $user['password'])) {
                    if ((int)$user['is_active'] === 0) {
                        $error = 'Tài khoản của bạn đã bị khóa!';
                    } else {
                        $_SESSION['user'] = $user;
                        if ($user['role'] === 'admin') {
                            $this->redirect('admin/index');
                        } else {
                            $this->redirect('home/index');
                        }
                    }
                } else {
                    $error = 'Mật khẩu không chính xác!';
                }
            }
        }

        $this->view('auth/login', [
            'error' => $error,
            'email' => $email // Giữ lại email người dùng đã nhập
        ]);
    }

    public function register(): void {
        $error   = '';
        $success = '';
        $name    = '';
        $email   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $repass   = $_POST['repassword'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Vui lòng điền đầy đủ các trường thông tin!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không đúng định dạng (Ví dụ: name@example.com)!';
            } elseif (strlen($password) < 6) {
                $error = 'Mật khẩu phải chứa ít nhất 6 ký tự!';
            } elseif ($password !== $repass) {
                $error = 'Xác nhận mật khẩu không khớp!';
            } elseif ($this->userModel->findByEmail($email)) {
                // Kiểm tra trực tiếp bảng users để chống trùng tài khoản
                $error = 'Email này đã tồn tại trong hệ thống. Vui lòng chọn email khác!';
            } else {
                if ($this->userModel->register($name, $email, $password)) {
                    $success = 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.';
                    $name = '';
                    $email = '';
                } else {
                    $error = 'Có lỗi xảy ra khi tạo tài khoản, vui lòng thử lại.';
                }
            }
        }

        $this->view('auth/register', [
            'error'   => $error,
            'success' => $success,
            'name'    => $name,
            'email'   => $email
        ]);
    }

    public function logout(): void {
        unset($_SESSION['user']);
        $this->redirect('auth/login');
    }
}