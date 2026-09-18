<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  groupId: {
    type: String,
    default: 0,
  },
  group: {
      type: Object,
      default: () => ({}),
  },
})

const loading = ref(false)
const packages = ref([]) // List of packages (Tabs)
const products = ref([]) // List of products within selected package
const selectedPackage = ref('')
const search = ref('')
const chosenProducts = ref([])

const selectedProductIds = computed(() => chosenProducts.value.map(p => p.id))

const getPackages = async () => {
  loading.value = true
  try {
    const { data: result } = await axios.get('/api/stores/boosting-game', {
      params: {
        limit: 100,
        group_id: props.groupId
      },
    })
    
    packages.value = result.data?.data || []
    
    packages.value.unshift({
      id: 0,
      name: __t('Tất cả')
    })
    
    if (packages.value.length > 0) {
      const urlParams = new URLSearchParams(window.location.search);
      const pkgId = urlParams.get('package_id');
      const found = packages.value.find(p => p.id == pkgId);
      
      if (pkgId && found) {
        selectedPackage.value = found.id;
      } else {
        selectedPackage.value = packages.value[0].id
      }
    }
  } catch (error) {
    Swal.fire('Oops...', $catchMessage(error), 'error')
  } finally {
    loading.value = false
  }
}

const getProducts = async (packageId) => {
  loading.value = true
  try {
    const { data: result } = await axios.get('/api/stores/boosting-game/products', {
      params: {
        limit: 100,
        package_id: search.value ? 0 : packageId,
        group_id: props.groupId,
        search: search.value
      },
    })
    
    products.value = result.data?.data || []
    // Selection persists across package switches
  } catch (error) {
    console.error('Failed to fetch products for package', packageId, error)
    products.value = []
  } finally {
    loading.value = false
  }
}

watch(selectedPackage, async (newVal) => {
  if (newVal !== undefined) {
    const url = new URL(window.location);
    if (newVal == 0) {
        url.searchParams.delete('package_id');
    } else {
        url.searchParams.set('package_id', newVal);
    }
    window.history.pushState({}, '', url);

    await getProducts(newVal)
  }
})

let searchTimeout = null
watch(search, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(async () => {
        await getProducts(selectedPackage.value)
    }, 500)
})

const onSearch = async () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    await getProducts(selectedPackage.value)
}

const formatCurrency = (number, currency = 'VND', maxinum = 2) => {
  return $formatCurrency(number, currency, maxinum)
}

const selectedProducts = computed(() => {
  return chosenProducts.value
})

const handleItemClick = (item) => {
    const index = chosenProducts.value.findIndex(p => p.id === item.id)
    if (index > -1) {
        chosenProducts.value.splice(index, 1)
    } else {
        chosenProducts.value.push(item)
    }
}

const totalPrice = computed(() => {
  return selectedProducts.value.reduce((sum, item) => sum + item.price, 0)
})

const isAllSelected = computed(() => {
  return products.value.length > 0 && selectedProductIds.value.length === products.value.length
})

const toggleAll = (e) => {
  if (e.target.checked) {
    products.value.forEach(p => {
        if (!selectedProductIds.value.includes(p.id)) {
            chosenProducts.value.push(p)
        }
    })
  } else {
    const currentIds = products.value.map(p => p.id)
    chosenProducts.value = chosenProducts.value.filter(p => !currentIds.includes(p.id))
  }
}

const onSubmit = async (value) => {
  if (selectedProducts.value.length === 0) {
    return Swal.fire('Oops...', $__t('Vui lòng chọn ít nhất một gói cần thuê'), 'error')
  }

  if (value.input_user === '' && value.input_pass === '' && (props.group.slug === 'cay-thue-blox-fruits' ? value.input_extra === '' : true)) {
    return Swal.fire('Oops...', $__t('Vui lòng nhập đầy đủ thông tin'), 'error')
  }

  const confirm = await Swal.fire({
    icon: 'question',
    title: $__t('Bạn chắc chứ?'),
    text: `${$__t('Bạn sẽ mua')} ${selectedProducts.value.length} ${$__t('gói với tổng giá')} ${formatCurrency(finalPrice.value)}?`,
    showCancelButton: true,
    confirmButtonText: $__t('Đồng ý'),
    cancelButtonText: $__t('Hủy'),
    heightAuto: false,
    focusConfirm: false,
  })

  if (confirm.isConfirmed !== true) return

  Swal.fire({
    icon: 'info',
    title: $__t('Đang xử lý!'),
    html: $__t('Không được tắt trang này, vui lòng đợi trong giây lát!'),
    timerProgressBar: true,
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    didOpen: () => {
      Swal.showLoading()
    },
  })

  try {
    // Send all products in a single request
    const productCodes = selectedProducts.value.map(p => p.code)
    const requestData = {
      ...value,
      product_codes: productCodes,
      coupon_code: formBuy.value.coupon_code
    }
    
    const { data: result } = await axios.post('/api/stores/boosting-game/buy-multiple', requestData)

    Swal.fire('Great !', result.message || $__t('Đã tạo đơn hàng thành công'), 'success').then(() => {
      if (result.data?.code) {
         window.open('/account/orders/boosting/' + result.data.code, '_self')
      }
    })
  } catch (error) {
    Swal.fire('Oops...', $catchMessage(error), 'error')
  }
}

