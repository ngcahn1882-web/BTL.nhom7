<!-- ======================================================== -->
<!-- 1. FOOTER 4 CỘT CHUẨN RUBIES SHOP -->
<!-- ======================================================== -->
<footer style="background-color: #000000; color: #ffffff;" class="pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-md-3">
                <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">RUBIES STUDIO</h6>
                <p class="small text-white-50 mb-2">79 Hồ Tùng Mậu, Từ Liêm, Hà Nội</p>
                <p class="small text-white-50 mb-2">Điện thoại: 070 347 0938</p>
                <p class="small text-white-50 mb-3">Email: rubiesin2015@gmail.com</p>
                <div class="d-flex gap-3 text-white fs-5">
                    <a href="#" class="text-white text-decoration-none"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-white text-decoration-none"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="text-white text-decoration-none"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#" class="text-white text-decoration-none"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">CÔNG TY</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=about" class="text-white-50 text-decoration-none">RUBIES RUBIES</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=about" class="text-white-50 text-decoration-none">Tuyển Dụng & Việc Làm</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=about" class="text-white-50 text-decoration-none">Tin Tức Thời Trang</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=about" class="text-white-50 text-decoration-none">Chăm Sóc Khách Hàng</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">CHÍNH SÁCH KHÁCH HÀNG</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=loyalty" class="text-white-50 text-decoration-none">Chính Sách KH Thân Thiết</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=return" class="text-white-50 text-decoration-none">Chính Sách Đổi và Trả Hàng</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=warranty" class="text-white-50 text-decoration-none">Chính Sách Bảo Hành</a></li>
                    <li class="mb-2">• <a href="index.php?url=page/policy&type=usage" class="text-white-50 text-decoration-none">Hướng Dẫn Sử Dụng</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing: 1px;">THÔNG TIN CỬA HÀNG</h6>
                <p class="fw-bold text-white mb-2">CỬA HÀNG SỐ 1</p>
                <p class="small text-white-50">79 Hồ Tùng Mậu, Từ Liêm, Hà Nội</p>
            </div>
        </div>

        <div class="border-top border-secondary pt-3 text-center small text-white-50">
            © Bản quyền thuộc về Rubies Rubies | Cung cấp bởi Tran Ngoc Anh
        </div>
    </div>
</footer>

<!-- ======================================================== -->
<!-- 2. GIAO DIỆN CHAT NỔI (CLIENT CHAT WIDGET) -->
<!-- ======================================================== -->
<div id="rubies-chat-widget" style="position: fixed; bottom: 25px; right: 25px; z-index: 9999;">
    <!-- Nút tròn mở chat -->
    <button id="chat-toggle-btn" type="button" class="btn btn-danger rounded-circle shadow-lg position-relative d-flex align-items-center justify-content-center" 
            style="width: 55px; height: 55px; font-size: 22px; background-color: #8B1A2F !important; border: none;">
        <i class="fa-solid fa-comment-dots text-white"></i>
        <span id="client-unread-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 11px; display: none;">
            0
        </span>
    </button>

    <!-- Hộp thoại chat -->
    <div id="chat-box" class="card border-0 shadow-lg d-none" 
         style="position: absolute; bottom: 70px; right: 0; width: 350px; height: 480px; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden;">
        
        <div class="card-header text-white d-flex justify-content-between align-items-center py-2" style="background-color: #8B1A2F !important;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-headset fs-5"></i>
                <div>
                    <div class="fw-bold small mb-0">Hỗ trợ trực tuyến Rubies</div>
                    <small class="text-white-50" style="font-size: 10px;">● Trực tuyến | Phản hồi ngay</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" id="chat-close-btn" style="font-size: 11px;"></button>
        </div>
        
        <!-- Khung tin nhắn -->
        <div class="card-body p-3 overflow-auto flex-grow-1" id="chat-body" style="font-size: 13px; background-color: #fbfbfb;">
            <div class="text-center text-muted small my-2" id="chat-loading-history">
                <i class="fa-solid fa-spinner fa-spin"></i> Đang tải tin nhắn...
            </div>
        </div>

        <!-- Gợi ý câu hỏi nhanh -->
        <div id="chat-suggestions" class="p-2 bg-light d-flex gap-1 flex-wrap border-top" style="font-size: 11px;">
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="sendChat('Bảng size chuẩn')">Bảng size</button>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="sendChat('Phí vận chuyển')">Phí ship</button>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" onclick="sendChat('Chính sách đổi trả')">Đổi trả</button>
            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill text-danger" onclick="sendChat('Gặp nhân viên tư vấn')">Gặp nhân viên</button>
        </div>

        <!-- Ô soạn tin -->
        <div class="card-footer p-2 bg-white border-top d-flex gap-1">
            <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button id="chat-send" type="button" class="btn btn-sm text-white" style="background-color: #8B1A2F !important;">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 3. REAL-TIME CLIENT JAVASCRIPT POLLING -->
