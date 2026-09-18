<script setup>
import { computed, onMounted, ref } from 'vue'

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

const form = ref({
  price: '',
  search: '',
  display_by: '',
})

const pagination = ref({
  page: 1,
  limit: 16,
  total_rows: 0,
  total_page: 0,
  visible_pages: [],
})

const visiblePages = computed(() => {
  const { page, total_page } = pagination.value
  const visibleRange = 5

  let startPage = Math.max(page - Math.floor(visibleRange / 2), 1)
  let endPage = Math.min(startPage + visibleRange - 1, total_page)

  if (endPage - startPage + 1 < visibleRange) {
    startPage = Math.max(endPage - visibleRange + 1, 1)
  }

  return Array.from(
    { length: endPage - startPage + 1 },
    (_, i) => startPage + i
  )
})

const goToPage = (page) => {
  if (pagination.value.page === page) {
    return
  }

  pagination.value.page = page

  getProducts()
}

const onPrevPage = () => {
  if (pagination.value.page <= 1) return

  pagination.value.page--

  getProducts()
}

const onNextPage = () => {
  if (pagination.value.page >= pagination.value.total_page) return

  pagination.value.page++

  getProducts()
}

const orderBy = computed(() => {
  if (__defaultLang && __defaultLang === 'vn') {
    return [
      {
        label: 'Mặc định',
        value: '',
      },
      {
        label: 'Mới nhất',
        value: 'created_at_desc',
      },
      {
        label: 'Cũ nhất',
        value: 'created_at_asc',
      },
      {
        label: 'Giá thấp đến cao',
        value: 'price_asc',
      },
      {
        label: 'Giá cao đến thấp',
        value: 'price_desc',
      },
    ]
  } else {
    return [
      {
        label: 'Default',
        value: '',
      },
      {
        label: 'Newest',
        value: 'created_at_desc',
      },
      {
        label: 'Oldest',
        value: 'created_at_asc',
      },
      {
        label: 'Price low to high',
        value: 'price_asc',
      },
      {
        label: 'Price high to low',
        value: 'price_desc',
      },
    ]
  }
})

const priceRange = computed(() => {
  if (__defaultLang && __defaultLang === 'vn') {
    return [
      {
        label: 'Tất cả',
        value: '',
      },
      {
        label: 'Dưới 100.000đ',
        value: '0-100000',
      },
      {
        label: '100.000đ - 200.000đ',
        value: '100000-200000',
      },
      {
        label: '200.000đ - 500.000đ',
        value: '200000-500000',
      },
      {
        label: '500.000đ - 1.000.000đ',
        value: '500000-1000000',
      },
      {
        label: 'Trên 1.000.000đ',
        value: '1000000-0',
      },
    ]
  } else {
    return [
      {
        label: 'All',
        value: '',
      },
      {
        label: 'Under $5',
        value: '0-5',
      },
      {
        label: '$5 - $10',
        value: '5-10',
      },
      {
        label: '$10 - $20',
        value: '10-20',
      },
      {
        label: '$20 - $50',
        value: '20-50',
      },
      {
        label: 'Over $50',
        value: '50-0',
      },
    ]
  }
})

const loading = ref(false)
const products = ref([])

const getProducts = async () => {
  loading.value = true
  try {
    const { data: result } = await axios.get('/api/stores/accounts-v2', {
      params: {
        ...form.value,
        group_id: props.groupId,
        ...pagination.value,
      },
    })

    if (
      getUrlQuery('page') !== null &&
      pagination.value.page !== parseInt(getUrlQuery('page'))
    ) {
      deleteUrlQuery('product_id')
    }

    if (pagination.value.page !== 1) {
      setUrlQuery('page', pagination.value.page)
    } else {
      deleteUrlQuery('page')
    }

    products.value = result.data?.data || []
    pagination.value = result.data?.meta || []
  } catch (error) {
    Swal.fire('Oops...', $catchMessage(error), 'error')
  } finally {
    setTimeout(() => {
      loading.value = false
      scollUp()
    }, 600)
  }
}

const onFilter = () => {
  pagination.value.page = 1

  getProducts()
}

const resetFilter = () => {
  form.value = {
    price: '',
    search: '',
  }

  pagination.value.page = 1

  getProducts()
}

const formatCurrency = (number, currency = 'VND', maxinum = 2) => {
  return $formatCurrency(number, currency, maxinum)
}

