<?php
// controllers/ChatController.php
require_once 'core/Controller.php';

class ChatController extends Controller {

    public function bot(): void {
        // Làm sạch bộ đệm để đảm bảo chỉ trả về JSON thuần
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Đọc dữ liệu JSON gửi lên từ Fetch API
        $input = json_decode(file_get_contents('php://input'), true);
        $userMsg = '';

        if (!empty($input['messages']) && is_array($input['messages'])) {
            $lastMsg = end($input['messages']);
            $userMsg = trim($lastMsg['content'] ?? '');
        } elseif (!empty($input['message'])) {
            $userMsg = trim($input['message']);
        }

        if ($userMsg === '') {
            echo json_encode(['reply' => 'Dạ chị cần Rubies hỗ trợ thông tin gì ạ?', 'type' => 'bot', 'suggestions' => []]);
            exit;
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $db = Database::getConnection();

        // Chuẩn hóa từ khóa tìm kiếm (chuyển chữ thường)
        $q = mb_strtolower($userMsg, 'UTF-8');

        $reply = "";
        $type  = "bot";
        $suggestions = [];

        // 1. Phân tích ngữ nghĩa theo từ khóa (Rule-based AI)
        if (preg_match('/(chào|hi|hello|alo|shop ơi|ad ơi)/i', $q)) {
            $reply = "Dạ Rubies xin chào chị! Em là trợ lý tự động của Rubies Studio. Em có thể hỗ trợ chị tư vấn chọn size, chính sách đổi trả hoặc thông tin khuyến mại ạ.";
            $suggestions = ['Bảng size chuẩn', 'Chính sách đổi trả', 'Phí vận chuyển bao nhiêu?', 'Gặp nhân viên tư vấn'];
        } 
        elseif (preg_match('/(size|kích cỡ|kích thước|số đo|mặc vừa|cao|nặng)/i', $q)) {
            $reply = "Dạ bảng size chuẩn tại Rubies:\n• Size S: 40kg - 48kg (Vòng eo 62-66cm)\n• Size M: 49kg - 54kg (Vòng eo 67-71cm)\n• Size L: 55kg - 60kg (Vòng eo 72-76cm)\nChị có thể để lại chiều cao và cân nặng để bên em tư vấn chuẩn nhất nha!";
            $suggestions = ['Chính sách đổi trả', 'Xem khuyến mại'];
        } 
        elseif (preg_match('/(đổi|trả|hoàn hàng|lỗi|bảo hành)/i', $q)) {
            $reply = "Dạ chính sách đổi trả của Rubies hỗ trợ trong vòng 03 ngày kể từ khi nhận hàng (còn nguyên tag mạc, chưa qua sử dụng). Chị có thể vào mục [Tài khoản] -> [Đơn hàng của bạn] để gửi yêu cầu hủy đơn hoặc hoàn hàng trực tiếp ạ!";
            $suggestions = ['Hướng dẫn đổi hàng', 'Gặp nhân viên tư vấn'];
        } 
        elseif (preg_match('/(ship|vận chuyển|phí giao|bao lâu|giao hàng)/i', $q)) {
            $reply = "Dạ Rubies MIỄN PHÍ VẬN CHUYỂN cho đơn hàng từ 1.000.000đ trở lên. Các đơn dưới 1.000.000đ áp dụng đồng giá ship toàn quốc chỉ 30.000đ. Thời gian giao hàng từ 2 - 4 ngày tùy khu vực ạ.";
            $suggestions = ['Xem sản phẩm bán chạy', 'Chính sách KH thân thiết'];
        } 
        elseif (preg_match('/(địa chỉ|ở đâu|cửa hàng|chi nhánh|store|shop)/i', $q)) {
            $reply = "Dạ cửa hàng Rubies hiện tại đặt tại:\n• Địa chỉ: 79 Hồ Tùng Mậu, Từ Liêm, Hà Nội\n• Hotline: 070 347 0938\n• Giờ mở cửa: 9:00 - 22:00 hàng ngày. Rất hân hạnh được đón tiếp chị!";
            $suggestions = ['Chính sách KH thân thiết', 'Gặp nhân viên tư vấn'];
        } 
        elseif (preg_match('/(khuyến mại|sale|giảm giá|ưu đãi|voucher)/i', $q)) {
            $reply = "Dạ hiện tại Rubies đang có rất nhiều thiết kế giảm giá cực ưu đãi tại mục [Khuyến mại]. Chị bấm vào menu 'Khuyến mại' trên thanh điều hướng để săn deal ngay nhé!";
            $suggestions = ['Xem hàng khuyến mại', 'Chính sách tích điểm'];
        } 
        elseif (preg_match('/(nhân viên|tư vấn viên|gặp người|admin|chăm sóc|tổng đài)/i', $q)) {
            $reply = "Dạ em đã ghi nhận yêu cầu nối máy. Em sẽ chuyển tiếp cuộc trò chuyện của chị tới chuyên viên tư vấn ngay ạ!";
            $type  = "contact";

            // Lưu tin nhắn vào CSDL để quản trị viên phản hồi trong Admin Chat
            if ($userId > 0) {
                try {
                    $stmt = $db->prepare("INSERT INTO admin_chats (user_id, sender, message, is_read, created_at) VALUES (?, 'user', ?, 0, NOW())");
                    $stmt->execute([$userId, $userMsg]);
                } catch (Exception $e) {
                    // Tiếp tục nếu bảng chưa sẵn sàng
                }
            }
        } 
        else {
            $reply = "Dạ em chưa hiểu rõ câu hỏi này của chị. Chị có thể bấm chọn các chủ đề nhanh bên dưới hoặc yêu cầu 'Gặp nhân viên tư vấn' để được hỗ trợ trực tiếp nhé!";
            $suggestions = ['Bảng size chuẩn', 'Phí vận chuyển bao nhiêu?', 'Chính sách đổi trả', 'Gặp nhân viên tư vấn'];
        }

        echo json_encode([
            'reply'       => $reply,
            'type'        => $type,
            'suggestions' => $suggestions
        ]);
        exit;
    }
}