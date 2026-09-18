<script setup>
import { computed, ref, onMounted } from 'vue';
import moment from 'moment';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (date) => {
    if (!date) return '';
    return moment(date).format('HH:mm:ss DD/MM/YYYY');
};

const copyToClipboard = (text) => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        // Simple toast or fallback if available, or just console
        // Assuming global Swal is available or similar
        if (window.Swal) {
             window.Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                icon: 'success',
                title: 'Đã sao chép'
            });
        }
    });
};

const getStatusColor = (status) => {
    const s = String(status).toLowerCase();
    if (['completed', 'success', '1'].includes(s)) return 'text-green-500';
    if (['cancelled', 'canceled', 'error', '2'].includes(s)) return 'text-red-500';
    if (['processing'].includes(s)) return 'text-blue-500';
    return 'text-yellow-500'; // Pending
};

const getStatusText = (status) => {
     const s = String(status).toLowerCase();
    if (['completed', 'success', '1'].includes(s)) return 'Hoàn thành';
    if (['cancelled', 'canceled', 'error', '2'].includes(s)) return 'Đã hủy / Hoàn tiền';
    if (['processing'].includes(s)) return 'Đang xử lý';
    if (['pending'].includes(s)) return 'Chờ xử lý';
    return status;
};

// Stepper Logic
const steps = [
    { title: 'Thanh toán', desc: 'Đơn hàng đã được thanh toán thành công', icon: 'fas fa-file-invoice-dollar' },
    { title: 'Đang xử lý', desc: 'Hệ thống đang xử lý đơn hàng của bạn', icon: 'fas fa-shield-alt' },
    { title: 'Hoàn thành', desc: 'Đơn hàng đã hoàn tất', icon: 'fas fa-check-circle' }
];

const currentStepIndex = computed(() => {
    const s = String(props.item.status).toLowerCase();
    if (['completed', 'success', '1'].includes(s)) return 3; // All done
    if (['cancelled', 'canceled', 'error', '2'].includes(s)) return 0; // Error state
    if (['processing'].includes(s)) return 2; // Processing
    return 1; // Paid/Pending
});

