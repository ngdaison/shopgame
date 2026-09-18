@extends('admin.layouts.master')
@section('title', 'Admin: KiyoAI Assistant')
@section('css')
<style>
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .chat-header {
        padding: 15px 20px;
        background: linear-gradient(135deg, #7366ff 0%, #a066ff 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
    }
    .message-wrapper {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    .message-ai-wrapper {
        align-items: flex-start;
    }
    .message-user-wrapper {
        align-items: flex-end;
    }
    .message {
        width: fit-content;
        max-width: 80%;
        padding: 8px 15px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 1px solid #e9ebef;
    }
    .message > *:last-child {
        margin-bottom: 0 !important;
    }
    .message p {
        margin-bottom: 8px;
    }
    .message-ai {
        align-self: flex-start;
        background: #fff;
        color: #333;
        border-bottom-left-radius: 4px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid #edf1f7;
    }
    .message-user {
        align-self: flex-end;
        background: #7366ff;
        color: #fff;
        border-bottom-right-radius: 4px;
        border-color: #5e52e5;
    }
    .chat-input-area {
        padding: 15px 20px;
        background: #fcfcfd;
        border-top: 1px solid #e9ebef;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .message-time {
        font-size: 10px;
        color: #aaa;
        margin-top: 4px;
        margin-bottom: 7px;
    }
    .chat-input {
        flex: 1;
        border: 1px solid #e0e6ed;
        border-radius: 10px;
        padding: 12px 20px;
        outline: none;
        transition: all 0.3s;
        background: #fff;
        resize: none;
        max-height: 150px;
        min-height: 46px;
        overflow-y: auto;
    }
    .chat-input:focus {
        border-color: #7366ff;
        box-shadow: 0 0 0 3px rgba(115, 102, 255, 0.1);
    }
    .btn-send {
        background: #7366ff;
        color: #fff;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, background 0.3s;
    }
    .btn-send:hover {
        background: #5e52e5;
        transform: scale(1.05);
    }
    .btn-send:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .chat-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: nowrap;
    }
    .btn-control {
        padding: 5px 15px;
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #e0e6ed;
        background: #fff;
        color: #333;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        height: 32px;
        white-space: nowrap;
        width: max-content;
    }
    .btn-memory-on {
        background: #fff;
        color: #28a745;
        border: 1px solid #28a745;
    }
    .btn-memory-on:hover {
        background: #28a745;
        color: #fff;
    }
    .btn-memory-off {
        background: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
    }
    .btn-memory-off:hover {
        background: #dc3545;
        color: #fff;
    }
    .btn-clear-chat {
        background: #e7515a;
        color: #fff;
        border: 1px solid #e7515a;
    }
    .btn-clear-chat:hover {
        background: #c33d45;
        color: #fff;
        border-color: #c33d45;
    }
    .model-select {
        padding: 0 10px;
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #e0e6ed;
        outline: none;
        background: #fff;
        color: #333;
        cursor: pointer;
        height: 32px;
    }
    .model-select:focus {
        border-color: #7366ff;
    }
    .typing-indicator {
        display: none;
        align-self: flex-start;
        background: #fff;
        padding: 10px 15px;
        border-radius: 15px;
        border-bottom-left-radius: 2px;
        font-size: 12px;
        color: #888;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .dot {
        display: inline-block;
        width: 4px;
        height: 4px;
        background: #888;
        border-radius: 50%;
        margin-right: 3px;
        animation: wave 1.3s linear infinite;
    }
    .dot:nth-child(2) { animation-delay: -1.1s; }
    .dot:nth-child(3) { animation-delay: -0.9s; }

    @keyframes wave {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-4px); }
    }
    pre {
        background: #f1f1f1;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }
    code {
        font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
    }
</style>
@endsection

@section('header_actions')
<div class="chat-controls">
    <select class="form-select form-select-sm" id="ai-type-select" onchange="toggleAISelector()" style="min-width: 120px; border-color: #e0e6ed; box-shadow: none; font-size: 13px;">
        <option value="default">Mặc định</option>
        <option value="chatlgbt">Chatlgbt</option>
    </select>

    <div id="model-selector-wrapper" style="display: none;">
        <select class="form-select form-select-sm" id="model-select" style="min-width: 250px; border-color: #e0e6ed; box-shadow: none; font-size: 13px;">
            <optgroup label="Khuyến nghị - Mới nhất 2025">
                <option value="gpt-4o-2024-11-20" {{ ($config['chatgpt_model'] ?? '') == 'gpt-4o-2024-11-20' ? 'selected' : '' }}>🔥 GPT-4o (2024-11-20) - $2.50/$5.00 per 1M tokens</option>
            </optgroup>
            <optgroup label="GPT-4 Series - Cao cấp">
                <option value="gpt-4o" {{ ($config['chatgpt_model'] ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o - $2.50/$5.00 per 1M tokens</option>
                <option value="gpt-4o-2024-08-06" {{ ($config['chatgpt_model'] ?? '') == 'gpt-4o-2024-08-06' ? 'selected' : '' }}>GPT-4o (Aug 2024) - $2.50/$5.00 per 1M tokens</option>
                <option value="gpt-4o-mini" {{ ($config['chatgpt_model'] ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini - $0.15/$0.60 per 1M tokens</option>
                <option value="gpt-4-turbo" {{ ($config['chatgpt_model'] ?? '') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo - $10.00/$30.00 per 1M tokens [Legacy]</option>
            </optgroup>
            <optgroup label="GPT-3.5 Series - Tiết kiệm">
                <option value="gpt-3.5-turbo" {{ ($config['chatgpt_model'] ?? '') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo - $0.50/$1.50 per 1M tokens</option>
                <option value="gpt-3.5-turbo-0125" {{ ($config['chatgpt_model'] ?? '') == 'gpt-3.5-turbo-0125' ? 'selected' : '' }}>GPT-3.5 Turbo (0125) - $0.50/$1.50 per 1M tokens</option>
            </optgroup>
            <optgroup label="o1 Series - Lý luận phức tạp">
                <option value="o1" {{ ($config['chatgpt_model'] ?? '') == 'o1' ? 'selected' : '' }}>o1 - $15.00/$60.00 per 1M tokens</option>
                <option value="o1-preview" {{ ($config['chatgpt_model'] ?? '') == 'o1-preview' ? 'selected' : '' }}>o1 Preview - $15.00/$60.00 per 1M tokens</option>
                <option value="o1-mini" {{ ($config['chatgpt_model'] ?? '') == 'o1-mini' ? 'selected' : '' }}>o1 Mini - $3.00/$12.00 per 1M tokens</option>
            </optgroup>
        </select>
    </div>
    
    <button class="btn-control btn-memory-on" id="btn-memory" onclick="toggleMemory()">
        <i class="bx bx-world"></i> <span>Memory ON</span>
    </button>

    <button class="btn-control btn-clear-chat" onclick="clearChat()">
        <i class="bx bx-trash"></i> Xóa chat
    </button>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card custom-card overflow-hidden">
            <div class="chat-container" style="height: calc(100vh - 235px); box-shadow: none; border-radius: 0;">
                <div class="chat-header">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-white text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-bot fs-4"></i>
                        </div>
                        <div>
                            <h5 class="m-0 text-white">KiyoAI</h5>
                            <small class="text-white-50">Sẵn sàng hướng đến tương lai của Việt Nam</small>
                        </div>
                    </div>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <div class="message-wrapper message-ai-wrapper">
                        <div class="message message-ai">
                            <div class="mb-2">🚀 <b>Chào mừng bạn đến với KiyoAI Assistant!</b></div>
                            <div class="mb-1">📊 <b>CÂU HỎI HỆ THỐNG CÓ THỂ TRẢ LỜI:</b></div>
                            <div class="mb-2">Bạn có thể hỏi bất kỳ thông tin nào xuất hiện trên Dashboard:</div>
                            <div class="mb-1">💰 <b>Doanh thu</b>: "doanh thu hôm nay", "lợi nhuận tháng này", "nạp tiền tuần qua"</div>
                            <div class="mb-1">👥 <b>Thành viên</b>: "tổng thành viên", "user mới hôm nay", "top người dùng"</div>
                            <div class="mb-1">📦 <b>Đơn hàng</b>: "đơn hàng đã bán hôm nay", "đơn hàng tháng 01", "dịch vụ bán chạy nhất"</div>
                            <div class="mb-2">🧠 <b>Chế độ:</b> 
                                <br>• <b>Mặc định</b>: Trả lời nhanh các số liệu hệ thống.
                                <br>• <b>Chatlgbt</b>: Phân tích chuyên sâu và tư vấn thông minh.</div>
                            <div>Hãy thử hỏi: "<b>doanh thu hôm nay</b>" hoặc "<b>tổng thành viên đăng ký</b>"</div>
                        </div>
                        <div class="message-time text-muted">{{ date('H:i d/m/Y') }}</div>
                    </div>
                </div>
                <div class="typing-indicator" id="typing-indicator">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    AI đang suy nghĩ...
                </div>
                <div class="chat-input-area">
                    <textarea class="chat-input" id="chat-input" rows="1" placeholder="Nhập câu hỏi tại đây... (Shift + Enter để xuống dòng)" 
                        onkeydown="if(event.keyCode==13 && !event.shiftKey) { event.preventDefault(); sendMessage(); }"></textarea>
                    <button class="btn-send" id="btn-send" onclick="sendMessage()">
                        <i class="bx bx-send fs-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    let chatHistory = [];
    let memoryEnabled = true;
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const typingIndicator = document.getElementById('typing-indicator');
    const btnSend = document.getElementById('btn-send');
    const btnMemory = document.getElementById('btn-memory');
    const aiTypeSelect = document.getElementById('ai-type-select');
    const modelSelect = document.getElementById('model-select');
    const modelSelectorWrapper = document.getElementById('model-selector-wrapper');

    function toggleAISelector() {
        if (aiTypeSelect.value === 'chatlgbt') {
            modelSelectorWrapper.style.display = 'block';
        } else {
            modelSelectorWrapper.style.display = 'none';
        }
    }

    function toggleMemory() {
        memoryEnabled = !memoryEnabled;
        const span = btnMemory.querySelector('span');
        if (memoryEnabled) {
            btnMemory.className = 'btn-control btn-memory-on';
            span.textContent = 'Memory ON';
        } else {
            btnMemory.className = 'btn-control btn-memory-off';
            span.textContent = 'Memory OFF';
        }
    }

    function appendMessage(role, content) {
        const wrapper = document.createElement('div');
        wrapper.className = `message-wrapper message-${role}-wrapper`;

        const msgDiv = document.createElement('div');
        msgDiv.className = `message message-${role}`;
        
        // Render Markdown for AI messages
        if (role === 'ai') {
            msgDiv.innerHTML = marked.parse(content);
        } else {
            msgDiv.textContent = content;
        }
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time text-muted';
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        timeDiv.textContent = `${hours}:${minutes} ${day}/${month}/${year}`;

        wrapper.appendChild(msgDiv);
        wrapper.appendChild(timeDiv);
        chatMessages.appendChild(wrapper);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Save to local history (limit to 5 pairs)
        chatHistory.push({ role: role === 'ai' ? 'assistant' : 'user', content: content });
        if (chatHistory.length > 10) chatHistory.shift(); 
    }

    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        chatInput.value = '';
        chatInput.style.height = 'auto'; // Reset height
        appendMessage('user', message);
        
        // Show typing indicator
        typingIndicator.style.display = 'block';
        chatMessages.scrollTop = chatMessages.scrollHeight;
        btnSend.disabled = true;

        try {
            const response = await axios.post('{{ route("admin.kiyoai.chat") }}', {
                message: message,
                history: chatHistory,
                model: modelSelect.value,
                memory: memoryEnabled,
                ai_type: aiTypeSelect.value
            });

            typingIndicator.style.display = 'none';
            btnSend.disabled = false;

            if (response.data.status) {
                appendMessage('ai', response.data.message);
            } else {
                appendMessage('ai', '⚠️ Lỗi: ' + response.data.message);
            }
        } catch (error) {
            typingIndicator.style.display = 'none';
            btnSend.disabled = false;
            let errorMsg = '❌ Lỗi kết nối: ' + (error.response?.data?.message || error.message || 'Không xác định');
            appendMessage('ai', errorMsg);
            console.error(error);
        }
    }

    // Auto-resize textarea
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    function clearChat() {
        if (confirm('Bạn có muốn xóa cuộc hội thoại này?')) {
            chatMessages.innerHTML = `
                <div class="message-wrapper message-ai-wrapper">
                    <div class="message message-ai">
                        <div class="mb-2">🚀 <b>Chào mừng bạn đến với KiyoAI Assistant!</b></div>
                        <div class="mb-1">📊 <b>CÂU HỎI HỆ THỐNG CÓ THỂ TRẢ LỜI:</b></div>
                        <div class="mb-2">Bạn có thể hỏi bất kỳ thông tin nào xuất hiện trên Dashboard:</div>
                        <div class="mb-1">💰 <b>Doanh thu</b>: "doanh thu hôm nay", "lợi nhuận tháng này", "nạp tiền tuần qua"</div>
                        <div class="mb-1">👥 <b>Thành viên</b>: "tổng thành viên", "user mới hôm nay", "top người dùng"</div>
                        <div class="mb-1">📦 <b>Đơn hàng</b>: "đơn hàng đã bán hôm nay", "đơn hàng tháng 01", "dịch vụ bán chạy nhất"</div>
                        <div class="mb-2">🧠 <b>Chế độ:</b> 
                            <br>• <b>Mặc định</b>: Trả lời nhanh các số liệu hệ thống.
                            <br>• <b>Chatlgbt</b>: Phân tích chuyên sâu và tư vấn thông minh.</div>
                        <div>Hãy thử hỏi: "<b>doanh thu hôm nay</b>" hoặc "<b>tổng thành viên đăng ký</b>"</div>
                    </div>
                    <div class="message-time text-muted">{{ date('H:i d/m/Y') }}</div>
                </div>
            `;
            chatHistory = [];
        }
    }
</script>
@endsection
