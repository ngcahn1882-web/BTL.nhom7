<?php
// core/App.php

class App {
    protected $controller = 'HomeController';
    protected $action = 'index';
    protected array $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Kiểm tra file Controller tồn tại
        if (!empty($url[0])) {
            $candidate = ucfirst($url[0]) . 'Controller';
            if (file_exists("controllers/{$candidate}.php")) {
                $this->controller = $candidate;
                unset($url[0]);
            }
        }

        require_once "controllers/{$this->controller}.php";
        $this->controller = new $this->controller();

        // 2. Kiểm tra phương thức (action) tồn tại trong Controller
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->action = $url[1];
                unset($url[1]);
            }
        }

        // 3. Lấy tham số còn lại
        $this->params = $url ? array_values($url) : [];

        // 4. Thực thi hàm
        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    private function parseUrl(): array {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}