const formBuy = ref({
  order_note: '',
  input_user: '',
  input_pass: '',
  input_extra: '',
  coupon_code: '', // Add coupon code to form
})

const couponApplied = ref(false)
const discountAmount = ref(0)
const checkingCoupon = ref(false)

const checkCoupon = async (silent = false) => {
    if (!formBuy.value.coupon_code) {
        if (!silent) {
             return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: $__t('Thất Bại'), text: $__t('Vui lòng nhập mã giảm giá') })
        }
        return
    }
    
    if (totalPrice.value <= 0) {
        discountAmount.value = 0
        couponApplied.value = false
        couponApplied.value = false
        if (!silent) {
            return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: $__t('Thất Bại'), text: $__t('Vui lòng chọn ít nhất một gói cần thuê') })
        }
        return
    }
    
    checkingCoupon.value = true
    try {
        const productIds = selectedProducts.value.map(p => 'boosting-' + props.groupId)
        
        const { data: result } = await axios.post('/api/check-coupon', {
            coupon_code: formBuy.value.coupon_code.trim(),
            cart_total: Number(totalPrice.value),
            product_ids: ['boosting-' + props.groupId]
        })
        
        if (result.status) {
            discountAmount.value = result.data.discount_amount
            couponApplied.value = true
            // Only show success toast if NOT silent (manual click)
            if (!silent) Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'success', title: $__t('Thành Công'), text: result.message })
        } else {
            discountAmount.value = 0
            couponApplied.value = false
            // ALWAYS show error toast if validation fails, even if silent (so user knows why it was removed)
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: $__t('Thất Bại'), text: result.message })
        }
    } catch (error) {
        discountAmount.value = 0
        couponApplied.value = false
        // ALWAYS show error toast on exception
        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: $__t('Thất Bại'), text: $catchMessage(error) })
    } finally {
        checkingCoupon.value = false
    }
}

const cancelCoupon = () => {
    formBuy.value.coupon_code = ''
    discountAmount.value = 0
    couponApplied.value = false
}

// Watch total price to reset coupon if amount changes
watch(totalPrice, () => {
    if (couponApplied.value) {
        discountAmount.value = 0
        couponApplied.value = false
        // Notify user that coupon is removed
        Swal.fire({ 
            toast: true, 
            position: 'top-end', 
            showConfirmButton: false, 
            timer: 3000, 
            icon: 'warning', 
            title: $__t('Cảnh Báo'),
            text: $__t('Mã giảm giá đã hủy do thay đổi gói. Vui lòng áp dụng lại mã nếu muốn sử dụng') 
        })
    }
})

const finalPrice = computed(() => {
    return Math.max(0, totalPrice.value - discountAmount.value)
})

const __t = (key) => {
  return $__t(key)
}

