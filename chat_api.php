<?php
// chat_api.php - Xử lý Real-time Chat cho cả Khách hàng và Admin
header('Content-Type: application/json; charset=utf-8');
require_once 'core/Database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$db = Database::getConnection();

$action = $_GET['action'] ?? '';
$currentUser = $_SESSION['user'] ?? null;
$isAdmin = ($currentUser && ($currentUser['role'] ?? '') === 'admin');

// ============================================================
// PHÂN HỆ 1: DÀNH CHO KHÁCH HÀNG (CLIENT)
// ============================================================

// 1. Khách hàng lấy toàn bộ lịch sử tin nhắn của mình
if ($action === 'client_get_history') {
    $userId = (int)($currentUser['id'] ?? 0);
    if ($userId <= 0) {
        echo json_encode(['ok' => true, 'messages' => []]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM admin_chats WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;
}

// 2. Khách hàng kiểm tra tin nhắn mới từ Admin (Real-time Polling)
if ($action === 'client_poll') {
    $userId  = (int)($currentUser['id'] ?? 0);
    $sinceId = (int)($_GET['since_id'] ?? 0);

    if ($userId <= 0) {
        echo json_encode(['ok' => true, 'messages' => []]);
        exit;
    }

    // Đánh dấu tin nhắn từ admin đã được khách đọc
    $db->prepare("UPDATE admin_chats SET is_read = 1 WHERE user_id = ? AND sender = 'admin'")->execute([$userId]);

    $stmt = $db->prepare("SELECT * FROM admin_chats WHERE user_id = ? AND id > ? ORDER BY id ASC");
    $stmt->execute([$userId, $sinceId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'messages' => $messages]);
    exit;
}

// 3. Khách hàng gửi tin nhắn (Lưu vào CSDL + Kích hoạt Bot tự động)
if ($action === 'client_send') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    $userId  = (int)($currentUser['id'] ?? 0);

    if ($message === '') {
        echo json_encode(['ok' => false, 'msg' => 'Tin nhắn rỗng']);
        exit;
    }

    // A. Lưu tin nhắn của Khách vào bảng admin_chats
    $insertedId = 0;
    if ($userId > 0) {
        $stmt = $db->prepare("INSERT INTO admin_chats (user_id, sender, message, is_read, created_at) VALUES (?, 'user', ?, 0, NOW())");
        $stmt->execute([$userId, $message]);
        $insertedId = (int)$db->lastInsertId();
    }

    // B. Phân tích ngữ nghĩa để Trợ lý ảo (Bot) phản hồi ngay
    $q = mb_strtolower($message, 'UTF-8');
    $reply = "";
    $suggestions = [];

    if (preg_match('/(chào|hi|hello|alo|shop ơi|ad ơi)/i', $q)) {
        $reply = "Dạ Rubies xin chào chị! Em là trợ lý Rubies Studio. Em có thể hỗ trợ tư vấn size, chính sách đổi trả hoặc kết nối chuyên viên cho chị ạ.";
        $suggestions = ['Bảng size chuẩn', 'Chính sách đổi trả', 'Phí vận chuyển', 'Gặp tư vấn viên'];
    } elseif (preg_match('/(size|kích cỡ|kích thước|số đo|mặc vừa|cao|nặng)/i', $q)) {
        $reply = "Dạ bảng size chuẩn tại Rubies:\n• Size S: 40kg - 48kg (Eo 62-66cm)\n• Size M: 49kg - 54kg (Eo 67-71cm)\n• Size L: 55kg - 60kg (Eo 72-76cm)\nChị có thể để lại số đo để chuyên viên tư vấn kỹ hơn nhé!";
        $suggestions = ['Chính sách đổi trả', 'Khuyến mại'];
    } elseif (preg_match('/(đổi|trả|hoàn hàng|lỗi)/i', $q)) {
        $reply = "Dạ Rubies hỗ trợ đổi trả trong vòng 3 ngày kể từ khi nhận hàng. Chị vào mục [Tài khoản] -> [Đơn hàng của bạn] để gửi yêu cầu hủy hoặc hoàn đơn trực tiếp ạ!";
        $suggestions = ['Gặp tư vấn viên', 'Phí vận chuyển'];
    } elseif (preg_match('/(ship|vận chuyển|phí giao|bao lâu)/i', $q)) {
        $reply = "Dạ Rubies MIỄN PHÍ VẬN CHUYỂN cho đơn từ 1.000.000đ. Đơn dưới 1.000.000đ phí đồng giá toàn quốc 30.000đ. Giao hàng từ 2 - 4 ngày ạ.";
        $suggestions = ['Bảng size chuẩn', 'Xem khuyến mại'];
    } elseif (preg_match('/(nhân viên|tư vấn viên|gặp người|admin|chăm sóc)/i', $q)) {
        $reply = "Dạ em đã thông báo đến chuyên viên tư vấn. Chị chờ trong giây lát, chuyên viên sẽ trả lời trực tiếp trong khung chat này ạ!";
    }else {
        // Nhánh mặc định (Fallback): Câu hỏi nằm ngoài kịch bản gợi ý -> Chuyển cho tư vấn viên
        $reply = "Dạ câu hỏi của chị đã được em chuyển tiếp tới chuyên viên tư vấn của Rubies. Chị vui lòng chờ trong giây lát, nhân viên sẽ trực tiếp phản hồi cho chị ngay ạ!";
        $suggestions = ['Bảng size chuẩn', 'Phí vận chuyển', 'Chính sách đổi trả'];
    }

    // C. Nếu có câu trả lời của Bot và khách đã đăng nhập -> Lưu luôn câu trả lời vào CSDL
    $botMsgId = 0;
    if ($reply !== '' && $userId > 0) {
        $stmtBot = $db->prepare("INSERT INTO admin_chats (user_id, sender, message, is_read, created_at) VALUES (?, 'admin', ?, 1, NOW())");
        $stmtBot->execute([$userId, $reply]);
        $botMsgId = (int)$db->lastInsertId();
    }

    echo json_encode([
        'ok'          => true,
        'user_msg_id' => $insertedId,
        'bot_msg_id'  => $botMsgId,
        'reply'       => $reply,
        'suggestions' => $suggestions
    ]);
    exit;
}

// ============================================================
// PHÂN HỆ 2: DÀNH CHO QUẢN TRỊ VIÊN (ADMIN)
// ============================================================
if (!$isAdmin) {
    echo json_encode(['ok' => false, 'msg' => 'Quyền truy cập bị từ chối']);
    exit;
}

// 4. Admin lấy danh sách khách hàng đã nhắn
if ($action === 'get_users') {
    $sql = "
        SELECT u.id, u.name, 
               (SELECT message FROM admin_chats WHERE user_id = u.id ORDER BY id DESC LIMIT 1) AS last_msg,
               (SELECT created_at FROM admin_chats WHERE user_id = u.id ORDER BY id DESC LIMIT 1) AS last_time,
               (SELECT COUNT(*) FROM admin_chats WHERE user_id = u.id AND sender = 'user' AND is_read = 0) AS unread
        FROM users u
        WHERE u.id IN (SELECT DISTINCT user_id FROM admin_chats)
        ORDER BY last_time DESC
    ";
    $users = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'users' => $users]);
    exit;
}

// 5. Admin lấy tin nhắn của 1 khách hàng cụ thể
if ($action === 'get_messages') {
    $userId  = (int)($_GET['user_id'] ?? 0);
    $sinceId = (int)($_GET['since_id'] ?? 0);

    if ($userId > 0) {
        $db->prepare("UPDATE admin_chats SET is_read = 1 WHERE user_id = ? AND sender = 'user'")->execute([$userId]);

        $stmt = $db->prepare("SELECT * FROM admin_chats WHERE user_id = ? AND id > ? ORDER BY id ASC");
        $stmt->execute([$userId, $sinceId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok' => true, 'messages' => $messages]);
        exit;
    }
}

// 6. Admin gửi tin nhắn trả lời khách hàng
if ($action === 'send') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $userId  = (int)($input['user_id'] ?? 0);
    $message = trim($input['message'] ?? '');

    if ($userId > 0 && $message !== '') {
        $stmt = $db->prepare("INSERT INTO admin_chats (user_id, sender, message, is_read, created_at) VALUES (?, 'admin', ?, 1, NOW())");
        $stmt->execute([$userId, $message]);
        echo json_encode(['ok' => true]);
        exit;
    }
}

echo json_encode(['ok' => false]);
exit;