// Modal State
const openBuyModal = ref(false)
const selectedItem = ref(null)
const couponCode = ref('')
const couponApplied = ref(false)
const discountAmount = ref(0)
const checkingCoupon = ref(false)
const buyQuantity = ref(1)

const currentPrice = computed(() => {
    if (!selectedItem.value) return 0
    // Check if bulk discount logic exists in original code?
    // Original: formatCurrency((item.price - (item.price * item.discount) / 100) * quantity)
    // It seems item.price is original price? And item.discount is percentage?
    // Wait, let's check `item.price_str` usage in original code.
    // In original code: item.price_str is displayed.
    // In bulk calc: (item.price - (item.price * item.discount) / 100) * quantity
    // This implies item.price is the base price, and discount is applied.
    // But `item.price_str` usually includes the discount if handled by backend.
    // Let's assume item.price is the FINAL unit price for calculation?
    // No, the bulk calc explicitly does `(item.price - (item.price * item.discount) / 100)`.
    // This means `item.price` is likely the original price.
    // So unit price = item.price * (1 - item.discount/100).
    
    let unitPrice = selectedItem.value.price
    if (selectedItem.value.discount > 0) {
        unitPrice = unitPrice * (1 - selectedItem.value.discount / 100)
    }
    return unitPrice * buyQuantity.value
})

const totalOriginalPrice = computed(() => {
    if (!selectedItem.value) return 0
    return selectedItem.value.price * buyQuantity.value
})

const finalPrice = computed(() => {
    return Math.max(0, currentPrice.value - discountAmount.value)
})

const checkCoupon = async () => {
    if (!couponCode.value) return
    
    if (!selectedItem.value || currentPrice.value <= 0) {
        return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: $__t('Vui lòng chọn sản phẩm trước khi áp mã giảm giá') })
    }
    
    checkingCoupon.value = true
    try {
        const { data: result } = await axios.post('/api/check-coupon', {
            coupon_code: couponCode.value.trim(),
            cart_total: Number(currentPrice.value),
            product_ids: ['account_v2-' + props.groupId] // Use group ID for checking, specific for V2
        })
        
        if (result.status) {
            discountAmount.value = result.data.discount_amount
            couponApplied.value = true
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'success', title: result.message })
        } else {
            discountAmount.value = 0
            couponApplied.value = false
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: result.message })
        }
    } catch (error) {
        discountAmount.value = 0
        couponApplied.value = false
        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: $catchMessage(error) })
    } finally {
        checkingCoupon.value = false
    }
}

const cancelCoupon = () => {
    couponCode.value = ''
    discountAmount.value = 0
    couponApplied.value = false
}

const openModal = (item) => {
    selectedItem.value = item
    couponCode.value = ''
    discountAmount.value = 0
    couponApplied.value = false
    buyQuantity.value = 1
    openBuyModal.value = true
}

const handleCancel = () => {
  openBuyModal.value = false
  selectedItem.value = null
}

const submitting = ref(false)

const confirmBuy = async () => {
  if (!selectedItem.value) return

  const item = selectedItem.value
  const quantity = buyQuantity.value

  // Bulk validation
  if (item.is_bulk > 1) {
      if (!quantity || quantity < 1) {
          return toastr.error(__t('Số lượng tài khoản cần mua phải lớn hơn 0'))
      }
      if (quantity > item.is_bulk) {
           return toastr.error(__t('Số lượng tài khoản cần mua không được lớn hơn') + ' ' + item.is_bulk)
      }
  }
  
  submitting.value = true

  try {
    const { data: result } = await axios.post(
      '/api/stores/accounts-v2/' + item.code + '/buy',
      {
        is_bulk: item.is_bulk,
        quantity: quantity,
        group_id: props.groupId,
        coupon_code: couponApplied.value ? couponCode.value : null
      }
    )

    // Store pending toast for next page
    sessionStorage.setItem('pending_toast', JSON.stringify({
        icon: 'success',
        title: 'Thành công',
        message: result.message
    }));

    if (result.data.is_bulk) {
      window.open('/account/orders/accounts/' + result.data.code, '_self')
    } else {
      window.open('/account/orders/accounts/' + result.data.code, '_self')
    }
  } catch (error) {
    const errorMsg = $catchMessage(error);
    if (window.Swal) {
         window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', window.Swal.stopTimer)
                toast.addEventListener('mouseleave', window.Swal.resumeTimer)
            }
        }).fire({
            icon: 'error',
            title: 'Lỗi',
            text: errorMsg
        });
    } else {
        toastr.error(errorMsg);
    }
  } finally {
    submitting.value = false
    // Don't close modal immediately on error so user can retry or read status, 
    // but on success we redirect essentially closing it.
    // If we want to close modal on error, uncomment next line:
    // openBuyModal.value = false 
  }
}