onMounted(() => {
  getPackages()
})
</script>
<template>
  <section>
    <div class="mb-5" v-if="group && group.descr">
        <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden -mx-2 sm:mx-0">
            <div class="p-5 ck-content" v-html="group.descr"></div>
        </div>
    </div>
    <a-card>
      <a-spin :spinning="loading">
        <a-form layout="vertical" :model="formBuy" @finish="onSubmit">
          <a-row :gutter="12">
            <!-- Left Column: Package Selection -->
            <a-col :xs="24" :lg="14">
              <a-form-item :label="__t('Chọn Gói Cần Thuê')">
                <div class="mb-3 custom-tabs">
                  <button type="button" v-for="pkg in packages" :key="pkg.id" @click="selectedPackage = pkg.id; search = ''"
                    class="tab-btn" :class="{ 'active': selectedPackage === pkg.id }">
                    {{ pkg.name }}
                  </button>
                </div>

                <div class="mb-4">
                  <a-input
                      v-model:value="search"
                      placeholder="Tìm kiếm gói cày..."
                      allow-clear
                      size="large"
                      @pressEnter="onSearch"
                  />
                </div>

                <div class="overflow-x-auto border rounded-md max-h-[500px] overflow-y-auto">
                  <table class="w-full text-sm text-left relative">
                    <!-- Hidden thead as requested by user -->
                    <thead class="hidden">
                      <tr>
                        <th class="px-4 py-3 text-center w-[50px]">
                          <input type="checkbox" :checked="isAllSelected" @change="toggleAll">
                        </th>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in products" :key="item.id"
                        class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer"
                        @click="handleItemClick(item)">
                        <td class="pl-4 pr-1 py-3 text-center w-[40px]">
                          <input type="checkbox" :checked="selectedProductIds.includes(item.id)"
                            @click.stop="handleItemClick(item)"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        </td>
                        <td class="pl-1 pr-4 py-3">
                          <div class="font-medium text-gray-900 dark:text-white">{{ item.name }}</div>
                          <div v-if="item.descr" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ item.descr }}</div>
                        </td>
                        <td class="pl-1 pr-4 py-3 text-right text-red-600 font-bold whitespace-nowrap align-middle">
                          <span class="text-base md:text-lg">{{ formatCurrency(item.price) }}</span>
                        </td>
                      </tr>
                      <tr v-if="products.length === 0">
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500">
                          {{ __t('Không có dữ liệu') }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </a-form-item>
            </a-col>

            <!-- Right Column: Form Fields -->
            <a-col :xs="24" :lg="10">
              <div class="product-buy-form p-1 rounded-lg h-full">

                <a-form-item :label="__t('Tài Khoản')" name="input_user">
                  <a-input v-model:value="formBuy.input_user" :placeholder="__t('Nhập tài khoản cần cày')"></a-input>
                </a-form-item>

                <a-form-item :label="__t('Mật Khẩu')" name="input_pass">
                  <a-input v-model:value="formBuy.input_pass" :placeholder="__t('Nhập mật khẩu của tài khoản đó')"></a-input>
                </a-form-item>

                <a-form-item :label="group.slug === 'cay-thue-blox-fruits' ? __t('Liên hệ') : __t('Cookie / 2FA')" name="input_extra">
                  <a-input v-model:value="formBuy.input_extra"
                    :placeholder="group.slug === 'cay-thue-blox-fruits' ? __t('Liên hệ (Zalo, Facebook...)') : __t('Có thể nhập chuỗi 2FA, link game pass hoặc cookie liên quan')"></a-input>
                  <div class="text-danger-600 font-bold text-xs mt-1" v-if="group.slug !== 'cay-thue-blox-fruits'">{{ __t('Dữ liệu này không bắt buộc, có thể bỏ trống') }}</div>
                </a-form-item>
                
                <a-form-item :label="__t('Liên hệ')" name="input_contact" v-if="group.slug !== 'cay-thue-blox-fruits'">
                  <a-input v-model:value="formBuy.input_contact" :placeholder="__t('Liên hệ (Zalo, Facebook...)')"></a-input>
                </a-form-item>

                <a-form-item :label="__t('Ghi Chú')" name="order_note">
                  <a-textarea v-model:value="formBuy.order_note" :placeholder="__t('Nhập ghi chú cho admin nếu có...')"
                    :rows="3"></a-textarea>
                </a-form-item>

                <a-form-item :label="__t('Mã Giảm Giá')">
                  <div class="flex gap-2">
                    <a-input v-model:value="formBuy.coupon_code" :placeholder="__t('Nhập mã giảm giá')"
                      :disabled="couponApplied"></a-input>
                    <a-button type="primary" :loading="checkingCoupon" @click="checkCoupon(false)" v-if="!couponApplied">{{
                      __t('Áp Dụng') }}</a-button>
                    <a-button danger @click="cancelCoupon" v-else>{{ __t('Hủy') }}</a-button>
                  </div>
                </a-form-item>

                <div class="mt-6 pt-4 border-t dark:border-gray-700">
                  <div class="text-center space-y-3">
                    <div>
                      <div v-if="couponApplied" class="text-sm text-success mb-1">
                        {{ __t('Đã giảm') }}: -{{ formatCurrency(discountAmount) }}
                      </div>
                      <h4 class="mb-0"><span class="text-danger-600 text-2xl font-bold">{{ formatCurrency(finalPrice) }}</span> <del
                          v-if="couponApplied" class="text-gray-400 text-sm ml-2">{{ formatCurrency(totalPrice) }}</del></h4>
                      <h6 class="text-sm text-gray-500">{{ __t('Tổng Thanh Toán') }}</h6>
                    </div>
                    <a-button type="primary" size="large" block htmlType="submit" class="h-12 text-lg font-bold">{{ __t('Tạo Đơn Hàng') }}</a-button>
                  </div>
                </div>
              </div>
            </a-col>
          </a-row>
        </a-form>
      </a-spin>
    </a-card>
  </section>
</template>

<style scoped>
.custom-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tab-btn {
    border: 1px solid #d9d9d9;
    background: #fff;
    color: #333;
    padding: 5px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    font-size: 14px;
}

.tab-btn:hover {
    color: #000;
    border-color: #000;
}

.tab-btn.active {
    background: #000;
    color: #fff;
    border-color: #000;
}

[data-theme='dark'] .tab-btn {
    background: #1f2937;
    color: #fff;
    border-color: #374151;
}

[data-theme='dark'] .tab-btn:hover {
    border-color: #fff;
}

[data-theme='dark'] .tab-btn.active {
    background: #fff;
    color: #000;
    border-color: #fff;
}

/* Tighter form spacing */
:deep(.product-buy-form .ant-form-item) {
    margin-bottom: 12px !important; 
}

/* Reduce Card Padding on Mobile */
@media (max-width: 600px) {
    :deep(.ant-card-body) {
        padding: 12px !important; /* Increased breathing room */
    }
    
    .product-buy-form {
        padding: 12px !important; 
    }
}
</style>