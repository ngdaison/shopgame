@extends('admin.layouts.master')
@section('title', 'Admin: Quản lý Tickets')

@section('css')
<style>
    /* Force no page scroll */
    html, body {
        overflow: hidden !important;
        height: 100% !important;
        position: fixed;
        width: 100%;
        margin: 0;
        overscroll-behavior: none;
    }
    /* Hide footer for app-like experience */
    footer, .footer {
        display: none !important;
    }
    
    /* Layout */
    .ticket-container {
        /* Header (~64px) + Breadcrumb (~76px) + Padding = approx 140-150px */
        height: calc(100vh - 160px); 
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
        overflow: hidden;
        display: flex;
    }
    .ticket-list-col {
        width: 300px;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        background: #f9fafb;
    }
    .ticket-chat-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
        min-height: 0; /* Important: prevents child clipping in flexbox */
    }
    .ticket-info-col {
        width: 250px;
        border-left: 1px solid #e5e7eb;
        background: #f9fafb;
        overflow-y: auto;
    }

    /* Ticket List Item */
    .ticket-item {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .ticket-item:hover {
        background: #f0f0f0;
    }
    .ticket-item.active {
        background: #e6f7ff;
        border-left: 3px solid #0d6efd;
    }
    .ticket-item .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    /* Facebook-like Online Dot */
    .avatar-container {
        position: relative;
        display: inline-flex; /* Shrink to wrap */
    }
    .online-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 15px; /* Bigger as requested */
        height: 15px;
        background-color: #31a24c; /* Facebook Green */
        border: 2px solid #fff;
        border-radius: 50%;
        z-index: 1;
    }
    .offline-dot {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 15px;
        height: 15px;
        background-color: #ccc; /* Grey */
        border: 2px solid #fff;
        border-radius: 50%;
        z-index: 1;
    }
    .ticket-item h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }
    .ticket-item p {
        margin: 0;
        font-size: 12px;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 180px;
    }
    .ticket-item .time {
        font-size: 10px;
        color: #999;
        position: absolute;
        top: 15px;
        right: 15px;
    }
    .ticket-item .badge-unread {
        position: absolute;
        bottom: 15px;
        right: 15px;
        font-size: 10px;
    }

    /* Chat Area */
    .chat-body::-webkit-scrollbar {
        width: 6px;
    }
    .chat-body::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 4px;
    }

    /* Tabs */
    .ticket-tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
    }
    .ticket-tab {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        border-bottom: 2px solid transparent;
    }
    .ticket-tab.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
    }
    .ticket-tab:hover {
        background: #f9fafb;
    }

    .ticket-list-col {
        width: 300px;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        background: #f9fafb;
    }

    /* Action Buttons */
    .btn-action {
        background-color: #f1f5f9 !important;
        border: none !important;
        border-radius: 8px !important;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        padding: 0;
        cursor: pointer;
        position: relative;
        z-index: 5;
    }
    .btn-action:hover {
        background-color: #e2e8f0 !important;
    }
    .btn-action i {
        color: #475569 !important;
        font-size: 16px;
    }
    .dropdown-menu {
        padding: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        z-index: 1060 !important;
    }
    .dropdown-menu.show {
        display: block !important;
    }
    .dropdown-item {
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .dropdown-item.text-danger:hover {
        background-color: #fef2f2;
    }

    /* Preview Item */
    .preview-item {
        position: relative;
        display: inline-block;
        margin-right: 15px;
        margin-top: 10px; 
    }
    .preview-item img {
        height: 60px;
        width: auto;
        min-width: 60px;
        max-width: 120px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .preview-item .btn-remove {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        background: red;
        color: white;
        border-radius: 50%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        padding: 0;
        z-index: 10;
    }
    
    .transition-opacity {
        transition: opacity 0.3s ease-in-out;
    }
    .chat-hidden {
        visibility: hidden !important;
        opacity: 0 !important;
    }

    /* Mobile Responsive Logic */
    @media (max-width: 767.98px) {
        /* Reset any conflicting styles */
        body {
            position: fixed;
            width: 100%;
            height: 100vh; /* Fallback */
            height: 100dvh;
        }

        .ticket-container {
            flex-direction: column;
            height: calc(100dvh - 60px) !important; 
            max-height: calc(100dvh - 60px) !important;
            width: 100vw;
            margin: 0;
            border-radius: 0;
            position: fixed; /* Changed to fixed to ensure it stays in view */
            top: 60px; /* Offset for header */
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40; /* Ensure it floats above standard content but maybe below navbar/modals */
        }

        .ticket-list-col {
            width: 100%;
            height: 100%;
            border-right: none;
            position: absolute; 
            z-index: 10;
            background: #f9fafb;
            display: flex; 
        }

        .ticket-chat-col {
            width: 100%;
            height: 100%;
            position: absolute;
            z-index: 20;
            background: #fff;
            display: none; 
        }

        .ticket-info-col {
            display: none !important; 
        }
        
        /* Smooth Scrolling for iOS */
        #ticket-list, #chat-messages-area {
            -webkit-overflow-scrolling: touch; 
        }
        
        /* State: Chat Active */
        .ticket-container.mobile-chat-active .ticket-list-col {
            display: none; 
        }
        .ticket-container.mobile-chat-active .ticket-chat-col {
            display: flex;
        }
        
        /* Back Button */
        .btn-mobile-back {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            margin-right: 8px;
            background: #f1f5f9;
            border-radius: 50%;
            color: #475569;
            text-decoration: none;
        }
    }
    @media (min-width: 768px) {
        .btn-mobile-back {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="ticket-container">
    <!-- LEFT: Ticket List -->
    <div class="ticket-list-col">
        <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0 fw-bold">Messages</h5>
                <small class="text-muted" id="ticket-count">Showing 0 / 0</small>
            </div>
            <div class="dropdown d-inline-block">
                <button id="btn-action-more" class="btn btn-action text-primary bg-primary-subtle" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-cog"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 180px;">
                    <li><a class="dropdown-item py-2" href="javascript:void(0)" id="btn-quick-reply-manage">Trả lời nhanh</a></li>
                    <li><a class="dropdown-item py-2" href="javascript:void(0)" id="btn-ticket-settings" data-bs-toggle="modal" data-bs-target="#modal-ticket-settings">Cài đặt</a></li>
                </ul>
            </div>
        </div>
        <div class="ticket-tabs">
            <div class="ticket-tab active" onclick="switchTab('open')" id="tab-open">Tin nhắn</div>
            <div class="ticket-tab" onclick="switchTab('closed')" id="tab-closed">Đóng</div>
        </div>
        <div class="flex-grow-1 overflow-auto" id="ticket-list">
            <!-- Loaded via JS -->
            <div class="text-center p-4 text-muted">
                <i class="fa fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>

    <!-- MIDDLE: Chat Area -->
    <div class="ticket-chat-col" id="ticket-chat-col">
        <!-- Empty State -->
        <div id="chat-empty-state" class="d-flex align-items-center justify-content-center h-100 text-muted">
            <div class="text-center">
                <i class="fa fa-comments fa-3x mb-3 text-light"></i>
                <p>Select a conversation to start chatting</p>
            </div>
        </div>
        
        <!-- Chat Interface (Hidden by default) -->
        <div id="chat-interface" class="flex-column h-100 d-none" style="display: none;">

            <!-- Header -->
            <div class="chat-header d-flex align-items-center justify-content-between border-bottom p-3 bg-white">
                <div class="d-flex align-items-center">
                    <a href="javascript:void(0)" class="btn-mobile-back" onclick="backToList()">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <div class="avatar me-3 position-relative" id="chat-header-avatar-wrap">
                        <img id="chat-header-avatar" src="" alt="" class="rounded-circle border" width="40" height="40" style="object-fit: cover;" onerror="this.src='{{ asset('/images/avatar/av-1.svg') }}'">
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold" id="chat-header-username">Username</h6>
                        <small class="text-muted" id="chat-header-title">Title</small>
                        <span id="chat-header-status" class="badge ms-2"></span>
                    </div>
                </div>
                <!-- Actions -->
                <div class="chat-actions d-flex gap-3">
                    <button id="btn-action-complete" class="btn btn-action" onclick="updateStatus(activeTicketId, 'closed')" title="Hoàn thành">
                        <i class="fa fa-check"></i>
                    </button>
                    <button id="btn-action-delete" class="btn btn-action" onclick="deleteCurrentTicket()" title="Xóa">
                        <i class="fa fa-times"></i>
                    </button>

                </div>
            </div>
            
            <!-- Messages -->
            <div id="chat-messages-area" class="flex-grow-1 overflow-auto p-3" style="background: #f5f7f9;">
                <!-- Messages injected here -->
            </div>
            
            <!-- Disclaimer / Typing Indicator (Optional) -->
            <div id="chat-typing-indicator" class="px-3" style="display:none;"><small class="text-muted">User is typing...</small></div>
            
            <!-- Composer -->
            <div class="chat-footer p-3 bg-white border-top">
                <div class="position-relative">
                    <!-- Image Previews -->
                    <div id="composer-previews" class="d-flex mb-2 overflow-auto ps-1 pt-1" style="max-height: 100px;"></div>
                    <input type="file" id="chat-image-input" class="d-none" accept="image/*" multiple onchange="handleImageUpload(this)">
                    <button class="btn btn-link p-1 border-0 shadow-none position-absolute" style="left: 10px; bottom: 2px; color: #6c757d; z-index: 10;" onclick="$('#chat-image-input').click()" title="Upload Image">
                        <i class="fa fa-image fa-lg"></i>
                    </button>
                    
                    <textarea id="chat-composer" class="form-control border shadow-none bg-light pt-1 pb-1" rows="1" placeholder="Type a message..." style="resize:none; border-radius: 20px; overflow:hidden; max-height:300px; padding-left: 45px; padding-right: 75px; min-height: 32px; line-height: 24px;"></textarea>
                    
                    <div class="d-flex align-items-center position-absolute" style="right: 10px; bottom: 2px; gap: 2px;">
                        <button class="btn btn-link p-1 border-0 shadow-none" type="button" onclick="sendReplyCurrent()" title="Send" style="color: #7d52f4;">
                             <i class="fa fa-paper-plane fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Info Sidebar -->
    <div class="ticket-info-col" id="ticket-info">
        <!-- Loaded via JS -->
    </div>

    <!-- Quick Reply: Exact Screenshot Match -->
    <div class="modal fade" id="modal-quick-replies" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size: 1.25rem; color: #334155;">
                        <i class="fas fa-list-ul"></i> Quản lý Quick Replies
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- List Header Row -->
                    <div class="mb-3">
                        <span class="fw-bold" style="font-size: 1rem; color: #94a3b8;">Danh sách câu trả lời nhanh</span>
                    </div>

                    <!-- List Area / Empty State -->
                    <div id="qr-list-container">
                        <!-- Empty state or list will be rendered here -->
                    </div>

                    <!-- Form Area (Bordered box) -->
                    <div class="mt-4 p-4 border" style="border-radius: 12px; border-color: #f1f5f9 !important;">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <label class="form-label fw-bold mb-2" style="color: #334155;">Lệnh (ví dụ: /gia)</label>
                                <input type="text" class="form-control py-2" id="qr-command" placeholder="/command" 
                                       style="border-radius: 8px; background-color: #fff; border: 1px solid #e2e8f0; height: 45px;">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold mb-2" style="color: #334155;">Nội dung trả lời</label>
                                <textarea class="form-control" id="qr-content" rows="4" 
                                          placeholder="Nhập nội dung sẽ chèn vào khung chat"
                                          style="border-radius: 8px; background-color: #fff; border: 1px solid #e2e8f0;"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button class="btn btn-outline-info d-flex align-items-center gap-2 px-4 py-2" 
                                    style="border-radius: 8px; border-color: #0dcaf0; color: #0dcaf0; font-weight: 600;"
                                    onclick="resetQRForm()">
                                <i class="fas fa-sync-alt"></i> Hủy
                            </button>
                            <button class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 text-white" 
                                    style="border-radius: 8px; background-color: #7d52f4; border-color: #7d52f4; font-weight: 600;"
                                    onclick="saveQuickReply()">
                                <i class="fas fa-save"></i> Lưu câu trả lời
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div> <!-- End ticket-container -->

    <!-- Ticket Settings Modal -->
    <div class="modal fade" id="modal-ticket-settings" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" style="font-size: 1.25rem; color: #334155;">
                        <i class="fas fa-cog"></i> Cấu hình Ticket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                     @php $ticket_config = Helper::getConfig('ticket_config'); @endphp
                     <form action="{{ route('admin.settings.general.update', ['type' => 'ticket_config']) }}" method="POST" class="default-form axios-form" data-reload="true">
                        @csrf
                        <div class="row">
                          <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Chủ đề Ticket</label>
                            <input type="text" class="form-control" name="ticket_categories" placeholder="Hỗ trợ nạp tiền, Hỗ trợ lỗi game, Hỗ trợ khác" value="{{ isset($ticket_config['ticket_categories']) ? str_replace(["\r\n", "\r", "\n"], ", ", $ticket_config['ticket_categories']) : '' }}">
                            <small class="text-muted">Nhập các chủ đề hỗ trợ, phân cách bằng dấu phẩy.</small>
                          </div>
                        </div>
                        <div class="text-end">
                          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Đóng</button>
                          <button type="submit" class="btn btn-primary px-4">Lưu Cấu Hình</button>
                        </div>
                     </form>
                </div>
            </div>
        </div>
    </div>

<!-- Emoji CSS & Slash Command CSS -->
<style>
    .emoji-picker-container {
        display: none;
        position: absolute;
        bottom: 50px;
        left: 0;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        width: 300px;
        height: 200px;
        overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 5px;
    }
    .emoji-item {
        cursor: pointer;
        padding: 5px;
        text-align: center;
        border-radius: 4px;
        font-size: 18px;
    }
    .emoji-item:hover {
        background: #f0f0f0;
    }
    
    .slash-autocomplete {
        display: none;
        position: absolute;
        z-index: 1050;
        width: 100%;
        max-width: 450px; /* Fixed width like screenshot */
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #f1f5f9;
        font-family: inherit;
    }
    .slash-header {
        padding: 10px 15px;
        font-weight: 700;
        color: #475569;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        font-size: 14px;
    }
    .btn-close-slash {
        cursor: pointer;
        color: #94a3b8;
        font-size: 14px;
        border: none;
        background: none;
    }
    .slash-items-wrapper {
        max-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
        padding: 5px 0;
    }
    .slash-item {
        padding: 12px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: background 0.2s;
        position: relative;
    }
    .slash-item:last-child {
        margin-bottom: 0;
    }
    .slash-item:hover, .slash-item.active {
        background: #f1f5f9;
    }
    .slash-tag {
        display: inline-block;
        background: #f1f5f9;
        color: #1e293b; /* Darker */
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700; /* Bolder */
        font-size: 13px;
        width: fit-content;
    }
    .slash-content {
        font-size: 14px;
        color: #334155;
        font-weight: 600; /* Bolder */
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.5;
        max-width: 100%; /* Use full width */
    }
    .slash-item.has-image .slash-content {
        max-width: calc(100% - 60px); /* Leave room for image on the right */
    }
    .slash-item img {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        border-radius: 4px;
        object-fit: cover;
    }
</style>
@endsection

@section('scripts')
<script>
    let currentTab = 'open';
    let activeTicketId = null;
    let poller = null;
    let firstLoad = true;
    let quickReplies = [];
    let oldestMessageId = null; 
    let isLoadingOld = false; // Prevent double fetch
    let isAllLoaded = false;
    let ticketListAbortController = null;
    let resizeObserver = null;
    let pinToBottom = false;
    


    $(document).ready(function() {
        // Init logic
        const urlParams = new URLSearchParams(window.location.search);
        const msgId = urlParams.get('messages');
        
        if (msgId === 'closed') {
            switchTab('closed', false);
        } else {
            fetchTickets().then(() => {
                if (msgId) {
                    openTicket(msgId, false);
                }
            });
        }

        startPolling();
        loadQuickReplies();
        initScrollListener();
        bindEnterKey(); // Initial bind
        
        // --- Event Delegation ---
        
        // Slash (Keep existing logic, just ensure selectors work)
        
        $(document).on('click', function(e) {
             if (!$(e.target).closest('#chat-composer, #slash-autocomplete').length) {
                $('#slash-autocomplete').hide();
             }
             if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').removeClass('show');
             }
        });

        // Manual Dropdown Toggle (Fallback)
        $(document).on('click', '#btn-action-more', function(e) {
             e.preventDefault();
             e.stopPropagation();
             $(this).next('.dropdown-menu').toggleClass('show');
        });
        
        
        // Quick Reply Modal
        $(document).on('click', '#btn-quick-reply-manage', function() {
            $('#modal-quick-replies').modal('show');
            renderQuickReplyList();
            // Close dropdown
            $(this).closest('.dropdown-menu').removeClass('show');
            $('.dropdown-menu').removeClass('show'); // Safety catch
        });
        
        // Ticket Settings Modal
        $(document).on('click', '#btn-ticket-settings', function() {
            // Close dropdown
            $(this).closest('.dropdown-menu').removeClass('show');
            $('.dropdown-menu').removeClass('show');
        });

        // Slash Command (Existing Logic maintained but ensure ID matches)
        let selectedSlashIndex = -1;
        $(document).on('keyup', '#chat-composer', function(e) {
            if (['ArrowUp', 'ArrowDown', 'Enter', 'Escape'].includes(e.key)) return;
            const val = $(this).val();
            const lastWord = val.split(/[\s\n]+/).pop();
            if (lastWord && lastWord.startsWith('/')) {
                const query = lastWord.substring(1).toLowerCase();
                const matches = quickReplies.filter(q => {
                    const cmd = String(q.command).toLowerCase();
                    const content = String(q.content).toLowerCase();
                    return cmd.includes(query) || content.includes(query);
                });
                if (matches.length > 0) {
                    showSlashAutocomplete(matches);
                    selectedSlashIndex = 0;
                    updateSlashSelection();
                } else { $('#slash-autocomplete').hide(); }
            } else { $('#slash-autocomplete').hide(); }
        });
        
        $(document).on('keydown', '#chat-composer', function(e) {
             const $box = $('#slash-autocomplete');
             if ($box.is(':visible')) {
                 if (e.key === 'ArrowUp') {
                     e.preventDefault(); selectedSlashIndex = Math.max(0, selectedSlashIndex - 1); updateSlashSelection();
                 } else if (e.key === 'ArrowDown') {
                     e.preventDefault(); selectedSlashIndex = Math.min($box.find('.slash-item').length - 1, selectedSlashIndex + 1); updateSlashSelection();
                 } else if (e.key === 'Enter') {
                     if (selectedSlashIndex >= 0) { e.preventDefault(); $box.find('.slash-item').eq(selectedSlashIndex).click(); }
                 } else if (e.key === 'Tab') {
                     e.preventDefault(); if (selectedSlashIndex >= 0) $box.find('.slash-item').eq(selectedSlashIndex).click();
                 } else if (e.key === 'Escape') { $box.hide(); }
             }
        });

        $(document).on('click', '.slash-item', function() {
             const content = $(this).data('content');
             const input = document.getElementById('chat-composer');
             $(input).val(content); // Replace all content
             $('#slash-autocomplete').hide();
             $(input).focus();
             // Trigger auto-resize
             $(input).trigger('input');
        });
    });
    
    // --- Core Navigation ---

    function openTicket(id, pushState = true) {
        if (id === 'closed') return; // Safety check
        if (activeTicketId == id) return;
        activeTicketId = id;
        
        // UI Highlight
        $('.ticket-item').removeClass('active');
        const $item = $(`.ticket-item[data-id="${id}"]`);
        $item.addClass('active');
        $item.find('.badge-unread').remove();
        $item.find('p').removeClass('fw-bold text-dark');

        // URL Update
        if (pushState) {
            const url = new URL(window.location);
            url.searchParams.set('messages', id);
            window.history.pushState({ticketId: id}, '', url);
        }
        
        loadTicketContent(id);

        // Mobile Toggle
        $('.ticket-container').addClass('mobile-chat-active');
    }

    function backToList() {
        $('.ticket-container').removeClass('mobile-chat-active');
        // Optional: Clear active state if desired, but keeping it selected is fine
        // activeTicketId = null; 
        // $('.ticket-item').removeClass('active');
    }
    
    
    // Browse Back Handling
    window.addEventListener('popstate', function(e) {
        if(e.state && e.state.ticketId) {
            openTicket(e.state.ticketId, false);
        } else {
            // Check URL if state null
            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('messages');
            if(id) openTicket(id, false);
        }
    });

    async function loadTicketContent(id) {
        // Force hide empty state and show interface
        $('#chat-empty-state').addClass('d-none').removeClass('d-flex').hide();
        $('#chat-interface').addClass('d-flex').removeClass('d-none').show();
        
        // Ensure parent column allows proper flex shrinking
        $('.ticket-chat-col').css('min-height', '0');
        
        // Show loading in messages area
        $('#chat-messages-area').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin text-muted"></i></div>');
        $('#ticket-info').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin text-muted"></i></div>');



        
        // Reset Pagination
        oldestMessageId = null;
        isAllLoaded = false;
        
        try {
            const res = await axios.get(`/admin/tickets/content/${id}`);
            if (res.data.status) {
                // Setup ResizeObserver to maintain bottom scroll during image load
                const $msgArea = $('#chat-messages-area');
                if (resizeObserver) resizeObserver.disconnect();
                
                pinToBottom = true;
                resizeObserver = new ResizeObserver(() => {
                    const $area = $('#chat-messages-area');
                    if (pinToBottom && $area.length) {
                        $area.css('scroll-behavior', 'auto');
                        $area.scrollTop($area[0].scrollHeight);
                        $area.css('scroll-behavior', '');
                    }
                });
                // We observe the inner content AFTER renderMessages creates it. 
                // But renderMessages is called below.
                // So we postpone observation? No, renderMessages acts synchronously.
                // We will call observe AFTER renderMessages.
                
                // Note: Scroll listener is now bound in scrollToBottom to prevent early trigger
                
                renderHeader(res.data.ticket);
                $('#ticket-info').html(res.data.info_html);
                
                // Messages
                // const $msgArea = $('#chat-messages-area'); // Already defined above
                $msgArea.removeClass('transition-opacity chat-hidden').addClass('chat-hidden');
                
                renderMessages(res.data.messages, false); // Replace
                
                // Now observe inner content
                const $inner = $('#chat-inner-content');
                if ($inner.length) resizeObserver.observe($inner[0]);

                if (res.data.messages.length > 0) {
                    lastId = res.data.messages[res.data.messages.length - 1].id;
                }
                scrollToBottom(true);
                
                // Reset Composer
                $('#chat-composer').val('').focus();
            }
        } catch (e) {
            console.error(e);
            toastr.error('Failed to load ticket');
        }
    }


    
    function renderHeader(ticket) {
        $('#chat-header-username').text(ticket.user.username);
        // Truncate title
        const title = ticket.title.length > 20 ? ticket.title.substring(0, 20) + '...' : ticket.title;
        $('#chat-header-title').text(title).attr('title', ticket.title); // Add tooltip
        
        const avatar = ticket.user.avatar || '{{ asset("/images/avatar/av-1.svg") }}';
        $('#chat-header-avatar').attr('src', avatar);
        
        // Online Dot for Header
        $('#chat-header-online-dot').remove();
        if (ticket.user.is_online) {
             $('#chat-header-avatar-wrap').append('<span id="chat-header-online-dot" class="online-dot" title="Online"></span>');
        }

        const statusColors = { open: 'bg-success', pending: 'bg-warning', closed: 'bg-danger' };
        const statusLabels = { open: 'Đang mở', pending: 'Đang chờ', closed: 'Đóng' };
        $('#chat-header-status').removeClass('bg-success bg-warning bg-danger')
            .addClass(statusColors[ticket.status] || 'bg-secondary')
            .text(statusLabels[ticket.status] || ticket.status);

        // Button Visibility
        if (ticket.status === 'closed') {
            $('#btn-action-complete').hide();
            $('#btn-action-delete').show();
        } else {
            $('#btn-action-complete').show();
            $('#btn-action-delete').hide();
        }
    }

    function renderMessages(messages, prepend = false) {
        // messages is array of objects
        // If prepend, we add to top.
        // If replace (not prepend), we overwrite.
        
        let html = messages.map(msg => createMessageHtml(msg)).join('');
        const $area = $('#chat-messages-area');
        
        // Ensure inner wrapper exists
        let $inner = $('#chat-inner-content');
        if ($inner.length === 0) {
            $area.html('<div id="chat-inner-content"></div>');
            $inner = $('#chat-inner-content');
        }

        if (!prepend) {
            $inner.html(html);
            // Update oldest ID
            if(messages.length > 0) oldestMessageId = messages[0].id; 
        } else {
            // Infinite Scroll Prepend
            // Save scroll height / top
            const oldHeight = $area[0].scrollHeight;
            const oldTop = $area[0].scrollTop;
            
            $inner.prepend(html);
            
            // Restore Scroll Position
            // New items added at top -> scrollHeight increases.
            // We want to stay at same "relative" position for user? 
            // Actually, we want to stay at the same visible message.
            // New ScrollTop = OldScrollTop + (NewScrollHeight - OldScrollHeight)
            const newHeight = $area[0].scrollHeight;
            $area.scrollTop(oldTop + (newHeight - oldHeight));
            
             // Update oldest ID from new batch
             if(messages.length > 0) oldestMessageId = messages[0].id;
        }
        
        if (messages.length < 30) isAllLoaded = true; // Primitive check
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function createMessageHtml(msg) {
        const initialBadge = msg.is_initial 
            ? '<span class="badge bg-dark mb-2">Nội dung ban đầu</span>' 
            : '';
            
        let content = escapeHtml(msg.message);
        // Parse Markdown Images: ![alt](url)
        content = content.replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1" class="img-fluid rounded mt-2 d-block" style="max-height: 200px;" onload="scrollToBottom()">');
            
        return `
            <div id="message-${msg.id}" class="mb-3 d-flex ${msg.sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start'}" data-message-id="${msg.id}">
                <div class="${msg.sender_type === 'admin' ? 'bg-primary text-white' : 'bg-white border text-dark'} p-2 rounded shadow-sm" style="max-width: 75%;">
                    ${initialBadge}
                    <div class="mb-0 text-break" style="white-space: pre-wrap; word-break: break-all;">${content}</div>
                    <small class="${msg.sender_type === 'admin' ? 'text-white-50' : 'text-muted'} d-block text-end mt-1" style="font-size: 10px;">${msg.time_str || msg.created_at}</small>
                </div>
            </div>
        `;
    }
    
    // --- Infinite Scroll ---
    function initScrollListener() {
        $('#chat-messages-area').on('scroll', function() {
            if ($(this).scrollTop() === 0 && !isLoadingOld && !isAllLoaded && activeTicketId) {
                loadOlderMessages();
            }
        });
    }
    
    async function loadOlderMessages() {
        if (!oldestMessageId) return;
        isLoadingOld = true;
        
        // Show loading spinner? (Optional)
        
        try {
            const res = await axios.get(`/admin/tickets/${activeTicketId}/messages`, {
                params: {
                    limit: 30,
                    before_id: oldestMessageId
                }
            });
            
            if (res.data.status && res.data.data.length > 0) {
                renderMessages(res.data.data, true); // Prepend
            } else {
                isAllLoaded = true;
            }
        } catch (e) {
            console.error(e);
        } finally {
            isLoadingOld = false;
        }
    }
    
    // --- Sending ---
    async function sendReplyCurrent() {
        if(!activeTicketId) return;
        sendReply(activeTicketId);
    }
    
    async function sendReply(id) {
        const $input = $('#chat-composer');
        const msg = $input.val().trim();
        if (!msg) return;
        
        $input.val('').focus().css('height', 'auto');

        try {
            const res = await axios.post(`/admin/tickets/${id}/reply`, { message: msg });
            if (res.data.status) {
                const newMsg = res.data.data;
                
                // Update Status UI (Backend auto-reopens)
                $('#chat-header-status').removeClass('bg-warning bg-danger').addClass('bg-success').text('Đang mở');
                $('#btn-action-complete').show();
                $('#btn-action-delete').hide();
                
                // Append
                $('#chat-inner-content').append(createMessageHtml(newMsg));
                lastId = parseInt(newMsg.id);
                scrollToBottom();
                
                // Sidebar Update (Manual)
                const $item = $(`.ticket-item[data-id="${id}"]`);
                if ($item.length) {
                    let preview = newMsg.message.replace(/<[^>]*>?/gm, '');
                    if(preview.length > 40) preview = preview.substring(0, 40) + '...';
                    $item.find('.msg-preview').text(preview);
                    $item.find('.time').text('Just now');
                    $item.find('.fa-reply').remove();
                    // Remove yellow immediately so user sees it "lose priority"
                    $item.removeClass('bg-warning-subtle').addClass('active');
                    // We DO NOT move to top. We let it stay here until fetchTickets re-sorts it to the "White" section.
                }
                
                // Refresh list to reorder (Admin replied -> moves down below 'Waiting' tickets)
                fetchTickets(false);
            }
        } catch (e) { toastr.error('Failed to send'); }
    }
    
    function scrollToBottom(reveal = false) {
        const $area = $('#chat-messages-area');
        if($area.length) {
            // Force instant scroll
            $area.css('scroll-behavior', 'auto');
            $area.scrollTop($area[0].scrollHeight);
            
            if (reveal) {
                // Use setTimeout to allow layout to settle
                setTimeout(() => {
                    // Force scroll again immediately before reveal
                    $area.scrollTop($area[0].scrollHeight);
                    
                    requestAnimationFrame(() => {
                         // Remove hidden class and add transition
                         $area.removeClass('chat-hidden');
                         $area.css('visibility', '').css('opacity', ''); // Clear inline if any
                         $area.addClass('transition-opacity').css('opacity', '1');
                         
                         $area.css('scroll-behavior', '');
                    });
                }, 300); // Increased to 300ms for safety on initial load
            } else {
                 $area.css('scroll-behavior', '');
            }
        }
    }
    
    // Force check on window load (image resources)
    $(window).on('load', function() {
        if(activeTicketId) {
             const $area = $('#chat-messages-area');
             if($area.length) $area.scrollTop($area[0].scrollHeight);
        }
    });
    
    function bindEnterKey() {
         const textarea = $('#chat-composer');
         if (textarea.length === 0) return; 

         // Auto-resize logic
         textarea.on('input', function() {
             this.style.height = '32px'; 
             let newHeight = this.scrollHeight;
             if (newHeight > 300) {
                 this.style.height = '300px';
                 this.style.overflowY = 'auto';
             } else {
                 this.style.height = newHeight + 'px';
                 this.style.overflowY = 'hidden';
             }
             scrollToBottom();
         });

         textarea.off('keydown.composer').on('keydown.composer', function(e) {
             if (e.key === 'Enter' && !e.shiftKey) {
                 if ($('#slash-autocomplete').is(':visible')) return; 
                 e.preventDefault();
                 sendReplyCurrent();
                 // Height reset handles in sendReply but also here for safety
                 this.style.height = 'auto';
             }
         });
    }  
    
    // --- Polling (Sync New Messages) ---
    function startPolling() {
        if (poller) clearInterval(poller);
        poller = setInterval(() => {
            fetchTickets(false);
            if (activeTicketId) pollChatUpdates();
        }, 3000);
    }
    
    async function pollChatUpdates() {
        const polledTicketId = activeTicketId;
        if (!polledTicketId) return;

        const $lastMsg = $('#chat-inner-content').children().last();
        let lastId = 0;
        if ($lastMsg.length && $lastMsg.data('message-id')) {
            lastId = parseInt($lastMsg.data('message-id'));
        }
        
        try {
            const res = await axios.get(`/admin/tickets/${polledTicketId}/messages`, { params: { limit: 20 } });
            if (activeTicketId !== polledTicketId) return; // User switched tickets during poll
            
            if (res.data.status && res.data.data) {
                // Update Status UI from poll
                const ts = res.data.ticket_status;
                if (ts) {
                    const statusColors = { open: 'bg-success', closed: 'bg-danger' };
                    const statusLabels = { open: 'Đang mở', closed: 'Đóng' };
                    $('#chat-header-status').removeClass('bg-success bg-warning bg-danger')
                        .addClass(statusColors[ts] || 'bg-secondary')
                        .text(statusLabels[ts] || ts);
                    
                    if (ts === 'closed') {
                        $('#btn-action-complete').hide();
                        $('#btn-action-delete').show();
                    } else {
                        $('#btn-action-complete').show();
                        $('#btn-action-delete').hide();
                    }
                }

                const msgs = res.data.data;
                const newMsgs = msgs.filter(m => parseInt(m.id) > lastId);
                
                if (newMsgs.length > 0) {
                    const wasAtBottom = isScrolledToBottom();
                    let html = '';
                    newMsgs.forEach(m => {
                         // Check if message already exists in DOM to prevent duplication
                         if ($(`#message-${m.id}`).length === 0) {
                             html += createMessageHtml(m);
                         }
                    });
                    
                    if (html) {
                        $('#chat-inner-content').append(html);
                        // Update lastId to the true last ID in the list
                        const $last = $('#chat-inner-content').children().last();
                        if ($last.length) lastId = parseInt($last.data('message-id'));
                    }
                    
                    if (wasAtBottom) {
                        scrollToBottom();
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.info('Có tin nhắn mới');
                        }
                    }
                }
            }
        } catch (e) {
            console.error("Polling error:", e);
        }
    }
    
    function isScrolledToBottom() {
        const $area = $('#chat-messages-area');
        if (!$area.length) return false;
        // logic: scrollTop + clientHeight >= scrollHeight - tolerance
        return $area[0].scrollTop + $area[0].clientHeight >= $area[0].scrollHeight - 50;
    }

    // --- Helpers ---
    function switchTab(tab, pushUrl = true) {
        currentTab = tab;
        $('.ticket-tab').removeClass('active');
        $(`#tab-${tab}`).addClass('active');
        fetchTickets(true);

        if (pushUrl) {
            const url = new URL(window.location);
            if (tab === 'closed') {
                url.searchParams.set('messages', 'closed');
            } else {
                url.searchParams.delete('messages');
            }
            window.history.pushState({}, '', url);
        }
    }

    async function fetchTickets(showLoading = false) {
        if (showLoading) $('#ticket-list').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        
        // Cancel any pending ticket list request
        if (ticketListAbortController) {
            ticketListAbortController.abort();
        }
        ticketListAbortController = new AbortController();
        const signal = ticketListAbortController.signal;

        try {
            const onlineOnly = $('#filter-online-only').is(':checked');
            const res = await axios.get('{{ route("admin.tickets.list") }}', { 
                params: { 
                    status: currentTab
                },
                signal: signal
            });
            
            if (res.data.status) {
                renderTicketList(res.data.data);
                $('#ticket-count').text(`Showing ${res.data.data.length} tickets`);
                firstLoad = false;
            }
        } catch (e) {
            if (axios.isCancel(e) || e.name === 'AbortError') {
                return;
            }
            console.error('Fetch tickets error:', e);
        }
    }

    function renderTicketList(tickets) {
        let html = '';
        if (tickets.length === 0) {
            html = '<div class="text-center p-4 text-muted">No tickets found.</div>';
        } else {
            tickets.forEach(t => {
                const isActive = activeTicketId == t.id ? 'active' : '';
                const showBadge = t.unread_for_admin > 0 && activeTicketId != t.id;
                
                const unreadBadge = showBadge ? `<span class="badge bg-danger rounded-pill badge-unread">${t.unread_for_admin}</span>` : '';
                const waitingClass = t.is_waiting ? 'bg-warning-subtle' : ''; 
                
                const onlineBadge = t.user.is_online ? '<span class="online-dot" title="Online"></span>' : '';

                html += `
                <div class="ticket-item ${isActive} ${waitingClass}" onclick="openTicket(${t.id})" data-id="${t.id}">
                    <div class="d-flex align-items-center">
                        <div class="avatar-container me-2"> 
                            <img src="${t.user.avatar || '{{ asset("/images/avatar/av-1.svg") }}'}" class="avatar">
                            ${onlineBadge}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="text-truncate">${t.user.username} <span class="badge bg-light text-dark border ms-1" style="font-size:9px;">${t.category}</span></h6>
                            <p class="mb-0 ${showBadge ? 'fw-bold text-dark' : 'text-muted'}">
                                ${t.is_waiting ? '<i class="fa fa-reply text-warning me-1"></i>' : ''}
                                <span class="msg-preview">${t.last_message}</span>
                            </p>
                        </div>
                    </div>
                    <span class="time">${t.updated_at_human}</span>
                    ${unreadBadge}
                </div>
                `;
            });
        }
        $('#ticket-list').html(html);
    }
    
    // Slash Helpers already defined above/delegated. 
    function updateSlashSelection() {
        const $items = $('.slash-item');
        $items.removeClass('active');
        if (selectedSlashIndex >= 0 && selectedSlashIndex < $items.length) {
            const $active = $items.eq(selectedSlashIndex);
            $active.addClass('active');
            // scrollIntoView logic if needed
        }
    }
    
    function showSlashAutocomplete(matches) {
        let $box = $('#slash-autocomplete');
        if ($box.length === 0) {
             $('body').append('<div id="slash-autocomplete" class="slash-autocomplete"></div>');
             $box = $('#slash-autocomplete');
        }
        const $textarea = $('#chat-composer');
        const offset = $textarea.offset();
        // Height of the box approx
        const boxHeight = Math.min(400, (matches.length * 80) + 50);
        
        $box.css({ 
            bottom: $(window).height() - offset.top + 25, 
            left: offset.left - 4, 
            width: 'auto',
            'min-width': '350px',
            'max-width': Math.min(500, $textarea.outerWidth()), 
            display: 'block' 
        });
        
        let html = `
            <div class="slash-header">
                <span>Tin nhắn nhanh</span>
                <button class="btn-close-slash" onclick="$('#slash-autocomplete').hide()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slash-items-wrapper">`;
            
        matches.forEach(m => {
            // Check for image in content (rough check for URL in content or specific field if we had one)
            // For now, let's see if we can extract an image tag or just placeholder logic
            const imgMatch = m.content.match(/<img[^>]+src="([^">]+)"/);
            const imgHtml = imgMatch ? `<img src="${imgMatch[1]}">` : '';
            const plainContent = m.content.replace(/<[^>]*>/g, ''); // Extract text only for preview
            
            const hasImageClass = imgMatch ? 'has-image' : '';
            
            html += `
                <div class="slash-item ${hasImageClass}" data-content="${m.content}">
                    <div class="slash-tag">/${m.command}</div>
                    <div class="slash-content">${plainContent}</div>
                    ${imgHtml}
                </div>`;
        });
        html += '</div>';
        $box.html(html);
    }
    
    // Quick Replies & Delete helpers
    async function loadQuickReplies() {
         try { const res = await axios.get('{{ route("admin.quick-replies.index") }}'); if(res.data.status) { quickReplies = res.data.data; renderQuickReplyList(); } } catch(e) {}
    }
    function renderQuickReplyList() {
        if (quickReplies.length === 0) {
            $('#qr-list-container').html(`
                <div class="p-3 d-flex align-items-center gap-2" style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <i class="fas fa-info-circle text-muted"></i>
                    <span class="text-muted" style="font-size: 0.9rem;">Chưa có câu trả lời nhanh nào. Hãy thêm mới bên dưới.</span>
                </div>
            `);
        } else {
            let html = '<div class="list-group list-group-flush border rounded-3 overflow-hidden">';
            quickReplies.forEach(q => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="overflow-hidden">
                            <span class="badge bg-primary-subtle text-primary mb-1">/${q.command}</span>
                            <div class="small text-muted text-truncate" style="max-width: 500px;">${q.content}</div>
                        </div>
                        <button class="btn btn-link text-danger p-1" onclick="deleteQuickReply(${q.id})">
                            <i class="fa fa-trash-alt"></i>
                        </button>
                    </div>`;
            });
            html += '</div>';
            $('#qr-list-container').html(html);
        }
    }

    function resetQRForm() {
        $('#qr-command').val('');
        $('#qr-content').val('');
        $('#qr-command').focus();
    }

    async function saveQuickReply() {
        let cmd = $('#qr-command').val().trim();
        const content = $('#qr-content').val().trim();
        
        if(!cmd || !content) return toastr.error('Vui lòng nhập đầy đủ lệnh và nội dung');
        if(!cmd.startsWith('/')) cmd = '/' + cmd;
        
        try { 
            const res = await axios.post('{{ route("admin.quick-replies.store") }}', { command: cmd.substring(1), content: content }); 
            if(res.data.status) { 
                toastr.success('Đã lưu câu trả lời nhanh'); 
                resetQRForm();
                loadQuickReplies(); 
            } 
        } catch(e) { 
            toastr.error('Lệnh này đã tồn tại hoặc có lỗi xảy ra'); 
        }
    }
    async function deleteQuickReply(id) {
        if(!confirm('Delete?')) return;
        try { await axios.post('{{ route("admin.quick-replies.delete") }}', {id}); loadQuickReplies(); toastr.success('Deleted'); } catch(e) {}
    }
    
    async function deleteCurrentTicket() {
        if(!activeTicketId || !confirm('Xác nhận xóa yêu cầu này?')) return;
         try { 
             await axios.post('{{ route("admin.tickets.delete") }}', {id: activeTicketId}); 
             toastr.success('Đã xóa yêu cầu');
             window.location.href = '{{ route("admin.tickets") }}?messages=closed';
         } catch(e) { toastr.error('Lỗi khi xóa'); }
    }
    
    function insertAtCursor(myField, myValue) {
        // ... existing legacy logic or modern range ...
        if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
        } else {
            myField.value += myValue;
        }
    }    
    
    async function updateStatus(id, status) {
        try {
            await axios.post(`/admin/tickets/${id}/status`, { status });
            toastr.success('Status updated');
            fetchTickets(false); // Update list
            if (activeTicketId === id) {
                 loadTicketContent(id, true); // Reload header/footer
            }
        } catch (e) {
            toastr.error('Failed to update status');
        }
    }
    
    async function deleteTicket(id) {
        if(!confirm('Are you sure you want to delete this ticket?')) return;
        try {
            await axios.post('{{ route("admin.tickets.reply", ["id" => ":id"]) }}'.replace(':id', id).replace('reply', 'delete'), { id }); 
            // NOTE: My route definition for delete was missing in web.php explicitly as 'delete'. 
            // But I have `admin.tickets.delete` logic in controller? 
            // Wait, I didn't add route in web.php for delete! 
            // I only added `delete` method in Controller.
            // I need to fix web.php or use a different endpoint?
            // Actually, I should add the route in the next step.
            
            toastr.success('Ticket deleted');
            activeTicketId = null;
            $('#chat-interface').addClass('d-none').removeClass('d-flex').hide();
            $('#chat-empty-state').addClass('d-flex').removeClass('d-none').show();
            $('#ticket-info').empty();
            fetchTickets(true);
        } catch (e) {
             // Fallback if route missing
             toastr.error('Delete not implemented or failed');
        }
    }
    
    async function saveAdminNote() {
        if (!activeTicketId) return;
        const note = $('#admin-note-input').val();
        try {
            await axios.post(`/admin/tickets/${activeTicketId}/note`, { note });
            toastr.success('Note saved');
        } catch (e) {
            toastr.error('Failed to save note');
        }
    }
    
    // --- Image Upload & Attachments ---
    let attachedImages = [];

    // Paste Event Listener
    $(document).on('paste', '#chat-composer', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        const files = [];
        for (let i = 0; i < items.length; i++) {
            if (items[i].kind === 'file' && items[i].type.indexOf('image/') !== -1) {
                files.push(items[i].getAsFile());
            }
        }
        if (files.length > 0) {
            e.preventDefault(); // Prevent pasting the image binary text
            uploadFiles(files);
        }
    });

    async function handleImageUpload(input) {
        if (!input.files || input.files.length === 0) return;
        await uploadFiles(input.files);
        input.value = ''; 
    }

    function compressImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1600;

                    if (width > height) {
                        if (width > maxDim) {
                            height *= maxDim / width;
                            width = maxDim;
                        }
                    } else {
                        if (height > maxDim) {
                            width *= maxDim / height;
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                    resolve(compressedBase64);
                };
                img.onerror = (err) => reject(err);
            };
            reader.onerror = (err) => reject(err);
        });
    }

    async function uploadFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.size > 15 * 1024 * 1024) {
                toastr.error(`File ${file.name} quá lớn (Max 15MB)`);
                continue;
            }

            try {
                const base64Data = await compressImage(file);
                attachedImages.push(base64Data);
            } catch (e) {
                console.error(e);
                toastr.error('Lỗi khi xử lý ảnh ' + file.name);
            }
        }
        renderPreviews();
    }

    function renderPreviews() {
        const $container = $('#composer-previews');
        $container.empty();
        
        attachedImages.forEach((url, index) => {
            $container.append(`
                <div class="preview-item">
                    <img src="${url}">
                    <button class="btn-remove" onclick="removeImage(${index})">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `);
        });
        
        // Adjust padding of textarea if images exist (optional UX)
        // For now, previews are ABOVE the textarea, so no padding change needed.
    }

    function removeImage(index) {
        attachedImages.splice(index, 1);
        renderPreviews();
    }
    
    // --- Sending Logic ---

    async function sendReplyCurrent() {
        const textarea = $('#chat-composer');
        let message = textarea.val(); // Don't trim yet, might be pure image
        
        // Append attached images as markdown
        if (attachedImages.length > 0) {
            const imageMarkdown = attachedImages.map(url => `![Image](${url})`).join(' ');
            if (message.trim()) {
                message += "\n" + imageMarkdown;
            } else {
                message = imageMarkdown;
            }
        }
        
        if (!activeTicketId) return;
        if (!message.trim()) return;

        // 5MB Limit Check
        if (message.length > 4800000) {
            toastr.error('Dung lượng tin nhắn quá lớn (Gần 5MB). Vui lòng gửi ít ảnh hơn.');
            return;
        }
        
        textarea.val('');
        textarea.css('height', '32px');
        attachedImages = []; // Clear attachments
        renderPreviews();

        try {
            await axios.post(`/admin/tickets/${activeTicketId}/reply`, {
                message: message
            });
            // Fetch immediately to show new message
            pollChatUpdates();
        } catch (e) {
            toastr.error('Failed to send');
            // If failed, we might want to restore content. 
            // Implementation detail: for now just error.
        }
    }
    
    // Remove old manual preview listener if exists
    // (Previous implementation used to parse textarea, we removed thatrequirement)

</script>

</script>
@endsection
