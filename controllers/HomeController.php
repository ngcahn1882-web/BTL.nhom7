<?php
// controllers/HomeController.php
require_once 'core/Controller.php';

class HomeController extends Controller {
    public function index(): void {
        // 1. Gọi Model
        $productModel = $this->model('ProductModel');

        // 2. Lấy dữ liệu từ Database
        $bestSellers    = $productModel->getBestSellers(8);
        $newestProducts = $productModel->getNewestProducts(8);

        // 3. TRUYỀN SANG VIEW: đặt dữ liệu vào mảng [ 'tên_biến' => $giá_trị ]
        $this->view('home/index', [
            'bestSellers'    => $bestSellers,    // Sang View sẽ thành biến $bestSellers
            'newestProducts' => $newestProducts  // Sang View sẽ thành biến $newestProducts
        ]);
    }
}