// Replaced buyItem with openModal logic wrapper
const buyItem = (item) => {
    openModal(item)
}

const redirectTo = (item) => {
  window.open('/tai-khoan-v2/thong-tin/' + item.code, '_self')
}

const isString = (value) => {
  return typeof value === 'string' || value instanceof String
}

const scollUp = () => {
  window.scrollTo({
    top: 0,
    behavior: 'auto',
  })
}

const setUrlQuery = (name, value) => {
  if (parseInt(getUrlQuery(name)) === parseInt(value)) return

  const url = new URL(window.location.href)
  url.searchParams.set(name, value)
  window.history.pushState({}, '', url)
}

const deleteUrlQuery = (name) => {
  if (getUrlQuery(name) === null) return

  const url = new URL(window.location.href)
  url.searchParams.delete(name)
  window.history.pushState({}, '', url)
}

const getUrlQuery = (name) => {
  const url = new URL(window.location.href)
  return url.searchParams.get(name)
}

const isCardActive = (id) => {
  const url = new URL(window.location.href)
  return url.searchParams.get('product_id') === id
}

window.addEventListener('popstate', () => {
  const page = getUrlQuery('page')

  if (page && pagination.value.page !== parseInt(page)) {
    pagination.value.page = parseInt(page)
    getProducts()
  } else if (!page && pagination.value.page !== 1) {
    pagination.value.page = 1
    getProducts()
  }
})

const __t = (text) => {
  return $__t(text)
}

