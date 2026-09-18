<script setup>
import { computed, onMounted, ref } from 'vue'

// Props
const props = defineProps({
  groupId: {
    type: String,
    default: 0,
  },
  group: {
    type: Object,
    default: () => ({}),
  },
  packages: {
    type: Array,
    default: () => [],
  },
})

// --- Form filter
const form = ref({
  price: '',
  search: '',
  display_by: '',
  package_id: '',
})

// --- Pagination
const pagination = ref({
  page: 1,
  limit: 9999,
  total_rows: 0,
  total_page: 0,
  visible_pages: [],
})

// visible pages (computed)
const visiblePages = computed(() => {
  const { page, total_page } = pagination.value
  const visibleRange = 5

  let startPage = Math.max(page - Math.floor(visibleRange / 2), 1)
  let endPage = Math.min(startPage + visibleRange - 1, total_page)

  if (endPage - startPage + 1 < visibleRange) {
    startPage = Math.max(endPage - visibleRange + 1, 1)
  }

  return Array.from({ length: Math.max(0, endPage - startPage + 1) }, (_, i) => startPage + i)
})

// pagination controls
const goToPage = (page) => {
  if (pagination.value.page === page) return
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
      { label: 'Mặc định', value: '' },
      { label: 'Mới nhất', value: 'created_at_desc' },
      { label: 'Cũ nhất', value: 'created_at_asc' },
      { label: 'Giá thấp đến cao', value: 'price_asc' },
      { label: 'Giá cao đến thấp', value: 'price_desc' },
    ]
  } else {
    return [
      { label: 'Default', value: '' },
      { label: 'Newest', value: 'created_at_desc' },
      { label: 'Oldest', value: 'created_at_asc' },
      { label: 'Price low to high', value: 'price_asc' },
      { label: 'Price high to low', value: 'price_desc' },
    ]
  }
})

const priceRange = computed(() => {
  if (__defaultLang && __defaultLang === 'vn') {
    return [
      { label: 'Tất cả', value: '' },
      { label: 'Dưới 100.000đ', value: '0-100000' },
      { label: '100.000đ - 200.000đ', value: '100000-200000' },
      { label: '200.000đ - 500.000đ', value: '200000-500000' },
      { label: '500.000đ - 1.000.000đ', value: '500000-1000000' },
      { label: 'Trên 1.000.000đ', value: '1000000-0' },
    ]
  } else {
    return [
      { label: 'All', value: '' },
      { label: 'Under $5', value: '0-5' },
      { label: '$5 - $10', value: '5-10' },
      { label: '$10 - $20', value: '10-20' },
      { label: '$20 - $50', value: '20-50' },
      { label: 'Over $50', value: '50-0' },
    ]
  }
})

// --- Data
const loading = ref(false)
const products = ref([])

// --- API calls
const getProducts = async () => {
  loading.value = true
  try {
    const { data: result } = await axios.get('/api/stores/items', {
      params: {
        ...form.value,
        group_id: props.groupId,
        ...pagination.value,
      },
    })

    products.value = result.data?.data || []
    pagination.value = result.data?.meta || []
  } catch (error) {
    Swal.fire('Oops...', $catchMessage(error), 'error')
  } finally {
    loading.value = false
    scollUp()
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
    display_by: '',
  }
  pagination.value.page = 1
  getProducts()
}

// --- helpers
const scollUp = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatCurrency = (number, currency = 'VND', maxinum = 2) => {
  return $formatCurrency(number, currency, maxinum)
}

// --- Modal & buy
const open = ref(false)
const product = ref(null)

const resetFormBuy = () => ({
  Lien_He: '',
  Mat_Khau: '',
  Tai_Khoan: '',
  Ten_Game: '',
  user_note: '',
  isConfirm: false,
  Dang_Nhap_Bang: null,
  coupon_code: '',
})
const formBuy = ref(resetFormBuy())
const couponApplied = ref(false)
const discountAmount = ref(0)
const checkingCoupon = ref(false)

const showModal = async (item) => {
  formBuy.value = resetFormBuy()
  product.value = item
  couponApplied.value = false
  discountAmount.value = 0
  open.value = true
}

