<?php
// core/Controller.php

class Controller {
    // Khởi tạo Model
    protected function model(string $modelName) {
        require_once "models/{$modelName}.php";
        return new $modelName();
    }

    // Nạp giao diện View và giải nén dữ liệu
    protected function view(string $viewPath, array $data = []): void {
        extract($data);
        if (file_exists("views/{$viewPath}.php")) {
            require_once "views/{$viewPath}.php";
        } else {
            die("View views/{$viewPath}.php không tồn tại!");
        }
    }

    // Điều hướng trang
    protected function redirect(string $route): void {
        header("Location: index.php?url=" . trim($route, '/'));
        exit;
    }
}