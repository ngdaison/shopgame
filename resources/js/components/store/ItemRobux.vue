<template>
    <div class="font-sans text-slate-800 dark:text-slate-200 mt-[-25px] lg:mt-[-25px] mb-[-25px] lg:mb-[-25px]">
        <div class="grid grid-cols-12 gap-3 lg:gap-4">
            <!-- Step 0: Description Use 'order' ref if needed? user said "top". -->
             <div class="col-span-12 mb-3" v-if="group.descr">
                <div class="bg-white dark:bg-[#1e1e2d] rounded-lg border border-gray-300 dark:border-[#2d2d3a] -mx-2 sm:mx-0">
                    <div class="p-5 ck-content" v-html="group.descr"></div>
                </div>
            </div>

            <!-- Step 1: Information -->
            <div class="col-span-12 lg:col-span-8 order-1">
                <div class="bg-white dark:bg-[#1e1e2d] rounded-lg border border-gray-300 dark:border-[#2d2d3a] -mx-2 sm:mx-0">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-[#2d2d3a] bg-gray-50 dark:bg-[#2b2b40] rounded-t-lg">
                        <h2 class="font-bold text-base text-gray-800 dark:text-white">Điền thông tin</h2>
                    </div>
                    
                    <div class="p-5 space-y-5">
                        
                        <!-- Link Input -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                {{ robuxType === 'genuine' ? 'Tên tài khoản' : 'Pass ID / Link Game Pass' }}
                            </label>
                            <a-input 
                                v-model:value="gamepassLink"
                                :placeholder="robuxType === 'genuine' ? 'Nhập tên tài khoản' : 'Nhập Link hoặc Pass ID'"
                                size="middle"
                                class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                            />
                        </div>

                        <!-- Password Input -->


                        <!-- Tridirectional Inputs -->
                        <div class="space-y-4">
                            <!-- Case: No Tax -> Robux | Price -->
                            <div v-if="taxLabel === 0" class="grid grid-cols-2 gap-4">
                                <!-- Robux Amount -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Số lượng Robux</label>
                                    <a-input
                                        v-model:value="inputRobuxAmount"
                                        @input="onAmountChange"
                                        placeholder="0"
                                        size="middle"
                                        :disabled="robuxType === 'genuine'"
                                        class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                    >
                                        <template #prefix>
                                            <span class="text-gray-500 dark:text-gray-400 font-bold">R$</span>
                                        </template>
                                    </a-input>
                                </div>
                                <!-- Price -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Giá tiền</label>
                                    <a-input
                                        v-model:value="inputPrice"
                                        @input="onPriceChange"
                                        placeholder="0"
                                        size="middle"
                                        :disabled="robuxType === 'genuine'"
                                        class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                    >
                                        <template #prefix>
                                            <span class="text-gray-500 dark:text-gray-400 font-bold">đ</span>
                                        </template>
                                    </a-input>
                                </div>
                            </div>

                            <!-- Case: With Tax -> Robux (Row 1), Sau Thue | Price (Row 2) -->
                            <template v-else>
                                <!-- Robux Amount -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Số lượng Robux</label>
                                    <a-input
                                        v-model:value="inputRobuxAmount"
                                        @input="onAmountChange"
                                        placeholder="0"
                                        size="middle"
                                        :disabled="robuxType === 'genuine'"
                                        class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                    >
                                        <template #prefix>
                                            <span class="text-gray-500 dark:text-gray-400 font-bold">R$</span>
                                        </template>
                                    </a-input>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- After Tax -->
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Sau thuế ({{ taxLabel }}%)</label>
                                        <a-input
                                            v-model:value="inputAfterTaxAmount"
                                            @input="onAfterTaxChange"
                                            placeholder="0"
                                            size="middle"
                                            :disabled="robuxType === 'genuine'"
                                            class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                        >
                                            <template #prefix>
                                                <span class="text-gray-500 dark:text-gray-400 font-bold">R$</span>
                                            </template>
                                        </a-input>
                                    </div>
                                    <!-- Price -->
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Giá tiền</label>
                                        <a-input
                                            v-model:value="inputPrice"
                                            @input="onPriceChange"
                                            placeholder="0"
                                            size="middle"
                                            :disabled="robuxType === 'genuine'"
                                            class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                        >
                                            <template #prefix>
                                                <span class="text-gray-500 dark:text-gray-400 font-bold">đ</span>
                                            </template>
                                        </a-input>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Mobile Toggle Button -->
                        <div class="lg:hidden flex justify-end pt-2 -mb-1">
                            <a-button type="primary" size="middle" @click="handleOpenMobileSummary" class="!font-bold flex items-center justify-center gap-2 shadow-sm rounded-lg">
                                <i class="fas fa-shopping-cart"></i>
                                Đặt đơn hàng
                            </a-button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary (Step 2 - previously 3) -->
            <div class="col-span-12 lg:col-span-4 order-2 lg:row-span-2 hidden lg:block">
                  <div class="bg-white dark:bg-[#1e1e2d] rounded-lg border border-gray-300 dark:border-[#2d2d3a] overflow-y-auto sticky top-28 z-10 max-h-[calc(100vh-8rem)]" style="transform: translate3d(0,0,0); will-change: transform;">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-[#2d2d3a] bg-gray-50 dark:bg-[#2b2b40] rounded-t-lg">
                        <h2 class="font-bold text-base text-gray-800 dark:text-white">Thông tin đơn hàng</h2>
                    </div>
                    
                    <div class="p-5">
                            <!-- Product Info (No Box) -->
                            <div class="flex items-center gap-4 mb-4">
                                 <!-- Robux Icon -->
                                 <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center">
                                    <img :src="'/images/logo/Robux.png'" alt="Robux" class="w-14 h-14 object-contain" />
                                 </div>
                                 <div class="flex-1">
                                      <h3 class="font-bold text-gray-800 dark:text-white text-base mb-0.5">
                                          {{ summaryText }}
                                      </h3>
                                      <!-- Detail Calculation -->
                                      <p v-if="robuxType !== 'genuine'" class="text-sm text-gray-400 font-medium">
                                         {{ formatNumber(currentRate) }} x {{ formatNumber(inputRobuxAmount) }} = {{ formatCurrency(totalOriginalPrice) }}
                                      </p>
                                 </div>
                            </div>
                            
                            <div class="border-t border-gray-200 dark:border-[#2d2d3a] my-2"></div>

                            <!-- Total -->
                            <div class="mb-4">
                                <span class="block text-gray-600 dark:text-gray-400 text-xs font-bold mb-1">Tổng Thanh Toán</span>
                                <div class="text-2xl font-bold text-red-600">
                                     {{ formatCurrency(finalPrice) }}
                                     <span v-if="couponApplied" class="text-xs text-gray-400 line-through ml-1 font-normal">
                                         {{ formatCurrency(totalOriginalPrice) }}
                                     </span>
                                </div>
                                 <div v-if="couponApplied" class="text-green-600 text-xs font-medium mt-0.5">Đã giảm: -{{ formatCurrency(discountAmount) }}</div>
                            </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Mã giảm giá</label>
                            <div class="flex gap-2">
                                <a-input v-model:value="formBuy.coupon_code" 
                                       :disabled="couponApplied" 
                                       @keyup.enter="!couponApplied && checkCoupon()"
                                       placeholder="Mã giảm giá..." 
                                       size="middle"
                                       class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                                />
                                <a-button v-if="!couponApplied" 
                                        @click="checkCoupon" 
                                        type="primary"
                                        :loading="checkingCoupon" >
                                    Áp dụng
                                </a-button>
                                <a-button v-else 
                                        @click="cancelCoupon" 
                                        danger>
                                    Hủy
                                </a-button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <a-checkbox v-model:checked="confirmed">
                                <span v-if="robuxType === 'genuine'" class="text-sm font-bold text-red-600 leading-snug pl-1">
                                    Tôi cung cấp đúng theo như yêu cầu
                                </span>
                                <span v-else class="text-sm font-medium text-gray-600 dark:text-gray-400 leading-snug pl-1">
                                    Tôi đã tạo đúng <span class="text-red-600 font-bold">Game Pass {{ formatNumber(inputRobuxAmount) }} Robux</span>
                                </span>
                            </a-checkbox>
                        </div>

                        <a-button type="primary" 
                                  size="middle"
                                  block
                                  :disabled="!confirmed"
                                  @click="buyNow"
                                  class="!h-auto !font-bold shadow-sm">
                            Thanh toán
                        </a-button>
                    </div>
                </div>
            </div>

             <div class="col-span-12 lg:col-span-8 order-3" v-if="localPrizes.length > 0">
                 <div class="bg-white dark:bg-[#1e1e2d] rounded-lg border border-gray-300 dark:border-[#2d2d3a] -mx-2 sm:mx-0">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-[#2d2d3a] bg-gray-50 dark:bg-[#2b2b40] rounded-t-lg">
                        <h2 class="font-bold text-base text-gray-800 dark:text-white">Chọn gói sản phẩm</h2>
                    </div>
                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <div v-for="(prize, index) in localPrizes" :key="index"
                                 @click="selectPackage(prize)"
                                 class="relative rounded-xl border border-solid p-4 cursor-pointer transition-all duration-200 min-h-[90px] flex flex-col justify-center !shadow-none !ring-0 !outline-none hover:shadow-none focus:ring-0 active:ring-0 select-none"
                                 :class="isPackageSelected(prize) 
                                          ? 'border-blue-600 bg-white dark:bg-[#1e1e2d] z-10' 
                                          : 'border-gray-200 dark:border-[#2d2d3a] bg-white dark:bg-[#1e1e2d] hover:border-gray-300 dark:hover:border-gray-400'">
                                
                                <!-- Name -->
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-1 line-clamp-1 pr-10">{{ prize.name }}</h3>
                                
                                <!-- Price + Icon Row -->
                                <div class="flex items-center justify-between mt-auto">
                                    <p class="text-gray-500 dark:text-gray-400 font-bold text-xs">{{ formatNumber(prize.amount) }} đ</p>
                                    
                                    <div class="relative">
                                        <div v-if="isPackageSelected(prize)" 
                                              class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-sm">
                                            <i class="fas fa-check text-xs"></i>
                                        </div>
                                        <div v-else
                                              class="w-8 h-8 bg-[#EDF2F7] dark:bg-[#2d2d3a] rounded-full flex items-center justify-center text-[#A0AEC0] dark:text-gray-500">
                                            <i class="fas fa-plus text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

        </div>

        <!-- Mobile Order Summary Modal -->
        <a-modal
            v-model:visible="showMobileSummary"
            :footer="null"
            :closable="false"
            width="min(95%, 450px)"
            :centered="true"
            :body-style="{ padding: '0', background: 'transparent' }"
        >
            <div class="bg-white dark:bg-[#1e1e2d] rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="px-2 py-1 border-b border-gray-200 dark:border-[#2d2d3a] bg-gray-50 dark:bg-[#2b2b40] flex justify-between items-center">
                    <h2 class="font-bold text-base text-gray-800 dark:text-white">Thông tin đơn hàng</h2>
                    <button @click="showMobileSummary = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-0">
                    <!-- Product Info -->
                    <div class="flex items-center gap-4 mb-4">
                         <!-- Robux Icon -->
                         <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center">
                            <img :src="'/images/logo/Robux.png'" alt="Robux" class="w-14 h-14 object-contain" />
                         </div>
                         <div class="flex-1">
                              <h3 class="font-bold text-gray-800 dark:text-white text-base mb-0.5">
                                  {{ summaryText }}
                              </h3>
                              <!-- Detail Calculation -->
                              <p v-if="robuxType !== 'genuine'" class="text-sm text-gray-400 font-medium">
                                 {{ formatNumber(currentRate) }} x {{ formatNumber(inputRobuxAmount) }} = {{ formatCurrency(totalOriginalPrice) }}
                              </p>
                         </div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-[#2d2d3a] my-2"></div>

                    <!-- Total -->
                    <div>
                        <span class="block text-gray-600 dark:text-gray-400 text-xs font-bold mb-1">Tổng Thanh Toán</span>
                        <div class="text-2xl font-bold text-red-600">
                             {{ formatCurrency(finalPrice) }}
                             <span v-if="couponApplied" class="text-xs text-gray-400 line-through ml-1 font-normal">
                                 {{ formatCurrency(totalOriginalPrice) }}
                             </span>
                        </div>
                         <div v-if="couponApplied" class="text-green-600 text-xs font-medium mt-0.5">Đã giảm: -{{ formatCurrency(discountAmount) }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Mã giảm giá</label>
                        <div class="flex gap-2">
                            <a-input v-model:value="formBuy.coupon_code" 
                                   :disabled="couponApplied" 
                                   @keyup.enter="!couponApplied && checkCoupon()"
                                   placeholder="Mã giảm giá..." 
                                   size="middle"
                                   class="dark:bg-[#2b2b40] dark:border-[#3f3f50] dark:text-white"
                            />
                            <a-button v-if="!couponApplied" 
                                    @click="checkCoupon" 
                                    type="primary"
                                    class="!bg-black !border-black hover:!bg-gray-800 hover:!border-gray-800 !text-white !font-bold"
                                    :loading="checkingCoupon" >
                                Áp dụng
                            </a-button>
                            <a-button v-else 
                                    @click="cancelCoupon" 
                                    danger>
                                Hủy
                            </a-button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <a-checkbox v-model:checked="confirmed">
                            <span v-if="robuxType === 'genuine'" class="text-sm font-bold text-red-600 leading-snug pl-1">
                                Tôi cung cấp đúng theo như yêu cầu
                            </span>
                            <span v-else class="text-sm font-medium text-gray-600 dark:text-gray-400 leading-snug pl-1">
                                Tôi đã tạo đúng <span class="text-red-600 font-bold">Game Pass {{ formatNumber(inputRobuxAmount) }} Robux</span>
                            </span>
                        </a-checkbox>
                    </div>

                    <div class="flex justify-end items-center gap-3 mt-3 border-t border-gray-100 dark:border-[#2d2d3a] pt-3">
                         <a-button 
                                  @click="showMobileSummary = false">
                            Huỷ
                        </a-button>
                        <a-button type="primary" 
                                  :disabled="!confirmed"
                                  @click="buyNow"
                                  class="!bg-black !border-black hover:!bg-gray-800 hover:!border-gray-800 !font-bold">
                            Thanh toán
                        </a-button>
                    </div>
                </div>
            </div>
        </a-modal>
    </div>
</template>

<script>
    import { ref, computed, watch, onMounted } from 'vue';
    
    export default {
        props: {
            group: {
                type: Object,
                required: true
            },
            groupImage: {
                type: String,
                default: ''
            },
            spinner: {
                type: String,
                default: ''
            },
            robuxRate: {
                type: [Number, String],
                default: 0
            },
            robuxTax: {
                type: Number,
                default: 0.7
            },
            robuxType: {
                type: String,
                default: '120h'
            }
        },
        setup(props) {
            const gamepassLink = ref('');
            const gamepassPassword = ref('');
            const selectedPackages = ref([]);
            const confirmed = ref(false);
            const localPrizes = ref([]);
            const loading = ref(false);
            const showMobileSummary = ref(false);
            
            const inputRobuxAmount = ref('');
            const inputAfterTaxAmount = ref('');
            const inputPrice = ref('');

            // Coupon state
            const formBuy = ref({
                coupon_code: ''
            });
            const couponApplied = ref(false);
            const discountAmount = ref(0);
            const checkingCoupon = ref(false);

            const formatNumber = (num) => {
                 if (num === null || num === undefined || num === '') return '0';
                 // Strip non-numeric characters (allow dots for decimals if needed, but Robux is usually int)
                 // Keeping it simple: remove everything that's not 0-9, ., or -
                 const n = Number(String(num).replace(/[^0-9.-]/g, ''));
                 if (isNaN(n)) return '0';
                 return new Intl.NumberFormat('en-US').format(n);
            };
    


            // Parse Rate Config from sub_name OR robuxRate if available (Format: 100, 500|50)
            const parsedRateTiers = computed(() => {
                let config = props.group.sub_name || '';
                // If sub_name doesn't have tiers but robuxRate does, use robuxRate
                if (!config && props.robuxRate) {
                    config = String(props.robuxRate);
                }
                
                if (!config) return [];

                try {
                     const parts = config.split(',');
                     const tiers = [];
                     
                     parts.forEach(part => {
                         const s = part.trim();
                         if (!s) return;
                         
                         if (s.includes('|')) {
                             const [limit, rate] = s.split('|').map(x => parseInt(x.trim()) || 0);
                             if (rate > 0) {
                                 tiers.push({ limit, rate });
                             }
                         } else {
                             // Standalone number is treated as Base Rate (Limit 0)
                             const rate = parseInt(s);
                             if (rate > 0) {
                                 tiers.push({ limit: 0, rate });
                             }
                         }
                     });
                     
                     return tiers.sort((a, b) => a.limit - b.limit);
                } catch (e) {
                    return [];
                }
            });

            const getRateForAmount = (amount) => {
                // Priority 1: Use Tier Config
                if (parsedRateTiers.value.length > 0) {
                     const tiers = parsedRateTiers.value; 
                     // Loop backwards to find the highest threshold <= amount
                     for (let i = tiers.length - 1; i >= 0; i--) {
                         if (amount >= tiers[i].limit) return tiers[i].rate;
                     }
                     // Should be covered by limit:0, but safe fallback
                     return tiers[0].rate;
                }

                // Priority 2: Fallback to simple prop rate. Ensure it is a number.
                const r = Number(props.robuxRate);
                return (!isNaN(r)) ? r : 0;
            };

            // Fetch products logic
            const getProducts = async () => {
                // First check if prizes are provided in props
                if (props.group.prizes && props.group.prizes.length > 0) {

                     localPrizes.value = props.group.prizes.map((p, index) => {
                        // Safe parsing for Price (percent) and Robux Amount (value)
                        // Remove dots and commas to handle format "10.000" or "10,000"
                        const rawPrice = String(p.percent || '0').replace(/[.,]/g, '');
                        const rawValue = String(p.value || '0').replace(/[.,]/g, '');
                        
                        let amount = parseInt(rawPrice) || 0;
                        const robuxAmount = parseInt(rawValue) || extractRobuxAmount(p.name || '');

                        if (props.robuxType === 'genuine') {
                             // For genuine robux, the 'percent' field IS the price already
                             return {
                                 ...p,
                                 amount: amount, 
                                 robux_amount: robuxAmount,
                                 tierRate: 0, 
                                 code: `robux-${props.group.id}-${index}`,
                                 name: `Gói ${formatNumber(robuxAmount)} Robux`
                             };
                        }
                        
                        // Heuristic: If amount (percent field) is small (e.g. < 1000), it's likely a Rate.
                        // (User uses Rate 200, so we need a higher threshold than 50. Prices are usually > 1000)
                        let rate = getRateForAmount(robuxAmount);
                        
                        if (amount > 0 && amount < 1000) {
                             // It's a Rate
                             rate = amount;
                             // Calculate Price from Rate: Price = Robux * Rate
                             amount = Math.floor(robuxAmount * rate);
                        } else if (amount >= 1000) {
                             // It's a Price (Legacy config or explicit price)
                             // Calculate implies rate: Rate = Price / Robux
                             if (robuxAmount > 0) {
                                rate = Math.floor(amount / robuxAmount) || getRateForAmount(robuxAmount);
                             }
                        } else {
                             // amount is 0, use default rate
                             // Recalculate based on default Rate
                             rate = getRateForAmount(robuxAmount);
                             amount = Math.floor(robuxAmount * rate);
                        }
                        
                        return {
                            ...p,
                            amount: amount, 
                            robux_amount: robuxAmount,
                            tierRate: rate, // Store specific rate for this tier
                            // Generate a virtual code for backend identification
                            code: `robux-${props.group.id}-${index}`,
                            name: `Gói ${formatNumber(robuxAmount)} Robux`
                        };
                    });
                    
                    return;
                }
                
                // Fallback to API if no props.prizes
                loading.value = true;
                try {
                    const { data: result } = await axios.get('/api/stores/items', {
                        params: {
                            group_id: props.group.id,
                            limit: 100
                        },
                    });
                    
                    if (result.data && result.data.data) {
                        localPrizes.value = result.data.data.map(p => {
                            const robuxAmount = extractRobuxAmount(p.name);
                            return {
                                ...p,
                                amount: p.price,
                                robux_amount: robuxAmount,
                                name: `Gói ${formatNumber(robuxAmount)} Robux`
                            };
                        });
                    }
                } catch (error) {
                    console.error('Failed to fetch Robux packages', error);
                } finally {
                    loading.value = false;
                }
            };
    
            onMounted(() => {
                getProducts();
            });
    
            const selectPackage = (pkg) => {
                // If starting package selection from manual input (no packages selected), reset amount first
                let currentRobux = selectedPackages.value.length === 0 ? 0 : (parseInt(inputRobuxAmount.value.replace(/[^0-9]/g, '')) || 0);
                
                const index = selectedPackages.value.findIndex(p => p.code === pkg.code);
                
                if (index > -1) {
                    currentRobux = Math.max(0, currentRobux - pkg.robux_amount);
                    selectedPackages.value.splice(index, 1);
                } else {
                    currentRobux += pkg.robux_amount;
                    selectedPackages.value.push(pkg);
                }
                 
                 const totalRobux = selectedPackages.value.reduce((sum, p) => sum + p.robux_amount, 0);
                 const totalPrice = selectedPackages.value.reduce((sum, p) => sum + p.amount, 0);

                  // Update inputs based on new total, keep selected packages
                  updateAllInputs(totalRobux, 'robux', false);
                  if (props.robuxType === 'genuine') {
                      updateAllInputs(totalPrice, 'price', false);
                  }
 
                 // Reset coupon if package changes
                couponApplied.value = false;
                discountAmount.value = 0;
            };

            const isPackageSelected = (pkg) => {
                return selectedPackages.value.some(p => p.code === pkg.code);
            };
            
            // Coupon Logic
             const checkCoupon = async () => {
                if (!formBuy.value.coupon_code) {
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: 'Vui lòng nhập mã giảm giá' });
                }
                
                if (parseFloat(inputPrice.value.replace(/[^0-9]/g, '')) <= 0) {
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: 'Vui lòng chọn gói hoặc nhập số lượng trước' });
                }
                
                checkingCoupon.value = true;
                try {
                    const productIds = ['item-' + props.group.id];
                    
                    const { data: result } = await axios.post('/api/check-coupon', {
                        coupon_code: formBuy.value.coupon_code.trim(),
                        cart_total: Number(inputPrice.value.replace(/[^0-9]/g, '')),
                        product_ids: productIds
                    });
                    
                    if (result.status) {
                        discountAmount.value = result.data.discount_amount;
                        couponApplied.value = true;
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'success', title: result.message });
                    } else {
                        discountAmount.value = 0;
                        couponApplied.value = false;
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: result.message });
                    }
                } catch (error) {
                    discountAmount.value = 0;
                    couponApplied.value = false;
                    Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: error.response?.data?.message || 'Lỗi kiểm tra mã' });
                } finally {
                    checkingCoupon.value = false;
                }
            };
    
            const cancelCoupon = () => {
                formBuy.value.coupon_code = '';
                discountAmount.value = 0;
                couponApplied.value = false;
            };
    
            const extractRobuxAmount = (name) => {
                const match = String(name).replace(/\./g, '').match(/(\d+)/);
                return match ? parseInt(match[0]) : 0;
            };
    
            const formatCurrency = (value) => {
                 return new Intl.NumberFormat('en-US').format(value) + ' đ';
            };
            
            const prizePrice = (prize) => {
                return prize ? prize.amount : 0;
            };
            
             // Handle Tax Logic:
             // If tax > 1 (e.g., 30), it's a Percentage (30%). Retain = 100% - 30% = 70%.
             // If tax <= 1 (e.g., 0.7), it's a Retain Factor (70%). Tax = 100% - 70% = 30%.
             
             const taxLabel = computed(() => {
                const tax = Number(props.robuxTax);
                if (tax === 0) return 0;
                
                if (tax >= 1) {
                    return Math.round(tax);
                }
                return Math.round((1 - tax) * 100);
             });

             const retainFactor = computed(() => {
                const tax = Number(props.robuxTax);
                if (tax === 0) return 1;

                if (tax >= 1) {
                    return (100 - tax) / 100;
                }
                return tax;
             });

             const taxText = computed(() => {
                 return `Sau thuế (${taxLabel.value}%)`;
             });

            const finalPrice = computed(() => {
                 const price = parseInt(inputPrice.value.replace(/[^0-9]/g, '')) || 0;
                 return Math.max(0, price - discountAmount.value);
            });

            const totalOriginalPrice = computed(() => {
                 return parseInt(inputPrice.value.replace(/[^0-9]/g, '')) || 0;
            });

             const currentRate = computed(() => {
                const rawRobux = String(inputRobuxAmount.value).replace(/[^0-9]/g, '');
                const robux = parseInt(rawRobux) || 0;
                
                // User Request: Rate = Price / Robux
                const price = totalOriginalPrice.value;
                if (robux > 0 && price > 0) {
                     const r = price / robux;
                     const calculatedRate = Math.floor(r);
                     if (calculatedRate > 0) return calculatedRate;
                }
                
                // Fallback: Use theoretical rate
                if (robux > 0) {
                     return getRateForAmount(robux);
                }

                // Global fallback
                return 0;
             });

             const summaryText = computed(() => {
                const robux = parseInt(inputRobuxAmount.value.replace(/[^0-9]/g, '')) || 0;
                if (robux > 0) return `Mua ${formatNumber(robux)} Robux`;
                
                return 'Chưa chọn';
            });
            
            const isValid = computed(() => {
                return gamepassLink.value.length > 0 && (selectedPackages.value.length > 0 || inputRobuxAmount.value) && confirmed.value;
            });

            const maxRobuxAllowed = computed(() => {
                // Sum of all available packages
                if (localPrizes.value && localPrizes.value.length > 0) {
                     return localPrizes.value.reduce((sum, p) => sum + (p.robux_amount || 0), 0);
                }
                return 999999999; // Fallback if no packages
            });
    
            const buyNow = async () => {
                const robuxAmount = parseInt(inputRobuxAmount.value.replace(/[^0-9]/g, '')) || 0;

                if (!gamepassLink.value) {
                    const msg = props.robuxType === 'genuine' ? 'Vui lòng nhập tên tài khoản' : 'Vui lòng nhập Pass ID / Link Game Pass';
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: msg });
                }

                if (props.robuxType !== 'genuine') {
                    const valInput = gamepassLink.value.trim();
                    const isUrl = valInput.includes('roblox.com') && valInput.includes('/game-pass/');
                    const isNumeric10 = /^\d{10}$/.test(valInput);

                    if (!isUrl && !isNumeric10) {
                        return Swal.fire({ 
                            toast: true, 
                            position: 'top-end', 
                            showConfirmButton: false, 
                            timer: 3000, 
                            icon: 'error', 
                            title: 'Pass ID phải đúng 10 số hoặc nhập Link game pass' 
                        });
                    }
                }

                if (robuxAmount <= 0) {
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: 'Vui lòng nhập số lượng Robux' });
                }

                if (!confirmed.value) {
                    const msg = props.robuxType === 'genuine' ? 'Vui lòng xác nhận thông tin cung cấp đúng yêu cầu' : 'Vui lòng xác nhận đã tạo đúng Game Pass';
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: msg });
                }
    
                const confirm = await Swal.fire({
                    icon: 'question',
                    title: 'Bạn chắc chứ?',
                    text: `Bạn sẽ mua với giá ${formatCurrency(finalPrice.value)}?`,
                    showCancelButton: true,
                    confirmButtonText: 'Đồng ý',
                    cancelButtonText: 'Hủy',
                });
    
                if (confirm.isConfirmed !== true) return;
                
                // Find a primary product to use as ID (if multi-select)
                const product = selectedPackages.value.length > 0 
                    ? selectedPackages.value[0] 
                    : (localPrizes.value.length > 0 ? localPrizes.value[0] : { code: 'robux-' + props.group.id + '-0' });
                
                // ... same buy logic logic
                Swal.fire({ 
                    icon: 'info',
                    title: 'Đang xử lý!',
                    html: 'Không được tắt trang này, vui lòng đợi trong giây lát!',
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => { Swal.showLoading() },
                });
    
                try {
                    const formParam = {
                        Tai_Khoan: gamepassLink.value,
                        isConfirm: true,
                        coupon_code: couponApplied.value ? formBuy.value.coupon_code : '',
                        Mat_Khau: gamepassPassword.value,
                        Lien_He: '',
                        user_note: '',
                        robux_amount: parseInt(inputRobuxAmount.value.replace(/[^0-9]/g, '')) || 0,
                        package_indices: selectedPackages.value.map(p => {
                            return localPrizes.value.findIndex(lp => lp.code === p.code);
                        }).filter(idx => idx !== -1)
                    };
                    
                    let buyUrl = '/api/stores/items/' + (product.code || product.id) + '/buy';
                    
                    const { data: result } = await axios.post(buyUrl, formParam);
    
                    sessionStorage.setItem('pending_toast', JSON.stringify({
                        icon: 'success',
                        title: 'Thành Công',
                        message: result.message
                    }));
                    window.open('/account/orders/items/' + result.data.code, '_self');
                } catch (error) {
                    Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: error.response?.data?.message || 'Có lỗi xảy ra' });
                }
            };
    
            // Helper to calculate Price based on Robux & Rate
            // User confirmed strict multiplication: Price = Robux * Rate
            const calculatePrice = (robux, rate) => {
                 return Math.floor(robux * rate);
            };

            const calculateRobux = (price, rate) => {
                 if (rate <= 0) return 0;
                 return Math.floor(price / rate);
            };



            const updateAllInputs = (baseValue, type, clearPackages = true) => {
                if (clearPackages) {
                    selectedPackages.value = [];
                }

                if (props.robuxType === 'genuine') {
                    if (type === 'robux') {
                        inputRobuxAmount.value = baseValue > 0 ? formatNumber(baseValue) : '';
                    } else if (type === 'price') {
                        inputPrice.value = baseValue > 0 ? formatNumber(baseValue) : '';
                    }
                    return;
                }

                let robux = 0;
                
                if (type === 'robux') {
                    robux = baseValue;
                } else if (type === 'after-tax') {
                    robux = Math.ceil(baseValue / retainFactor.value); // Estimate
                } else if (type === 'price') {
                     // Estimation: Use default rate first to get rough Robux
                     // Note: Price = Robux * Rate -> Robux = Price / Rate.
                     // But Rate depends on Robux. Circular.
                     
                     // Iterative approach or approximation.
                     // Use lowest rate first? Or default rate.
                     let tempRobux = calculateRobux(baseValue, props.robuxRate);
                     
                     // Helper to find Robux from Price given Rate Tiers functions is hard because it's piecewise linear.
                     // Reverse lookup:
                     // For each tier, calculate if Price fits in the range (Limit * Rate).
                     
                     if (parsedRateTiers.value.length > 0) {
                         let found = false;
                         for (const tier of parsedRateTiers.value) {
                             // Max Price for this tier is Limit * Rate
                             // Wait, is "Limit" the Robux Limit? Yes.
                             // Input is Price (VND).
                             
                             // Calculate potential Robux using this tier's rate
                             let r = Math.floor(baseValue / tier.rate);
                             
                             // Check if this estimated Robux actually falls within this tier's validity
                             // Validity for Tier i: (PrevLimit < r <= Limit)
                             // Actually we iterate ASC.
                             // Ideally: r <= Limit.
                             
                             // But wait, if input Price is huge, using strict Limit might be wrong if we just want "Which rate applies".
                             // Rate applies based on Robux amount.
                             
                             // Let's stick to simple approximation:
                             // Calculate Robux using this rate. If Robux <= Limit, then this is the correct tier.
                             if (r <= tier.limit) {
                                 tempRobux = r;
                                 found = true;
                                 break;
                             }
                         }
                         if (!found) {
                             // Exceeds all limits, use last rate
                             const lastRate = parsedRateTiers.value[parsedRateTiers.value.length - 1].rate;
                             tempRobux = Math.floor(baseValue / lastRate);
                         }
                     }
                     
                     robux = tempRobux;
                }

                // Global Cap: Cannot exceed total available robux
                if (robux > maxRobuxAllowed.value) {
                    robux = maxRobuxAllowed.value;
                }
                
                // Determine Final Rate based on the calculated Robux
                const appliedRate = getRateForAmount(robux);

                 const afterTax = Math.floor(robux * retainFactor.value);
  
                 inputRobuxAmount.value = robux > 0 ? formatNumber(robux) : '';
                 inputAfterTaxAmount.value = afterTax > 0 ? formatNumber(afterTax) : '';
                  
                  if (type !== 'price') {
                      // Calculate Price using Hybrid Logic
                      const priceVal = calculatePrice(robux, appliedRate);
                      inputPrice.value = robux > 0 ? formatNumber(priceVal) : '';
                  }
              };

            const onAmountChange = (e) => {
                const rawValue = e.target.value.replace(/[^0-9]/g, '');
                let newVal = parseInt(rawValue) || 0;

                // If packages are selected, check if user is changing the value
                if (selectedPackages.value.length > 0) {
                    const pkgTotal = selectedPackages.value.reduce((sum, p) => sum + p.robux_amount, 0);
                    // If the new value is different from the package total, it means user wants to edit custom
                    if (newVal !== pkgTotal) {
                         selectedPackages.value = []; // Deselect package to allow custom input
                    }
                }

                updateAllInputs(newVal, 'robux');
            };

            const onAfterTaxChange = (e) => {
                const rawValue = e.target.value.replace(/[^0-9]/g, '');
                updateAllInputs(parseInt(rawValue) || 0, 'after-tax');
            };

             const onPriceChange = (e) => {
                 const rawValue = e.target.value.replace(/[^0-9]/g, '');
                 inputPrice.value = rawValue ? formatNumber(parseInt(rawValue) || 0) : '';
                 updateAllInputs(parseInt(rawValue) || 0, 'price');
             };

            const handleOpenMobileSummary = () => {
                const robuxAmount = parseInt(inputRobuxAmount.value.replace(/[^0-9]/g, '')) || 0;
                
                if (!gamepassLink.value) {
                    const msg = props.robuxType === 'genuine' ? 'Vui lòng nhập tên tài khoản' : 'Vui lòng nhập Pass ID hoặc Link game pass';
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: msg });
                }

                if (props.robuxType !== 'genuine') {
                    const valInputMobile = gamepassLink.value.trim();
                    const isUrlMobile = valInputMobile.includes('roblox.com') && valInputMobile.includes('/game-pass/');
                    const isNumeric10Mobile = /^\d{10}$/.test(valInputMobile);

                    if (!isUrlMobile && !isNumeric10Mobile) {
                        return Swal.fire({ 
                            toast: true, 
                            position: 'top-end', 
                            showConfirmButton: false, 
                            timer: 3000, 
                            icon: 'error', 
                            title: 'Pass ID phải đúng 10 số hoặc nhập Link game pass' 
                        });
                    }
                }

                if (robuxAmount <= 0) {
                    return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: 'Vui lòng nhập số lượng Robux' });
                }

                showMobileSummary.value = true;
            };

            return {
                gamepassLink,
                gamepassPassword,
                selectedPackages,
                confirmed,
                localPrizes,
                loading,
                formBuy,
                couponApplied,
                discountAmount,
                checkingCoupon,
                robuxTax: props.robuxTax,
                taxLabel,
                taxText,
                finalPrice,
                totalOriginalPrice,
                summaryText,
                selectPackage,
                isPackageSelected,
                checkCoupon,
                cancelCoupon,
                formatCurrency,
                formatNumber,
                isValid,
                buyNow,
                inputRobuxAmount,
                inputAfterTaxAmount,
                inputPrice,
                onAmountChange,
                onAfterTaxChange,
                onPriceChange,
                showMobileSummary,
                handleOpenMobileSummary,
                currentRate
            };
        }
    }
    </script>

<style scoped>
:deep(.ant-input-affix-wrapper input.ant-input) {
    border: none !important;
    box-shadow: none !important;
}
</style>