const checkCoupon = async () => {
    if (!formBuy.value.coupon_code) return
    
    if (!product.value || product.value.payment <= 0) {
        return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: $__t('Vui lòng chọn sản phẩm trước khi áp mã giảm giá') })
    }
    
    checkingCoupon.value = true
    try {
        const { data: result } = await axios.post('/api/check-coupon', {
            coupon_code: formBuy.value.coupon_code.trim(),
            cart_total: Number(product.value.payment),
            product_ids: ['item-' + props.groupId]
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
    formBuy.value.coupon_code = ''
    discountAmount.value = 0
    couponApplied.value = false
}

const finalPrice = computed(() => {
    if (!product.value) return 0
    return Math.max(0, product.value.payment - discountAmount.value)
})

const buyItem = async (productParam, formParam) => {
  if (productParam?.code === undefined) return
  
  // Validation logic...
  let errorMsg = ''
  // ... (existing validation) but need to copy existing validation below or use replace properly

  if (productParam.type === 'user' && formParam.Tai_Khoan === '') {
    errorMsg = $__t('Vui lòng nhập tên tài khoản nhận')
  } else if (productParam.type === 'user_pass') {
    if (formParam.Tai_Khoan === '') {
      errorMsg = $__t('Vui lòng nhập tài khoản')
    } else if (formParam.Mat_Khau === '') {
      errorMsg = $__t('Vui lòng nhập mật khẩu')
    }
  } else if (productParam.type === 'gamepass') {
    if (formParam.Tai_Khoan === '') {
      errorMsg = $__t('Vui lòng nhập link GamePass')
    } else if (!formParam.Tai_Khoan.includes('roblox.com') || !formParam.Tai_Khoan.includes('/game-pass/')) {
        errorMsg = $__t('Link GamePass không hợp lệ (phải chứa roblox.com và /game-pass/)')
    }
  }

  if (errorMsg) {
    return Swal.fire('Oops...', errorMsg, 'error')
  }

  const confirm = await Swal.fire({
    icon: 'question',
    title: $__t('Bạn chắc chứ?'),
    text: `${$__t('Bạn sẽ mua vật phẩm')} #${productParam.code} ${$__t('với giá')} ${formatCurrency(finalPrice.value)}?`,
    showCancelButton: true,
    confirmButtonText: $__t('Đồng ý'),
    cancelButtonText: $__t('Hủy'),
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
    didOpen: () => { Swal.showLoading() },
  })

  try {
    const { data: result } = await axios.post('/api/stores/items/' + productParam.code + '/buy', formParam)

    Swal.fire('Great!', result.message, 'success').then(() => {
      window.open('/account/orders/items/' + result.data.code, '_self')
    })
  } catch (error) {
    Swal.fire('Oops...', $catchMessage(error), 'error')
  }
}

// i18n helper passthrough
const __t = (key) => {
  return $__t(key)
}

const loginWithOptions = computed(() => window.loginWith)

// initial mount
onMounted(() => {
  getProducts()
})
</script>

<template>
  <section>
    <div class="mb-5" v-if="group && group.descr">
        <div class="bg-white rounded-lg border border-gray-300 -mx-2 sm:mx-0">
            <div class="p-5 ck-content" v-html="group.descr"></div>
      </div>
    </div>

    <a-spin :spinning="loading">
      <form class="mb-2 mt-5" @submit.prevent="onFilter">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label class="form-label text-black">{{ __t('Chọn mức giá') }}: </label>
            <a-select v-model:value="form.price" :placeholder="__t('Chọn mức giá')" style="width: 100%">
              <a-select-option v-for="item in priceRange" :key="item.value" :value="item.value">{{ item.label }}</a-select-option>
            </a-select>
          </div>

          <div>
            <label class="form-label text-black">{{ __t('Sắp Xếp Theo:') }}</label>
            <a-select v-model:value="form.display_by" style="width: 100%">
              <a-select-option v-for="item in orderBy" :key="item.value" :value="item.value">{{ item.label }}</a-select-option>
            </a-select>
          </div>

          <div>
            <label class="form-label text-black">{{ __t('Tìm kiếm') }}: </label>
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

    <!-- Package Tabs -->
    <div 
      class="flex flex-wrap gap-4 mb-3 pb-3 border-b border-gray-200 items-center" 
      :class="group.is_center == 1 ? 'justify-center' : 'justify-start'"
      v-if="packages && packages.length > 0"
    >
      <!-- "All" Tab -->
      <div 
        @click="form.package_id = ''; onFilter()"
        class="flex items-center gap-3 px-5 py-2 cursor-pointer group transition-all duration-300 border rounded-lg bg-white shadow-sm"
        :class="form.package_id === '' ? 'border-primary text-primary' : 'border-gray-200 text-gray-600 opacity-60 hover:opacity-100'"
      >
        <div class="w-10 h-10 overflow-hidden rounded-lg">
          <img 
            :src="group.image_package || group.image || '/images/svg/spinner.svg'" 
            class="w-full h-full object-cover"
            alt="All Packages"
          />
        </div>
        <span class="text-sm font-bold uppercase">{{ __t('Tất cả') }}</span>
      </div>

      <!-- Individual Package Tabs -->
      <div 
        v-for="pkg in packages" 
        :key="pkg.id"
        @click="form.package_id = pkg.id; onFilter()"
        class="flex items-center gap-3 px-5 py-2 cursor-pointer group transition-all duration-300 border rounded-lg bg-white shadow-sm"
        :class="form.package_id === pkg.id ? 'border-primary text-primary' : 'border-gray-200 text-gray-600 opacity-60 hover:opacity-100'"
      >
        <div class="w-10 h-10 overflow-hidden rounded-lg">
          <img 
            :src="pkg.image || group.image || '/images/svg/spinner.svg'" 
            class="w-full h-full object-cover"
            alt="Package Image"
          />
        </div>
        <span class="text-sm font-bold uppercase">
          {{ pkg.name }}
        </span>
      </div>
    </div>

    <a-row :gutter="18" v-if="loading && products?.length === 0">
      <a-col :xs="12" :sm="8" :lg="6" v-for="item in 4" :key="item">
        <a-card class="mb-3">
          <a-skeleton active></a-skeleton>
        </a-card>
      </a-col>
    </a-row>

    <a-spin v-else :spinning="loading">
      <a-row :gutter="18">
        <a-col
          :xs="12"
          :md="6"
          :lg="4"
          v-for="item in products"
          :key="item.id"
          class="mb-2 mt-2"
        >
          <a-badge-ribbon
            color="red"
            :text="`${item.discount}%`"
            :class="{ hidden: item.discount <= 0 }"
            placement="start"
          >
            <div
              @click="showModal(item)"
              class="border border-primary rounded-lg p-[1px] hover:shadow-md transition duration-150 cursor-pointer"
              style="background: transparent"
            >
              <div class="p-3">
                <div class="relative w-full overflow-hidden rounded-lg" style="aspect-ratio: 16/9;">
                  <img
                    :src="item.image || '/images/svg/spinner.svg'"
                    class="w-full h-full object-cover mx-auto transition-transform duration-300 hover:scale-105"
                    alt=""
                  />
                </div>
                <h4 class="text-left text-lg font-semibold mt-2 text-gray-800 whitespace-normal">
                  {{ item.name.replace('(ROBUX 120H)', '').trim() }}
                </h4>
                <div class="mt-2 text-left text-red-600 font-bold text-base">
                  {{ item.payment_str }}
                </div>
              </div>
            </div>
          </a-badge-ribbon>
        </a-col>
      </a-row>
    </a-spin>

    <a-card v-if="products?.length === 0 && !loading">
      <a-empty :description="__t('Không tìm thấy vật phẩm nào trong nhóm này')"></a-empty>
    </a-card>


    <a-modal
      v-model:open="open"
      :width="750"
      :title="`${__t('Mua Vật Phẩm')} ${product?.name}`"
      @cancel="handleCancel"
      @ok="handleOk"
      :footer="null"
      body-style="{ padding: '18px' }"
      class="buy-item-modal"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-4">
          <div class="mb-3">

            <div v-html="product?.description"></div>
          </div>
          
          <div class="grid grid-cols-1 sm:grid-cols-1 gap-3">
            <div v-if="product?.type === 'gamepass'">
              <label class="form-label text-xs text-black">{{ __t('Link GamePass') }}</label>
              <a-input
                v-model:value="formBuy.Tai_Khoan"
                :placeholder="__t('Nhập link GamePass')"
                aria-autocomplete="off"
                size="middle"
              />
            </div>

            <div v-else>
              <label class="form-label text-xs text-black">{{ __t('Tài Khoản') }}</label>
              <a-input
                v-model:value="formBuy.Tai_Khoan"
                :placeholder="__t('Nhập tài Khoản')"
                aria-autocomplete="off"
                size="middle"
              />
            </div>

            <div v-if="product?.type === 'user_pass'">
              <label class="form-label text-xs text-black">{{ __t('Mật khẩu') }}</label>
              <a-input-password
                v-model:value="formBuy.Mat_Khau"
                :placeholder="__t('Mật khẩu')"
                aria-autocomplete="off"
                size="middle"
              />
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="text-sm font-semibold mb-1 text-black">{{ __t('Thanh toán') }}</div>

          <div>
            <a-input
              v-model:value="formBuy.Lien_He"
              :placeholder="__t('Liên hệ (Zalo, Facebook...)')"
              aria-autocomplete="off"
            />
          </div>

          <div>
            <a-textarea
              v-model:value="formBuy.user_note"
              :placeholder="__t('Ghi chú cho admin')"
              rows="3"
            />
          </div>

          <div>
             <label class="form-label text-xs text-black">{{ __t('Mã giảm giá') }}</label>
             <div class="flex gap-2">
                 <a-input v-model:value="formBuy.coupon_code" :placeholder="__t('Nhập mã giảm giá')" :disabled="couponApplied" aria-autocomplete="off" size="middle"></a-input>
                 <a-button type="primary" :loading="checkingCoupon" @click="checkCoupon" v-if="!couponApplied">{{ __t('Áp Dụng') }}</a-button>
                 <a-button danger @click="cancelCoupon" v-else>{{ __t('Hủy') }}</a-button>
             </div>
          </div>

          <div class="mt-2">
            <div class="text-sm text-black">{{ __t('Tổng Thanh Toán') }}</div>
            <div v-if="couponApplied" class="text-xs text-success mb-1">
                {{ __t('Đã giảm') }}: -{{ formatCurrency(discountAmount) }}
            </div>
            <div class="text-2xl font-extrabold text-red-600">
              {{ formatCurrency(finalPrice) }} <del v-if="couponApplied" class="text-gray-400 text-sm ml-2">{{ product?.payment_str }}</del>
            </div>
          </div>

          <div class="mt-2">
            <a-checkbox v-model:checked="formBuy.isConfirm">
              <span class="text-sm text-black">{{ __t('Tôi cung cấp đúng theo như yêu cầu') }}</span>
            </a-checkbox>
          </div>

          <div class="flex justify-end items-center gap-3 mt-3">
            <a-button @click="handleCancel">{{ __t('Huỷ') }}</a-button>
            <a-button
              type="primary"
              :loading="submitting"
              :disabled="formBuy?.isConfirm !== true"
              @click="buyItem(product, formBuy)"
            >
              {{ __t('Thanh toán') }}
            </a-button>
          </div>
        </div>
      </div>
    </a-modal>
  </section>
</template>

<style scoped>
section {
  margin-top: 20px;
}

:deep(.buy-item-modal .ant-input-password),
:deep(.buy-item-modal .ant-input) {
  background-color: #ffffff;
  border-color: #d1e9ff;
  border-radius: 8px !important;
  height: 42px !important;
}

:deep(.ant-input-affix-wrapper input.ant-input) {
  background-color: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

:deep(.ant-input-affix-wrapper:focus),
:deep(.ant-input-affix-wrapper-focused) {
  border-color: #1890ff !important;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2) !important;
}
</style>