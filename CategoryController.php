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
        $categories = $this->productModel->getAllCategories();

        // Cấu hình phân trang (9 sản phẩm / trang cho lưới 3 cột)
        $limit = 9;
        $page  = max(1, (int)($_GET['page'] ?? 1));

        if ($catId > 0) {
            $stmtCat = $db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmtCat->execute([$catId]);
            $currentCat = $stmtCat->fetch();
            $catName = $currentCat['name'] ?? 'Tất cả sản phẩm';

            // Đếm tổng số sản phẩm thuộc danh mục này hoặc danh mục con
            $stmtCount = $db->prepare("
                SELECT COUNT(*) 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? OR c.parent_id = ?
            ");
            $stmtCount->execute([$catId, $catId]);
            $totalProducts = (int)$stmtCount->fetchColumn();

            $totalPages = max(1, (int)ceil($totalProducts / $limit));
            $page       = min($page, $totalPages);
            $offset     = ($page - 1) * $limit;

            $stmt = $db->prepare("
                SELECT p.*, c.name as cat_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? OR c.parent_id = ?
                ORDER BY p.id DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindValue(1, $catId, PDO::PARAM_INT);
            $stmt->bindValue(2, $catId, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->bindValue(4, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();
        } else {
            $catName = 'Tất Cả Sản Phẩm';
            $totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();

            $totalPages = max(1, (int)ceil($totalProducts / $limit));
            $page       = min($page, $totalPages);
            $offset     = ($page - 1) * $limit;

            $stmt = $db->prepare("
                SELECT p.*, c.name as cat_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();
        }

        $this->view('category/index', [
            'categories'    => $categories,
            'products'      => $products,
            'catName'       => $catName,
            'currentId'     => $catId,
            'currentPage'   => $page,
            'totalPages'    => $totalPages,
            'totalProducts' => $totalProducts
        ]);
    }
}