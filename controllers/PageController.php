<?php
// controllers/PageController.php
require_once 'core/Controller.php';

class PageController extends Controller {
    private $productModel;

    public function __construct() {
        $this->productModel = $this->model('ProductModel');
    }

    public function sale(): void {
        $saleProducts = $this->productModel->getSaleProducts();
        $this->view('pages/sale', [
            'products' => $saleProducts
        ]);
    }

    public function policy(): void {
        $type = $_GET['type'] ?? 'loyalty';
        $title = "";
        $content = "";

        switch ($type) {
            case 'loyalty':
                $title = "Chính Sách KH Thân Thiết";
                $content = '
                    <p>Rubies sẽ tự động tích lũy doanh số mua hàng theo SĐT:</p>
                    <ul class="list-unstyled">
                        <li>- Tổng doanh số từ 10.000.000đ sẽ được giảm 5% cho các đơn hàng sau.</li>
                        <li>- Tổng doanh số từ 20.000.000đ sẽ được giảm 10% cho các đơn hàng sau.</li>
                        <li>- Tổng doanh số từ 40.000.000đ sẽ được giảm 15% cho các đơn hàng sau.</li>
                        <li>- Tổng doanh số từ 100.000.000đ sẽ được giảm 20% cho các đơn hàng sau.</li>
                    </ul>';
                break;

            case 'return':
                $title = "Chính Sách Đổi và Trả Hàng";
                $content = '
                    <ul class="list-unstyled">
                        <li>- Thời gian đổi hàng: 3 ngày tính từ thời điểm giao hàng thành công.</li>
                        <li>- Hỗ trợ đổi size/ đổi mẫu 1 lần cho một đơn hàng.</li>
                        <li>- Sản phẩm chưa qua sử dụng; không dính bẩn, hư hỏng; sản phẩm còn nguyên tag mạc.</li>
                        <li>- Khách hàng thanh toán tiền chênh lệch nếu sản phẩm đổi có giá cao hơn.</li>
                        <li>- Khách hàng thanh toán phí ship 2 chiều khi có nhu cầu đổi hàng.</li>
                    </ul>';
                break;

            case 'warranty':
                $title = "Chính Sách Bảo Hành";
                $content = '
                    <p>Sản phẩm được bảo hành trong các trường hợp sau:</p>
                    <ul class="list-unstyled">
                        <li>- Lỗi đường may, nút bấm, khóa kéo do nhà sản xuất.</li>
                        <li>- Sản phẩm bị ra màu hoặc co rút vải quá mức cho phép trong lần giặt đầu tiên.</li>
                        <li>- Thời hạn tiếp nhận bảo hành: Trong vòng 30 ngày kể từ ngày mua hàng.</li>
                    </ul>';
                break;

            case 'usage':
                $title = "Hướng Dẫn Sử Dụng";
                $content = '
                    <p class="fw-bold"><i class="fa-solid fa-info-circle me-2"></i> HƯỚNG DẪN SỬ DỤNG CHUNG</p>
                    <ol class="ps-3 mb-4">
                        <li>Không dùng nước giặt/bột giặt tẩy mạnh, không ngâm vải trong thời gian lâu.</li>
                        <li>Sản phẩm sẽ bền đẹp hơn nếu giặt bằng tay hoặc giặt khô.</li>
                        <li>Không phơi dưới ánh nắng trực tiếp với đồ jeans, khaki hoặc màu pastel.</li>
                        <li>Sản phẩm màu đậm nên giặt riêng trong 2-3 lần đầu.</li>
                    </ol>';
                break;

            case 'about':
            default:
                $title = "Về Chúng Tôi";
                $content = "
                    <h5 class='fw-bold text-danger mb-3'>1. Khởi nguồn từ đam mê</h5>
                    <p><em>\"Mỗi viên đá quý đều cần được mài giũa để tỏa sáng, và phụ nữ cũng vậy.\"</em></p>
                    <p>RUBIES ra đời từ một giấc mơ giản đơn: tạo ra những bộ trang phục không chỉ để mặc, mà để phái đẹp cảm thấy được trân trọng và tự tin nhất trong từng khoảnh khắc của cuộc sống.</p>
                    <h5 class='fw-bold text-danger mt-4 mb-3'>2. Sự tỉ mỉ trong từng điểm chạm</h5>
                    <p>- <strong>Chất liệu tự nhiên:</strong> Ưu tiên Linen thoáng mát, Lụa mềm mại hay Dạ tweed sang trọng.</p>
                    <p>- <strong>Thiết kế độc bản:</strong> Hàng chục lần fitting để phù hợp nhất với vóc dáng phụ nữ Việt Nam.</p>";
                break;
        }

        $this->view('pages/policy', [
            'type'    => $type,
            'title'   => $title,
            'content' => $content
        ]);
    }

    public function search(): void {
        $q = trim($_GET['q'] ?? '');
        $results = $this->productModel->search($q);
        $this->view('pages/search', [
            'keyword' => $q,
            'results' => $results
        ]);
    }
}