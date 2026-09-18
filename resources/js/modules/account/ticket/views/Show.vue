<script setup>
import { onMounted, onUnmounted, ref, nextTick, watch } from 'vue'
import moment from 'moment'
import { message } from 'ant-design-vue';

const props = defineProps({
  ticketCode: {
    type: String,
    required: true,
  },
})

const ticket = ref(null)
const messages = ref([])
const loading = ref(true)
const replyContent = ref('')
const sending = ref(false)
const conversationBox = ref(null)
const innerMessageContainer = ref(null)
const composerRef = ref(null)
const isReady = ref(false)

const adjustHeight = () => {
  nextTick(() => {
    const el = composerRef.value
    if (!el) return
    el.style.height = '32px' 
    const newHeight = Math.min(el.scrollHeight, 200)
    el.style.height = newHeight + 'px'
  })
}

const formatDate = (date) => {
  return moment(date).format('HH:mm DD/MM/YYYY')
}

const parseMessage = (content) => {
    if (!content) return '';
    // Escape HTML first to prevent XSS (basic)
    // In Vue {{ }} escapes automatically, but for v-html we must do it manually if we want to be safe-ish
    // However, since we are replacing text, let's simpler approach:
    // We assume content is text.
    let safe = content.replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
        
    // Allow Markdown Image - limit height to match Admin
    return safe.replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1" style="max-height: 200px; width: auto; max-width: 100%; border-radius: 8px; margin-top: 5px; display: block;" onload="window.dispatchEvent(new CustomEvent(\'ticket-image-loaded\'))">');
}

const fetchTicket = async (isPoll = false) => {
  try {
    const { data: result } = await window.axios.get(`/api/users/tickets/${props.ticketCode}`)
    ticket.value = result.data
    
    // Check if new messages arrived
    const oldLength = messages.value.length
    const newMessages = result.data.messages || []
    messages.value = newMessages
    
    // Only scroll if NOT polling (initial load) OR if new messages arrived
    if (!isPoll || newMessages.length > oldLength) {
        scrollToBottom()
    }
  } catch (error) {
    if (!isPoll) {
        console.error(error);
        message.error('Lỗi: ' + (error.response?.data?.message || error.message));
    }
  } finally {
    if (!isPoll) loading.value = false
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (conversationBox.value) {
      // Force instant scroll
      conversationBox.value.style.scrollBehavior = 'auto'
      conversationBox.value.scrollTop = conversationBox.value.scrollHeight
      
      // Double check after small delay
      setTimeout(() => {
          if (conversationBox.value) {
            conversationBox.value.scrollTop = conversationBox.value.scrollHeight
          }
      }, 100)
      
      setTimeout(() => {
          if (conversationBox.value) {
            conversationBox.value.scrollTop = conversationBox.value.scrollHeight
            isReady.value = true
            
            // Allow smooth scrolling again
            conversationBox.value.style.scrollBehavior = ''
            
            // Bind Scroll Listener NOW
            isPinnedToBottom.value = true
            // Remove old listener if any (though difficult with anon func, but we rely on simple add/remove or check flag)
            // Ideally we store the handler to remove it. But here we just add it once if we check a flag or just use onmessage
            
            // Cleanest way: assign to a var and remove if exists? 
            // Since this runs on every new message, we prefer NOT to add multiple listeners.
            // We can check if listener is attached? No.
            // Simplified: we only add it ONCE in onMounted but enable a FLAG?
            // Actually, the issue was the listener firing early.
            // Workaround: We use a named function for listener so we can remove/add it.
            
            conversationBox.value.onscroll = () => {
               const el = conversationBox.value
               if (!el) return
               const isAtBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 50
               isPinnedToBottom.value = isAtBottom
            }
          }
      }, 150)
    }
  })
}

const handleEnterKey = (e) => {
  if (e.shiftKey) return 
  
  e.preventDefault()
  if (sending.value) return
  sendReply()
}

const attachedImages = ref([])
    
const compressImage = (file) => {
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
                
                // Relaxed JPEG compression for 16MB relaxed DB limit
                const compressedBase64 = canvas.toDataURL('image/jpeg', 0.7);
                resolve(compressedBase64);
            };
            img.onerror = (err) => reject(err);
        };
        reader.onerror = (err) => reject(err);
    });
};
    
const uploadFiles = async (files) => {
    if (!files || files.length === 0) return;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        // Pre-compression limit check (for memory safety)
        if (file.size > 20 * 1024 * 1024) {
             message.error(`File ${file.name} quá lớn (Max 20MB)`);
             continue;
        }

        try {
            const base64Data = await compressImage(file);
            attachedImages.value.push(base64Data);
        } catch (e) {
            console.error(e);
            message.error('Lỗi khi xử lý ảnh ' + file.name);
        }
    }
}

const handleFileUpload = async (event) => {
    await uploadFiles(event.target.files);
    event.target.value = '';
    adjustHeight();
}

const handlePaste = async (event) => {
    const items = event.clipboardData?.items;
    if (!items) return;
    
    const files = [];
    for (let i = 0; i < items.length; i++) {
        if (items[i].kind === 'file' && items[i].type.indexOf('image/') !== -1) {
            files.push(items[i].getAsFile());
        }
    }
    
    if (files.length > 0) {
        event.preventDefault(); 
        await uploadFiles(files);
        adjustHeight();
    }
}

