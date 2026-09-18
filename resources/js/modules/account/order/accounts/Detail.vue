<script setup>
import { computed, ref, onMounted } from 'vue';
import moment from 'moment';

const props = defineProps({
    account: {
        type: Object,
        required: true
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (date) => {
    if (!date) return '';
    return moment(date).format('HH:mm:ss - DD/MM/YYYY');
};

const copyToClipboard = (text) => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
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

const togglePasswordVisibility = () => {
    const passwordInput = document.getElementById('password_field_vue');
    const eyeIcon = document.getElementById('toggle_eye_vue');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
         eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
};

const getStatusColor = (status) => {
    // Check refunded by code first
    if (String(props.account.buyer_code).trim().toUpperCase() === 'REFUNDED') return 'text-red-500';

    const s = String(status).toLowerCase();
    if (['completed', 'success', '1'].includes(s)) return 'text-green-500';
    if (['cancelled', 'canceled', 'error', '2'].includes(s)) return 'text-red-500';
    if (['processing'].includes(s)) return 'text-blue-500';
    return 'text-yellow-500'; // Pending
};

// Normalization
const accountData = computed(() => {
    const raw = props.account;
    const isV2 = !!raw.parent; // Simple check for V2 structure
    
    return {
        ...raw,
        group: raw.group || (raw.parent ? raw.parent.group : null),
        name: raw.name || (raw.parent ? raw.parent.name : null) || (raw.group ? raw.group.name : (raw.parent?.group?.name)) || 'Unknown'
    };
});

const isRefunded = computed(() => {
     return String(accountData.value.buyer_code).trim().toUpperCase() === 'REFUNDED';
});

// Warranty Logic
const warrantyInfo = computed(() => {
    const group = accountData.value.group;
    if (!group || !group.warranty_hours || group.warranty_hours <= 0) {
        return null;
    }
    
    const purchaseDate = moment(accountData.value.buyer_date);
    if (!purchaseDate.isValid()) return null;

    const expirationDate = purchaseDate.clone().add(group.warranty_hours, 'hours');
    
    return {
        text: expirationDate.format('HH:mm:ss - DD/MM/YYYY'),
        isExpired: moment().isAfter(expirationDate)
    };
});


// Stepper Logic
const steps = [
    { title: 'Thanh toán', desc: 'Đơn hàng đã được thanh toán thành công', icon: 'fas fa-file-invoice-dollar' },
    { title: 'Đang xử lý', desc: 'Hệ thống đang xử lý đơn hàng của bạn', icon: 'fas fa-shield-alt' },
    { title: 'Hoàn thành', desc: 'Đơn hàng đã hoàn tất', icon: 'fas fa-check-circle' }
];

const currentStepIndex = computed(() => {
    if (isRefunded.value) return 0; // Treat as error equivalent step or just keep 0

    const s = String(accountData.value.status).toLowerCase();
    // Assuming sold = completed usually
    if (['completed', 'success', '1'].includes(s) || accountData.value.buyer_name) return 3; 
    if (['cancelled', 'canceled', 'error', '2'].includes(s)) return 0; 
    if (['processing'].includes(s)) return 2; 
    return 1; 
});

// Account parsing logic
const parsedAccount = computed(() => {
    let user = accountData.value.username;
    let pass = accountData.value.password || '';
    let extra = accountData.value.extra_data || '';

    if (!pass && user && user.includes(':')) {
        const parts = user.split(':');
        user = parts[0] || '';
        pass = parts[1] || '';
        if (parts.length > 2) {
             const rest = parts.slice(2).join(':');
             extra = extra ? rest + '|' + extra : rest;
        }
    }
    return { user, pass, extra };
});

</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 font-sans text-gray-300">
        <!-- Left Column: Transaction Info -->
        <div class="bg-[#1e1e2d] border border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
            <div class="mb-5 pb-3 border-b border-[#2d2d3a] flex justify-between items-start">
                <h3 class="text-base font-bold text-white uppercase">
                    Thông tin giao dịch - <span class="text-red-500">{{ accountData.buyer_code }}</span>
                </h3>
                 <span v-if="isRefunded" class="px-2 py-1 rounded text-xs font-bold bg-[#3a1e1e] border border-[#502d2d] text-red-500">
                     Đã hoàn tiền
                 </span>
            </div>
            
            <div class="space-y-4">
                <!-- Product Name -->
                <div>
                     <span class="text-gray-200 font-bold block mb-1">Sản phẩm: <span class="text-white">{{ accountData.name }}</span></span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <!-- Purchase Date -->
                    <div>
                        <span class="text-gray-200 font-bold block mb-1">Ngày mua: <span class="text-white font-normal">{{ formatDate(accountData.buyer_date) }}</span></span>
                    </div>
                    
                    <!-- Warranty -->
                    <div v-if="warrantyInfo">
                         <span class="text-gray-200 font-bold block mb-1">
                             Bảo hành: 
                             <span class="font-normal" :class="warrantyInfo.isExpired ? 'text-red-500' : 'text-green-500'">
                                 <i class="fas fa-circle text-[8px] align-middle mr-1"></i> {{ warrantyInfo.text }}
                             </span>
                         </span>
                    </div>
                </div>

                 <!-- Price -->
                <div>
                    <span class="text-gray-200 font-bold block mb-1">Thanh toán: <span class="text-yellow-500">{{ formatCurrency(accountData.buyer_paym || accountData.payment) }}</span></span>
                </div>
            </div>
        </div>

        <!-- Right Column: Account Info -->
        <div class="bg-[#1e1e2d] border border-[#2d2d3a] rounded-lg p-5 shadow-sm h-full">
             <div class="mb-5 pb-3 border-b border-[#2d2d3a] flex justify-between items-start">
                <h3 class="text-base font-bold text-white uppercase">
                    Thông tin đơn hàng - <span class="text-red-500">{{ accountData.code }}</span>
                </h3>
            </div>

            <div class="space-y-4">
                <!-- Username -->
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-2">Tài khoản</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-500"></i>
                        </div>
                        <input type="text" 
                               :value="parsedAccount.user" 
                               readonly
                               class="bg-[#0f0f17] border border-[#2d2d3a] text-gray-300 text-sm rounded-lg block w-full pl-10 p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                        <button @click="copyToClipboard(parsedAccount.user)"
                                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-white transition-colors border-l border-[#2d2d3a]">
                             <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                 <!-- Password -->
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-2">Mật khẩu</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-500"></i>
                        </div>
                        <input type="password" 
                               id="password_field_vue"
                               :value="parsedAccount.pass" 
                               readonly
                               class="bg-[#0f0f17] border border-[#2d2d3a] text-gray-300 text-sm rounded-lg block w-full pl-10 pr-20 p-2.5 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                         <button @click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-10 px-3 text-gray-500 hover:text-white transition-colors">
                             <i id="toggle_eye_vue" class="fas fa-eye-slash"></i>
                        </button>
                        <button @click="copyToClipboard(parsedAccount.pass)"
                                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-white transition-colors border-l border-[#2d2d3a]">
                             <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- Extra / Cookie -->
                <div>
                     <label class="block text-gray-400 text-xs font-bold mb-2">Ghi chú / Extra</label>
                     <div class="relative">
                         <textarea class="bg-[#0f0f17] border border-[#2d2d3a] text-gray-500 text-sm rounded-lg block w-full p-2.5 h-20 resize-none focus:outline-none" 
                               readonly>{{ parsedAccount.extra || '(Trống)' }}</textarea>
                          <button @click="copyToClipboard(parsedAccount.extra)"
                                class="absolute top-2 right-2 px-2 py-1 text-gray-500 hover:text-white transition-colors border border-[#2d2d3a] rounded bg-[#1e1e2d]">
                             <i class="fas fa-copy"></i>
                        </button>
                     </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom: Status Stepper -->
        <div class="lg:col-span-2 bg-[#1e1e2d] border border-[#2d2d3a] rounded-lg p-5 shadow-sm">
             <div class="mb-5 pb-3 border-b border-[#2d2d3a]">
                <h3 class="text-base font-bold text-white">
                    Trạng thái đơn hàng
                </h3>
            </div>
            
            <div class="relative pl-4 space-y-8">
                <!-- Vertical Line -->
                <div class="absolute left-[27px] top-2 bottom-2 w-0.5 bg-[#2d2d3a] -z-0"></div>

                <div v-for="(step, index) in steps" :key="index" class="relative z-10 flex items-start gap-4">
                    <!-- Icon Circle -->
                    <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors duration-300"
                         :class="index < currentStepIndex ? 'bg-[#1e1e2d] border-blue-500 text-blue-500' : 'bg-[#1e1e2d] border-gray-600 text-gray-600'">
                         <i :class="step.icon" class="text-sm"></i>
                    </div>
                    
                    <!-- Content -->
                    <div class="pt-1">
                        <h4 class="text-sm font-bold" :class="index < currentStepIndex ? 'text-white' : 'text-gray-500'">{{ step.title }}</h4>
                        <p class="text-xs mt-1" :class="index < currentStepIndex ? 'text-gray-400' : 'text-gray-600'">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
