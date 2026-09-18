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
  const { page, total_page, visible_pages } = pagination.value
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
    const { data: result } = await axios.get('/api/stores/accounts', {
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
    const id = getUrlQuery('product_id')
    if (id) {
      setTimeout(() => {
        const element = document.getElementById('card_' + id)
        if (element) {
          element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'center',
          })
        }
      }, 500)
    } else {
      setTimeout(scollUp, 500)
    }

    setTimeout(() => {
      loading.value = false
    }, 600)
    //
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

const finalPrice = computed(() => {
    if (!selectedItem.value) return 0
    return Math.max(0, selectedItem.value.price - discountAmount.value)
})

const checkCoupon = async () => {
    if (!couponCode.value) return
    
    if (!selectedItem.value || selectedItem.value.price <= 0) {
        return Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 5000, timerProgressBar: true, icon: 'error', title: $__t('Vui lòng chọn sản phẩm trước khi áp mã giảm giá') })
    }
    
    checkingCoupon.value = true
    try {
        const { data: result } = await axios.post('/api/check-coupon', {
            coupon_code: couponCode.value.trim(),
            cart_total: Number(selectedItem.value.price),
            product_ids: ['account-' + props.groupId] // Use group ID for checking
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
    openBuyModal.value = true
}

const handleCancel = () => {
    openBuyModal.value = false
    selectedItem.value = null
}

const submitting = ref(false)

const confirmBuy = async () => {
  if (!selectedItem.value) return

  // Logic moved from buyItem
  const item = selectedItem.value
  
  submitting.value = true

  try {
    const { data: result } = await axios.post(
      '/api/stores/accounts/' + item.code + '/buy',
      {
          coupon_code: couponApplied.value ? couponCode.value : null
      }
    )

    // Store pending toast for next page
    sessionStorage.setItem('pending_toast', JSON.stringify({
        icon: 'success',
        title: 'Thành công',
        message: result.message
    }));

    window.open('/account/orders/accounts/' + result.data.code, '_self')

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
  }
}

// Replaced buyItem with openModal logic wrapper
const buyItem = (item) => {
    openModal(item)
}

const redirectTo = (item) => {
  window.open('/tai-khoan/thong-tin/' + item.code, '_self')
}

const randomColor = () => {
  const colors = [
    '#C70039',
    '#2E4374',
    '#219C90',
    '#EE9322',
    '#5B0888',
    '#004225',
    '#9400FF',
  ]

  return colors[Math.floor(Math.random() * colors.length)]
}

const isString = (value) => {
  return typeof value === 'string' || value instanceof String
}

const scollUp = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
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

    <div>
      <a-row v-if="!loading" :gutter="18">
        <a-col :xs="24" :md="8" :lg="6" v-for="item in products" :key="item.id" class="mb-2 mt-2 cursor-pointer" :id="`card_${item.code}`">
          <a-badge-ribbon color="red" :text="`-${item.discount}%`" :class="{ hidden: item.discount <= 0 }" placement="start">
            <a-badge-ribbon color="pink" :text="`MS: ${item.code}`" placement="end">
              <div class="border border-primary rounded-lg p-[1px] bg-white dark:bg-gray-800" :class="{ 'card-active': isCardActive(item.code) }">
                <div class="relative w-full overflow-hidden rounded-t-lg" style="aspect-ratio: 16/9;">
                  <img
                    :src="item.image || '/images/svg/spinner.svg'"
                    class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                    :alt="item.name"
                  />
                </div>
                <div class="p-3">
                  <div class="grid grid-cols-2 mb-3 gap-3">
                    <div v-for="(hl, hlx) in item.highlights" :key="hlx" class="lg:col-span-1">
                      <a-tag v-if="isString(hl)" :color="randomColor()" class="w-full font-bold" style="white-space: normal">
                        <i class="fa-solid fa-check-circle"></i> {{ hl }}
                      </a-tag>
                      <a-tag v-else-if="
                        hl?.name !== undefined && hl?.value !== 'undefined'
                      " :color="randomColor()" class="w-full font-bold" style="white-space: normal">
                        <i class="fa-solid fa-star"></i> {{ hl.name }}:
                        {{ hl.value }}
                      </a-tag>
                      <a-tag v-else-if="hl?.[0] !== undefined" :color="randomColor()" class="w-full font-bold" style="white-space: normal">
                        <i class="fa-solid fa-check-circle"></i> {{ hl[0] }}
                      </a-tag>
                    </div>
                  </div>
                  <div class="text-center grid grid-cols-2 gap-3">
                    <a-tooltip :title="item.discount !== 0
                      ? `${__t('Giá gốc')} ${formatCurrency(item.price)}`
                      : ''
                      ">
                      <a-button type="dashed" block @click="buyItem(item)" class="dark:text-white"><i class="fas fa-credit-card me-2"></i>
                        {{ item.price_str }}</a-button>
                    </a-tooltip>
                    <a-tooltip :title="item.name">
                      <button class="btn btn-sm btn-primary w-full" @click="redirectTo(item)">
                        <i class="fas fa-shopping-cart me-2"></i><span>{{ __t('Chi tiết') }}</span>
                      </button>
                    </a-tooltip>
                  </div>
                </div>
              </div>
            </a-badge-ribbon>
          </a-badge-ribbon>
        </a-col>
      </a-row>
      <a-row v-else :gutter="18">
        <a-col :xs="12" :sm="8" :lg="6" v-for="item in 16" :key="item">
          <a-card class="mb-3 h-[303px]">
            <a-skeleton active></a-skeleton>
          </a-card>
        </a-col>
      </a-row>
    <a-card v-if="products?.length === 0 && !loading">
      <a-empty :description="__t('Không tìm thấy tài khoản nào trong nhóm này')"></a-empty>
    </a-card>
    <div class="text-center mt-3">
      <a-spin :spinning="loading">
        <ul class="list-none">
          <li class="inline-block">
            <a href="javascript:void(0)" @click="onPrevPage()"
              class="flex items-center justify-center w-6 h-6 bg-slate-100 dark:bg-slate-700 dark:hover:bg-black-500 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 hover:bg-black-500 hover:text-white text-sm font-Inter font-medium transition-all duration-300 relative top-[2px] pl-2">
              <i class="fas fa-arrow-left"></i>
            </a>
          </li>
          <li v-for="page in visiblePages" :key="page" class="inline-block">
            <a href="javascript:void(0)" @click="goToPage(page)"
              class="flex items-center justify-center w-6 h-6 bg-slate-100 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 hover:bg-black-500 hover:text-white text-sm font-Inter font-medium transition-all duration-300"
              :class="{ 'p-active': pagination.page === page }">
              {{ page !== -1 ? page : '...' }}</a>
          </li>
          <li class="inline-block">
            <a href="javascript:void(0)" @click="onNextPage()"
              class="flex items-center justify-center w-6 h-6 bg-slate-100 dark:bg-slate-700 dark:hover:bg-black-500 text-slate-800 dark:text-white rounded mx-[3px] sm:mx-1 hover:bg-black-500 hover:text-white text-sm font-Inter font-medium transition-all duration-300 relative top-[2px]">
              <i class="fas fa-arrow-right"></i>
            </a>
          </li>
        </ul>
      </a-spin>
    </div>
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
            
            <div>
                 <label class="form-label text-xs text-black">{{ __t('Mã giảm giá') }}</label>
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
                             <del v-if="finalPrice < selectedItem?.price" class="text-gray-400 text-base">{{ formatCurrency(selectedItem?.price) }}</del>
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
<style>
.card-active {
  border: 2px solid yellowgreen !important;
  box-shadow: 0 0 0 2px yellowgreen !important;
}
</style>