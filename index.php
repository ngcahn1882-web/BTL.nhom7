<?php
// index.php - Cổng kết nối toàn bộ hệ thống MVC

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nạp tự động các thành phần lõi
require_once 'core/Database.php';
require_once 'core/Controller.php';
require_once 'core/App.php';

// Khởi tạo router điều phối
$app = new App();