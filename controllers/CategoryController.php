<?php
// controllers/CategoryController.php
require_once 'core/Controller.php';

class CategoryController extends Controller {
    private $productModel;

    public function __construct() {
        $this->productModel = $this->model('ProductModel');
    }

    public function index(int $catId = 0): void {
        $db = Database::getConnection();
        
        // Lấy danh sách danh mục cha và con để hiển thị ở sidebar menu
        $categories = $this->productModel->getAllCategories();

        if ($catId > 0) {
            // Lấy thông tin danh mục hiện tại
            $stmtCat = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmtCat->execute([$catId]);
            $currentCat = $stmtCat->fetch();

            // Lấy sản phẩm thuộc danh mục này hoặc danh mục con của nó
            $stmt = $db->prepare("
                SELECT p.*, c.name as cat_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? OR c.parent_id = ?
                ORDER BY p.id DESC
            ");
            $stmt->execute([$catId, $catId]);
            $products = $stmt->fetchAll();
            $catName = $currentCat['name'] ?? 'Tất cả sản phẩm';
        } else {
            $products = $this->productModel->getAll(100);
            $catName = 'Tất Cả Sản Phẩm';
        }

        $this->view('category/index', [
            'categories' => $categories,
            'products'   => $products,
            'catName'    => $catName,
            'currentId'  => $catId
        ]);
    }
}