</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 font-sans text-gray-600 dark:text-gray-300">
        <!-- Left Column: Transaction Info -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
            <div class="mb-5">
                <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase">
                    Thông tin giao dịch - <span class="text-red-500">{{ item.code }}</span>
                </h3>
            </div>
            
            <div class="border border-gray-200 dark:border-[#2d2d3a] rounded-md overflow-hidden">
                <table class="w-full text-sm">
                    <tr class="border-b border-gray-100 dark:border-[#2d2f36]">
                        <th class="w-1/3 text-left p-3 font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-white/5 border-r border-gray-100 dark:border-[#2d2f36]">Sản phẩm</th>
                        <td class="p-3 font-bold text-gray-900 dark:text-white">{{ item.product ? item.product.name : 'Unknown' }}</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-[#2d2f36]">
                        <th class="w-1/3 text-left p-3 font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-white/5 border-r border-gray-100 dark:border-[#2d2f36]">Loại</th>
                        <td class="p-3">
                             <span class="px-2.5 py-0.5 rounded text-[11px] font-bold border bg-gray-50 dark:bg-[#14141f]" :class="getStatusColor(item.status)">
                                 {{ getStatusText(item.status) }}
                             </span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-[#2d2f36]">
                        <th class="w-1/3 text-left p-3 font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-white/5 border-r border-gray-100 dark:border-[#2d2f36]">Ngày mua</th>
                        <td class="p-3 font-medium text-gray-900 dark:text-white">{{ formatDate(item.created_at) }}</td>
                    </tr>
                    <tr class="border-b border-gray-100 dark:border-[#2d2f36]">
                        <th class="w-1/3 text-left p-3 font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-white/5 border-r border-gray-100 dark:border-[#2d2f36]">Ngày cập nhật</th>
                        <td class="p-3 font-medium text-gray-900 dark:text-white">{{ formatDate(item.updated_at) }}</td>
                    </tr>
                    <tr>
                        <th class="w-1/3 text-left p-3 font-medium text-gray-500 dark:text-gray-400 bg-gray-50/50 dark:bg-white/5 border-r border-gray-100 dark:border-[#2d2f36]">Thanh toán</th>
                        <td class="p-3 font-bold text-emerald-600 dark:text-emerald-400 text-base">{{ formatCurrency(item.payment || item.price) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Right Column: Order Info -->
        <div class="bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
             <div class="mb-5 pb-3 border-b border-gray-200 dark:border-[#2d2d3a]">
                <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase">
                    Thông tin đơn hàng - <span class="text-red-500">{{ item.code }}</span>
                </h3>
            </div>

            <div class="space-y-4">
                <!-- Helper to check if it's Robux order to show custom fields -->
                
                <!-- Link Gamepass (input_user/tk) -->
                <div>
                    <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold mb-2">Link Gamepass</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" 
                               :value="item.input_user || (item.params ? item.params.tk : '')" 
                               readonly
                               class="bg-gray-50 dark:bg-[#0f0f17] border border-gray-200 dark:border-[#2d2d3a] text-gray-700 dark:text-gray-300 text-sm rounded-lg block w-full pl-10 p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                        <button @click="copyToClipboard(item.input_user || (item.params ? item.params.tk : ''))"
                                class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors border-l border-gray-200 dark:border-[#2d2d3a]">
                             <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- Robux Quantity -->
                <div v-if="item.name">
                    <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold mb-2 text-yellow-600 dark:text-yellow-500">Số lượng Robux</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-yellow-600 dark:text-yellow-500 font-bold">#</span>
                        </div>
                        <input type="text" 
                               :value="item.name" 
                               readonly
                               class="bg-gray-50 dark:bg-[#0f0f17] border border-gray-200 dark:border-[#2d2d3a] text-gray-700 dark:text-gray-300 text-sm rounded-lg block w-full pl-10 p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                         <button @click="copyToClipboard(item.name)"
                                class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors border-l border-gray-200 dark:border-[#2d2d3a]">
                             <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- Note -->
                <div>
                     <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold mb-2">Ghi chú</label>
                     <textarea class="bg-gray-50 dark:bg-[#0f0f17] border border-gray-200 dark:border-[#2d2d3a] text-gray-700 dark:text-gray-500 text-sm rounded-lg block w-full p-2.5 h-20 resize-none focus:outline-none" 
                               readonly>{{ item.client_note || '(Trống)' }}</textarea>
                </div>

                 <!-- Admin Note/Feedback -->
                <div>
                     <label class="block text-gray-500 dark:text-gray-400 text-xs font-bold mb-2">Admin cập nhật</label>
                     <div class="bg-gray-50 dark:bg-[#0f0f17] border border-gray-200 dark:border-[#2d2d3a] text-gray-700 dark:text-gray-500 text-sm rounded-lg block w-full p-2.5 h-20 overflow-y-auto">
                         {{ item.admin_note || item.feedback || 'Chưa có phản hồi' }}
                     </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom: Status Stepper -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1e1e2d] border border-gray-200 dark:border-[#2d2d3a] rounded-lg p-6 shadow-sm">
             <div class="mb-6 pb-4 border-b border-gray-100 dark:border-[#2d2f36]">
                <h3 class="text-base font-bold text-gray-900 dark:text-white uppercase">
                    Trạng thái đơn hàng
                </h3>
            </div>
            
            <div class="flex flex-col lg:flex-row lg:items-center relative">
                <div v-for="(step, index) in steps" :key="index" class="flex items-start lg:items-center group relative lg:flex-1">
                    <!-- Vertical Line (Mobile) -->
                    <div v-if="index !== steps.length - 1" class="absolute left-4 top-8 bottom-0 w-px bg-gray-200 dark:bg-[#2d2f36] lg:hidden"></div>

                    <!-- Icon Circle -->
                    <div class="flex-shrink-0 w-8 h-8 rounded-md flex items-center justify-center border-2 transition-colors duration-300 relative z-10"
                         :class="index < currentStepIndex ? 'bg-blue-600/10 border-blue-600 text-blue-600' : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-[#2d2f36] text-gray-400 dark:text-gray-600'">
                         <i :class="step.icon" class="text-sm"></i>
                    </div>
                    
                    <!-- Content -->
                    <div class="ml-4 lg:ml-3 pb-8 lg:pb-0">
                        <h4 class="text-sm font-bold" :class="index < currentStepIndex ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'">{{ step.title }}</h4>
                        <p class="text-xs mt-1" :class="index < currentStepIndex ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600'">{{ step.desc }}</p>
                    </div>

                    <!-- Horizontal Line (PC) -->
                    <div v-if="index !== steps.length - 1" class="hidden lg:block flex-grow h-px bg-gray-200 dark:bg-[#2d2f36] mx-4"></div>
                </div>
            </div>
        </div>
    </div>
</template>