const removeImage = (index) => {
    attachedImages.value.splice(index, 1);
}

const sendReply = async () => {
  if (sending.value) return
  
  let content = replyContent.value;
  // Append images
  if (attachedImages.value.length > 0) {
      const imgMarkdown = attachedImages.value.map(url => `![Image](${url})`).join(' ');
      if (content.trim()) {
          content += "\n" + imgMarkdown;
      } else {
          content = imgMarkdown;
      }
  }

  if (!content.trim()) return

  // 5MB Limit Check (MySQL max_allowed_packet now 16MB)
  if (content.length > 4800000) {
      message.error('Dung lượng tin nhắn quá lớn (Gần 5MB). Vui lòng gửi ít ảnh hơn.');
      return;
  }

  const originalContent = replyContent.value
  replyContent.value = '' 
  const originalImages = [...attachedImages.value];
  attachedImages.value = []
  
  sending.value = true
  adjustHeight()
  try {
    await window.axios.post(`/api/users/tickets/${props.ticketCode}/reply`, {
      message: content,
    })
    
    if (ticket.value) ticket.value.status = 'open';
    fetchTicket(false)
  } catch (error) {
     replyContent.value = originalContent 
     attachedImages.value = originalImages 
     message.error('Gửi tin nhắn thất bại')
     adjustHeight()
  } finally {
    sending.value = false
  }
}

const interval = ref(null)
const resizeObserver = ref(null)
const isPinnedToBottom = ref(true)

onMounted(() => {
  fetchTicket()
  interval.value = setInterval(() => {
      fetchTicket(true) // Pass true to indicate it's a background poll
  }, 3000)
  
  // Setup ResizeObserver
  if (conversationBox.value) {
      resizeObserver.value = new ResizeObserver(() => {
          if (isPinnedToBottom.value && conversationBox.value) {
              conversationBox.value.style.scrollBehavior = 'auto'; // Force instant
              conversationBox.value.scrollTop = conversationBox.value.scrollHeight;
              conversationBox.value.style.scrollBehavior = '';
          }
      })
      // Inner content observed via watch
  }
  
  window.addEventListener('ticket-image-loaded', handleImageLoad)
})

watch(innerMessageContainer, (el) => {
    if (el && resizeObserver.value) {
        resizeObserver.value.disconnect();
        resizeObserver.value.observe(el);
    }
})

const handleImageLoad = () => {
    if (isPinnedToBottom.value && conversationBox.value) {
       conversationBox.value.style.scrollBehavior = 'auto';
       conversationBox.value.scrollTop = conversationBox.value.scrollHeight;
       conversationBox.value.style.scrollBehavior = ''; // Reset
    }
}

onUnmounted(() => {
  if (interval.value) clearInterval(interval.value)
  if (resizeObserver.value) resizeObserver.value.disconnect()
  window.removeEventListener('ticket-image-loaded', handleImageLoad)
  if (conversationBox.value) conversationBox.value.onscroll = null;
})

</script>