<!-- ======================================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let sendChat;

document.addEventListener('DOMContentLoaded', function () {
    const API = 'chat_api.php';
    const isLogged = <?= !empty($_SESSION['user']) ? 'true' : 'false'; ?>;

    const toggleBtn   = document.getElementById('chat-toggle-btn');
    const chatBox     = document.getElementById('chat-box');
    const closeBtn    = document.getElementById('chat-close-btn');
    const chatBody    = document.getElementById('chat-body');
    const chatInput   = document.getElementById('chat-input');
    const chatSend    = document.getElementById('chat-send');
    const suggBox     = document.getElementById('chat-suggestions');
    const unreadBadge = document.getElementById('client-unread-badge');

    let lastMessageId = 0;
    let pollInterval  = null;
    let unreadCount   = 0;
    let historyLoaded = false;

    if (!toggleBtn || !chatBox) return;

    // 1. Mở / Đóng khung chat
    toggleBtn.addEventListener('click', function () {
        chatBox.classList.toggle('d-none');
        if (!chatBox.classList.contains('d-none')) {
            // Đã mở chat -> Reset thông báo tin nhắn chưa đọc
            unreadCount = 0;
            unreadBadge.style.display = 'none';
            chatInput.focus();
            chatBody.scrollTop = chatBody.scrollHeight;

            // Nạp lịch sử lần đầu nếu chưa nạp
            if (!historyLoaded) {
                loadHistory();
            }
        }
    });

    closeBtn.addEventListener('click', function () {
        chatBox.classList.add('d-none');
    });

    // 2. Tải toàn bộ lịch sử trò chuyện cũ từ Database
    async function loadHistory() {
        const loadingEl = document.getElementById('chat-loading-history');
        if (!isLogged) {
            if (loadingEl) loadingEl.remove();
            chatBody.innerHTML = `
                <div class="text-start mb-2">
                    <span class="bg-light border p-2 rounded d-inline-block text-dark" style="max-width: 90%;">
                        Xin chào bạn! Bạn hãy <a href="index.php?url=auth/login" class="fw-bold text-danger">Đăng nhập</a> để được nhân viên Rubies hỗ trợ lưu lịch sử và tư vấn trực tiếp 24/7 nhé!
                    </span>
                </div>
            `;
            historyLoaded = true;
            return;
        }

        try {
            const res = await fetch(`${API}?action=client_get_history`);
            const data = await res.json();
            if (loadingEl) loadingEl.remove();

            if (data.ok && Array.isArray(data.messages) && data.messages.length > 0) {
                chatBody.innerHTML = '';
                data.messages.forEach(m => {
                    renderMessageBubble(m.sender, m.message, m.created_at);
                    lastMessageId = Math.max(lastMessageId, parseInt(m.id) || 0);
                });
            } else {
                chatBody.innerHTML = `
                    <div class="text-start mb-2">
                        <span class="bg-light border p-2 rounded d-inline-block text-dark" style="max-width: 90%;">
                            Dạ Rubies xin chào chị! Em có thể tư vấn mẫu mới, chọn size hoặc giải đáp các thắc mắc về đơn hàng cho chị ạ.
                        </span>
                    </div>
                `;
            }
            chatBody.scrollTop = chatBody.scrollHeight;
            historyLoaded = true;
        } catch (e) {
            console.error('Lỗi nạp lịch sử chat:', e);
            if (loadingEl) loadingEl.remove();
        }
    }

    // 3. Real-time Polling: Kiểm tra tin nhắn mới từ Admin mỗi 3 giây
    async function pollNewMessages() {
        if (!isLogged) return;
        try {
            const res = await fetch(`${API}?action=client_poll&since_id=${lastMessageId}`);
            const data = await res.json();
            if (data.ok && Array.isArray(data.messages) && data.messages.length > 0) {
                data.messages.forEach(m => {
                    // Chỉ append nếu tin này chưa từng vẽ
                    if (parseInt(m.id) > lastMessageId) {
                        if (m.sender === 'admin') {
                            renderMessageBubble('admin', m.message, m.created_at);

                            // Nếu đang đóng chat -> Tăng badge đỏ thông báo
                            if (chatBox.classList.contains('d-none')) {
                                unreadCount++;
                                unreadBadge.textContent = unreadCount;
                                unreadBadge.style.display = 'inline-block';
                            }
                        }
                        lastMessageId = Math.max(lastMessageId, parseInt(m.id));
                    }
                });
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        } catch (e) {
            // Bỏ qua lỗi ngắt kết nối tạm thời
        }
    }

    // 4. Hàm vẽ bong bóng tin nhắn
    function renderMessageBubble(sender, text, time) {
        const isUser = (sender === 'user');
        const formatted = (text || '').replace(/\n/g, '<br>');
        const timeStr = time ? `<small class="text-muted d-block mt-1" style="font-size: 10px;">${time.substring(11, 16)}</small>` : '';

        if (isUser) {
            chatBody.innerHTML += `
                <div class="text-end mb-2">
                    <span class="text-white p-2 rounded d-inline-block text-start shadow-sm" style="background-color: #8B1A2F !important; max-width: 85%;">
                        ${formatted}
                    </span>
                    ${timeStr}
                </div>
            `;
        } else {
            chatBody.innerHTML += `
                <div class="text-start mb-2">
                    <span class="bg-white border p-2 rounded d-inline-block text-dark shadow-sm" style="max-width: 90%;">
                        <strong class="d-block text-danger small mb-1" style="font-size: 11px;">
                            <i class="fa-solid fa-headset me-1"></i>Rubies Studio
                        </strong>
                        ${formatted}
                    </span>
                    ${timeStr}
                </div>
            `;
        }
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // 5. Gửi tin nhắn
    sendChat = async function(text) {
        text = (text || '').trim();
        if (!text) return;

        if (!isLogged) {
            alert('Vui lòng đăng nhập tài khoản để chat với cửa hàng bạn nhé!');
            window.location.href = 'index.php?url=auth/login';
            return;
        }

        // Render ngay tin nhắn người dùng lên giao diện
        renderMessageBubble('user', text);
        chatInput.value = '';
        suggBox.innerHTML = '';

        // Hiệu ứng loading
        const loadingId = 'loading-' + Date.now();
        chatBody.innerHTML += `
            <div class="text-start mb-2" id="${loadingId}">
                <span class="bg-light border p-2 rounded d-inline-block text-muted small">
                    <i class="fa-solid fa-ellipsis fa-fade"></i> Đang gửi...
                </span>
            </div>
        `;
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            const res = await fetch(`${API}?action=client_send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();

            const loadEl = document.getElementById(loadingId);
            if (loadEl) loadEl.remove();

            if (data.ok) {
                if (data.user_msg_id) {
                    lastMessageId = Math.max(lastMessageId, parseInt(data.user_msg_id));
                }

                // Nếu bot có câu trả lời tự động
                if (data.reply) {
                    renderMessageBubble('admin', data.reply);
                }

                // Render gợi ý câu hỏi mới nếu có
                if (Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                    suggBox.innerHTML = '';
                    data.suggestions.forEach(s => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill';
                        btn.textContent = s;
                        btn.onclick = () => sendChat(s);
                        suggBox.appendChild(btn);
                    });
                }
            }
        } catch (err) {
            console.error('Chat Error:', err);
            const loadEl = document.getElementById(loadingId);
            if (loadEl) loadEl.remove();
        }
    };

    chatSend.addEventListener('click', () => sendChat(chatInput.value));
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendChat(chatInput.value);
        }
    });

    // Khởi chạy vòng lặp Polling kiểm tra tin nhắn mới mỗi 3 giây
    pollInterval = setInterval(pollNewMessages, 3000);
});
</script>
</body>
</html>