onMounted(() => {
  const page = getUrlQuery('page')
  if (page) {
    pagination.value.page = parseInt(page)
  }
  getProducts()
})
</script>
<template>
  <section>
    <div class="mb-2" v-if="group && group.descr">
        <div class="bg-white rounded-lg shadow-sm border border-gray-300 overflow-hidden -mx-2 sm:mx-0">
            <div class="p-5 ck-content" v-html="group.descr"></div>
        </div>
    </div>
    <a-spin :spinning="loading">
      <form class="mb-5 mt-2" @submit.prevent="onFilter">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label class="form-label">{{ __t('Chọn mức giá') }}: </label>
            <a-select v-model:value="form.price" :placeholder="__t('Chọn mức giá')" style="width: 100%">
              <a-select-option v-for="item in priceRange" :key="item.value" :value="item.value">{{ item.label }}</a-select-option>
            </a-select>
          </div>
          <div>
            <label class="form-label">{{ __t('Sắp Xếp Theo:') }}</label>
            <a-select v-model:value="form.display_by" style="width: 100%">
              <a-select-option v-for="item in orderBy" :key="item.value" :value="item.value">{{ item.label }}</a-select-option>
            </a-select>
          </div>
          <div>
            <label class="form-label">{{ __t('Tìm kiếm') }}: </label>
            <a-input v-model:value="form.search" :placeholder="__t('Tìm kiếm mã sản phẩm, trang phục,...')" style="width: 100%" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <button class="btn btn-sm btn-primary w-full lg:mt-[32px]">
                <i class="fas fa-search"></i> {{ __t('Tìm kiếm') }}
              </button>
            </div>
            <div>
              <button class="btn btn-sm btn-danger w-full lg:mt-[32px]" type="button" @click="resetFilter()">
                <i class="fas fa-trash"></i> {{ __t('Đặt lại') }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </a-spin>

    <a-row :gutter="[16, 16]">
      <a-col :xs="24" :sm="12" :md="8" :lg="6" v-for="item in products" :key="item.id" class="mb-2 mt-2 cursor-default" :id="'card_' + item.code">
        <a-badge-ribbon color="red" :text="`-${item.discount}%`" :class="{ hidden: item.discount <= 0 }" placement="start">
          <a-badge-ribbon color="gray" :text="`MS: ${item.code}`" placement="end">
            <div class="border border-gray-200 rounded-lg p-[1px] bg-white dark:bg-gray-800 relative product-card">
              <div class="relative overflow-hidden rounded-t-lg" style="aspect-ratio: 16/9;">
                <img
                  :src="item.image || '/images/svg/spinner.svg'"
                  class="w-full h-full object-cover transition-transform duration-300 hover:scale-110 cursor-pointer"
                  :alt="item.name"
                  @click="redirectTo(item)"
                />
                <div class="absolute right-2 bottom-3 z-10 text-white text-sm font-bold rounded-md px-2 py-1 shadow-md" :class="{ 'bg-red-600': item.amount <= 0, 'bg-green-600': item.amount > 0 }">
                  <span v-if="item.amount > 0">{{ __t('Còn lại') }}: {{ item.amount }}</span>
                  <span v-else>{{ __t('Hết hàng') }}</span>
                </div>
              </div>
              <div class="px-2 pb-3 pt-1 text-left">
                <div class="text-center text-lg font-bold text-gray-900 dark:text-white" @click="redirectTo(item)">{{ item.name }}</div>
                <div class="mb-3 h-[80px] overflow-y-auto mt-2">
                  <div v-for="(hl, hlx) in item.highlights" :key="hlx" class="text-sm text-gray-700 dark:text-gray-300 mb-1">
                    <p v-if="hl?.name !== undefined && hl?.value !== 'undefined'" class="break-words"><i class="fa-solid fa-star text-primary me-1 text-[10px]"></i> <span class="font-semibold">{{ hl.name }}:</span> {{ hl.value }}</p>
                    <p v-else-if="isString(hl) || hl?.[0] !== undefined" class="break-words">{{ isString(hl) ? hl : hl[0] }}</p>
                  </div>
                </div>
                  <div class="text-center grid grid-cols-1 gap-2">
                    <div class="flex items-center justify-between px-2">
                      <del v-if="item.discount !== 0" class="text-gray-400 text-sm">{{ item.original_price_str }}</del>
                      <span class="text-red-600 font-bold text-lg">{{ item.price_str }}</span>
                    </div>
                    
                    <!-- In Stock: Show normal purchase button -->
                    <button v-if="item.amount > 0" class="btn btn-sm btn-primary w-full py-2 flex items-center justify-center gap-2" type="button" @click="buyItem(item)">
                      <i class="fas fa-shopping-cart"></i>
                      <span>{{ __t('Mua Ngay') }}</span>
                    </button>
                    
                    <!-- Out of Stock + Pre-order Enabled: Show pre-order button -->
                    <button v-else-if="item.amount <= 0 && item.allow_preorder" class="btn btn-sm btn-warning w-full py-2 flex items-center justify-center gap-2" type="button" @click="buyItem(item)">
                      <i class="fas fa-clock"></i>
                      <span>{{ __t('Đặt Trước') }}</span>
                    </button>
                    
                    <!-- Out of Stock + Pre-order Disabled: Show out of stock button -->
                    <button v-else class="btn btn-sm btn-danger w-full py-2 flex items-center justify-center gap-2 cursor-not-allowed" type="button" disabled>
                      <i class="fas fa-times-circle"></i>
                      <span>{{ __t('Hết Hàng') }}</span>
                    </button>
                  </div>
              </div>
            </div>
          </a-badge-ribbon>
        </a-badge-ribbon>
      </a-col>
    </a-row>
    <a-card v-if="products?.length === 0 && !loading">
      <a-empty :description="__t('Không tìm thấy tài khoản nào trong nhóm này')"></a-empty>
    </a-card>
    <div class="text-center mt-3">
      <a-spin :spinning="loading">
        <ul class="list-none">
          <li class="inline-block">
            <a href="javascript:void(0)" @click="onPrevPage()" class="flex items-center justify-center w-6 h-6 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 text-sm font-Inter font-medium relative top-[2px] pl-2">
              <iconify-icon icon="material-symbols:arrow-back-ios-rounded"></iconify-icon>
            </a>
          </li>
          <li v-for="page in visiblePages" :key="page" class="inline-block">
            <a href="javascript:void(0)" @click="goToPage(page)" class="flex items-center justify-center w-6 h-6 bg-slate-100 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 text-sm font-Inter font-medium" :class="{ 'p-active': pagination.page === page }">{{ page !== -1 ? page : '...' }}</a>
          </li>
          <li class="inline-block">
            <a href="javascript:void(0)" @click="onNextPage()" class="flex items-center justify-center w-6 h-6 bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 text-sm font-Inter font-medium relative top-[2px]">
              <iconify-icon icon="material-symbols:arrow-forward-ios-rounded"></iconify-icon>
            </a>
          </li>
        </ul>
      </a-spin>
    </div>
    
    <a-modal
      v-model:open="openBuyModal"
      :title="`${__t('Mua Tài Khoản')} #${selectedItem?.code}`"
      @cancel="handleCancel"
      :footer="null"
    >
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <span class="font-bold">{{ __t('Tên sản phẩm') }}:</span>
                <span>{{ selectedItem?.name }}</span>
            </div>
            
    <div v-if="selectedItem?.is_bulk > 1 || selectedItem?.is_bulk === 0">
      <label class="form-label text-xs text-black">{{ __t('Số Lượng Cần Mua') }}</label>
      <a-input-number
        v-model:value="buyQuantity"
        class="w-full custom-qty-input"
        :min="1"
        :max="selectedItem?.is_bulk > 1 ? Math.min(selectedItem.is_bulk, selectedItem.amount) : selectedItem.amount"
        size="middle"
      />
      <div v-if="selectedItem?.is_bulk > 1" class="mt-1 text-xs text-red-500">
          {{ __t('Mua tối đa') }} {{ selectedItem.is_bulk }} {{ __t('tài khoản') }}
      </div>
    </div>
            
            <div>
                 <label class="form-label text-xs text-black">{{ __t('Mã Giảm Giá') }}</label>
                 <div class="flex gap-2">
                     <a-input v-model:value="couponCode" :placeholder="__t('Nhập mã giảm giá')" :disabled="couponApplied" aria-autocomplete="off" size="middle"></a-input>
                     <a-button type="primary" :loading="checkingCoupon" @click="checkCoupon" v-if="!couponApplied">{{ __t('Áp Dụng') }}</a-button>
                     <a-button danger @click="cancelCoupon" v-else>{{ __t('Hủy') }}</a-button>
                 </div>
            </div>

            <div class="border-t pt-2 mt-2">
                 <div class="flex justify-between items-center text-lg">
                    <span class="font-bold">{{ __t('Tổng thanh toán') }}:</span>
                    <div class="text-right">
                        <div v-if="couponApplied" class="text-xs">
                            {{ __t('Đã giảm') }} <span class="text-green-600 font-bold">-{{ formatCurrency(discountAmount) }}</span>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                             <del v-if="finalPrice < totalOriginalPrice" class="text-gray-400 text-base">{{ formatCurrency(totalOriginalPrice) }}</del>
                             <span class="text-red-600 font-extrabold">{{ formatCurrency(finalPrice) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 mt-4">
                <a-button @click="handleCancel">{{ __t('Hủy') }}</a-button>
                <a-button type="primary" :loading="submitting" @click="confirmBuy">{{ __t('Xác nhận mua') }}</a-button>
            </div>
        </div>
    </a-modal>
  </section>
</template>

<style scoped>
section {
  margin-top: 20px;
}

</style>

<style>
/* Non-scoped styles for Modal content (teleported) */
.custom-qty-input.ant-input-number {
    width: 100%;
    border-radius: 6px;
    border: 1px solid #d9d9d9 !important;
    box-shadow: none !important;
}

.custom-qty-input.ant-input-number:hover,
.custom-qty-input.ant-input-number:focus,
.custom-qty-input.ant-input-number-focused {
    border-color: #1677ff !important;
    box-shadow: none !important;
    outline: none !important;
    --tw-ring-offset-shadow: 0 0 #0000 !important;
    --tw-ring-shadow: 0 0 #0000 !important;
}

/* Aggressively target inner input to remove black border/outline */
.custom-qty-input .ant-input-number-input,
.custom-qty-input input {
    height: 38px;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}

.custom-qty-input .ant-input-number-input:focus,
.custom-qty-input .ant-input-number-input:focus-visible,
.custom-qty-input input:focus,
.custom-qty-input input:focus-visible {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    --tw-ring-offset-shadow: 0 0 #0000 !important;
    --tw-ring-shadow: 0 0 #0000 !important;
}

/* Hide handlers */
.custom-qty-input .ant-input-number-handler-wrap {
    display: none !important;
}
</style>