<template>
  <div class="h-full flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-220px)] lg:overflow-hidden">
    <!-- Chat Column -->
    <div class="lg:col-span-2 w-full shrink-0 lg:shrink flex flex-col h-[calc(100vh-140px)] lg:h-full lg:flex-1">
      <div class="card flex-1 flex flex-col h-full shadow-sm min-h-0">
        <header class="card-header noborder border-b px-4 py-3 shrink-0">
           <h4 class="card-title text-lg font-medium">Nội dung trao đổi</h4>
        </header>
        <div class="card-body p-0 flex flex-col flex-1 overflow-hidden min-h-0">
         <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-4 bg-white dark:bg-slate-900 message-scroll-area custom-scrollbar" ref="conversationBox">
            <div v-if="loading" class="text-center py-5">Đang tải...</div>
            <div v-else-if="messages.length === 0" class="text-center text-gray-500 py-5">Chưa có tin nhắn nào.</div>
            
            <div v-else ref="innerMessageContainer" :style="{ visibility: isReady ? 'visible' : 'hidden' }" :class="{ 'opacity-0': !isReady, 'opacity-100': isReady }" class="transition-opacity duration-300">
            <div v-for="(msg, index) in messages" :key="msg.id" class="mb-4 flex flex-col" :class="msg.sender_type === 'user' ? 'items-end' : 'items-start'">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs text-gray-500">{{ formatDate(msg.created_at) }}</span>
                    <span class="font-bold text-sm">
                        {{ msg.sender_type === 'user' ? 'Bạn' : (msg.sender?.username || 'Admin') }}
                    </span>
                </div>
                <!-- Message Bubble -->
                <div class="p-3 rounded-lg max-w-[85%] break-words whitespace-pre-wrap shadow-sm"
                     :class="msg.sender_type === 'user' ? 'bg-primary-500 text-white rounded-tr-none' : 'bg-gray-100 dark:bg-slate-800 border dark:border-slate-700 rounded-tl-none'"
                     v-html="parseMessage(msg.message)">
                </div>
            </div>
            </div>
        </div>
        
        <!-- Composer Area -->
        <div class="p-3 border-t bg-white dark:bg-slate-800" v-if="ticket && ticket.status !== 'closed'">
           <!-- Image Previews -->
           <div v-if="attachedImages.length > 0" class="flex gap-2 mb-2 overflow-x-auto pb-1 pt-3 pr-2">
                <div v-for="(url, idx) in attachedImages" :key="idx" class="relative shrink-0">
                    <img :src="url" class="h-14 w-auto min-w-[56px] rounded border shadow-sm object-contain bg-white">
                     <button class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 shadow-sm"
                            @click="removeImage(idx)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
           </div>
           
           <div class="relative flex items-end gap-2 bg-gray-50 dark:bg-slate-700 p-1.5 rounded-[24px] border border-gray-100 dark:border-slate-600">
                <input type="file" ref="fileInput" class="hidden" accept="image/*" multiple @change="handleFileUpload" style="display:none">
                <a-button 
                    type="text" 
                    shape="circle" 
                    class="!text-gray-500 hover:!bg-gray-200 dark:hover:!bg-slate-600 shrink-0" 
                    style="width: 32px; height: 32px;"
                    @click="$refs.fileInput.click()"
                >
                    <i class="fas fa-image text-lg"></i>
                </a-button>

                <textarea 
                    ref="composerRef"
                    v-model="replyContent" 
                    class="flex-1 bg-transparent border-none outline-none focus:ring-0 focus:outline-none px-3 py-1.5 text-sm resize-none"
                    placeholder="Type a message..."
                    @input="adjustHeight"
                    @keydown.enter="handleEnterKey"
                    @paste="handlePaste"
                    style="height: 32px; min-height: 32px; max-height: 200px; line-height: 20px; overflow-y: auto; border: none !important; outline: none !important; box-shadow: none !important;"
                ></textarea>

                <a-button 
                    ref="sendBtn"
                    type="primary" 
                    shape="circle"
                    class="btn-send-custom !w-[34px] !h-[34px] flex items-center justify-center shrink-0 !bg-primary-500 !border-primary-500 !shadow-none" 
                    :disabled="sending || (!replyContent.trim() && attachedImages.length === 0)" 
                    :loading="sending"
                    @click="sendReply"
                    style="box-shadow: none !important; outline: none !important; border: none !important;"
                >
                    <i class="fas fa-paper-plane text-[10px]" v-if="!sending"></i>
                </a-button>
           </div>
        </div>
         <div v-else-if="ticket" class="p-4 bg-red-50 text-red-600 text-center border-t">
             Ticket này đã đóng.
         </div>
        </div>
      </div>
    </div>

    <!-- Info Column -->
    <div class="lg:w-80 shrink-0 lg:overflow-y-auto custom-scrollbar h-auto lg:h-full pb-4">
       <a-card title="Thông tin Ticket" class="shadow-sm">
           <div v-if="ticket" class="space-y-4">
               <div>
                   <div class="text-gray-500 text-xs uppercase font-bold mb-1">Mã Ticket</div>
                   <div class="font-mono bg-gray-100 px-2 py-1 rounded inline-block">{{ ticket.code || '#' + ticket.id }}</div>
               </div>
               <div>
                   <div class="text-gray-500 text-xs uppercase font-bold mb-1">Tiêu đề</div>
                   <div class="font-bold leading-tight" :title="ticket.title">
                       {{ ticket.title.length > 50 ? ticket.title.substring(0, 50) + '...' : ticket.title }}
                   </div>
               </div>
               <div>
                   <div class="text-gray-500 text-xs uppercase font-bold mb-1">Chủ đề</div>
                   <div><a-tag color="blue">{{ ticket.category }}</a-tag></div>
               </div>
               <div>
                   <div class="text-gray-500 text-xs uppercase font-bold mb-1">Trạng thái</div>
                   <div>
                       <a-tag v-if="ticket.status === 'open'" color="green">Đang mở</a-tag>
                       <a-tag v-else-if="ticket.status === 'pending'" color="orange">Đang chờ</a-tag>
                       <a-tag v-else-if="ticket.status === 'closed'" color="red">Đã đóng</a-tag>
                   </div>
               </div>
               <div>
                   <div class="text-gray-500 text-xs uppercase font-bold mb-1">Ngày tạo</div>
                   <div class="text-sm">{{ formatDate(ticket.created_at) }}</div>
               </div>
           </div>
           <div v-else class="text-center py-5">Đang tải...</div>
       </a-card>
    </div>
  </div>
</template>

<style scoped>
.btn-send-custom,
.btn-send-custom:focus,
.btn-send-custom:active,
.btn-send-custom:visited {
    box-shadow: none !important;
    outline: none !important;
    -webkit-box-shadow: none !important;
}

.btn-send-custom:hover {
    background-color: #4338ca !important; /* Indigo 700 fallback */
    border-color: #4338ca !important;
    opacity: 0.9;
}

:deep(.ant-btn:focus) {
    box-shadow: none !important;
    outline: none !important;
}
</style>
