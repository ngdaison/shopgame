<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { usePagination } from 'vue-request'
import moment from 'moment'
import { notification } from 'ant-design-vue'

const activeTab = ref('all')


// Columns for Nick V1 & V2
const columnsAccount = [
  {
    title: 'Sản phẩm',
    dataIndex: 'product_name',
    width: 200,
  },
  {
    title: 'Tên Tài Khoản',
    dataIndex: 'username',
  },
  {
    title: 'Trạng thái',
    dataIndex: 'status',
    align: 'center',
    width: 120,
  },
  {
    title: 'Thanh toán',
    dataIndex: 'buyer_paym',
  },
  {
    title: 'Mã đơn hàng',
    dataIndex: 'buyer_code',
  },
  {
    title: 'Mã số',
    dataIndex: 'code',
  },
  {
    title: 'Thao tác',
    dataIndex: 'action',
    fixed: 'right',
    width: 100,
    align: 'center',
  },
]

// Columns for Bulk Orders Table
const columnsBulk = [
  {
    title: 'Sản phẩm',
    dataIndex: 'name', 
  },
  {
    title: 'Số lượng',
    dataIndex: 'quantity',
    align: 'center',
  },
  {
    title: 'Trạng thái',
    dataIndex: 'status',
    align: 'center',
    width: 120,
  },
  {
    title: 'Thanh toán',
    dataIndex: 'payment',
  },
  {
    title: 'Mã đơn hàng',
    dataIndex: 'code',
  },

  {
    title: 'Thao tác',
    dataIndex: 'action',
    fixed: 'right',
    width: 100,
    align: 'center',
  },
]

const queryData = (params) => {
  return window.axios.get('/account/orders/accounts', {
    params: {
        tab: activeTab.value,

        ...params,
    },
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
  })
}

// Stats state
const stats = ref({
    total: 0,
    payment: 0,
    payment_in_month: 0
})

const { data, current, totalPage, loading, pageSize, run, refresh } =
  usePagination(queryData, {
    formatResult: (res) => {
      // res.data is { data: PaginatorObj, stats: StatsObj, success: true }
      // Update stats if present
      if (res.data.stats) {
          stats.value = res.data.stats
      }
      const paginator = res.data.data
      return {
        data: paginator.data ?? [],
        totalPage: paginator.total ?? 0,
      }
    },
    defaultParams: [
      {
        page: 1,
        limit: 12, 
      },
    ],
    pagination: {
      currentKey: 'page',
      pageSizeKey: 'limit',
    },
  })

const formatDate = (date, format = null) => {
  if (!date) return ''
  const parsedDate = moment(date)
  if (!parsedDate.isValid()) return ''
  return parsedDate.format(format || 'DD/MM/YYYY HH:mm')
}

const isNumeric = (n) => {
    if (n === null || n === undefined) return false;
    // Strip commas and spaces
    const clean = String(n).replace(/[, ]/g, '');
    return !isNaN(parseFloat(clean)) && isFinite(clean);
}

const formatCurrency = (value) => {
   if (!isNumeric(value)) {
       return '0 ₫';
   }
   // normalize
   const num = parseFloat(String(value).replace(/[, ]/g, ''));

   if (typeof window.$formatCurrency === 'function') {
       return window.$formatCurrency(num);
   }
   return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);
}

const getPaymentColor = (value) => {
    if (!isNumeric(value)) return 'text-red-500';
    const num = parseFloat(String(value).replace(/[, ]/g, ''));
    return num >= 0 ? 'text-green-500' : 'text-red-500';
}


const dataSource = computed(() => {
    if (loading.value) return [];
    return data.value?.data ?? []
})

const pagination = computed(() => ({
  current: current.value,
  total: totalPage.value,
  pageSize: pageSize.value,
}))

const handleTableChange = (pag) => {
  run({
    page: pag?.current,
    limit: pag.pageSize,
    tab: activeTab.value
  })
}

const handleTabChange = (key) => {
    activeTab.value = key;
    data.value = null; // Clear old data to prevent column mismatch
    run({
        page: 1,
        limit: pageSize.value,
        tab: key
    })
}

const viewDetail = (code) => {
    window.location.href = `/account/orders/accounts/${code}`
}

const getValidImage = (img) => {
    return img || '/images/logo/logo-square.png' // Fallback image
}

const extractIdentifier = (str) => {
    if (!str) return ''
    return str.split(':')[0]
}
</script>

<template>
  <div class="account-order-index">
    <div class="card">
      <header class="card-header noborder">
        <h4 class="card-title">Danh sách tài khoản</h4>
      </header>
      <div class="card-body px-6 pb-6">
        
        <!-- Tabs -->
        <a-tabs v-model:activeKey="activeTab" @change="handleTabChange" class="mb-5">
            <a-tab-pane key="all" tab="Tài Khoản" />
            <a-tab-pane key="bulk" tab="Bulk Orders" />
        </a-tabs>



        <!-- Content -->
        <a-spin :spinning="loading">
            
            <!-- Empty State -->
            <div v-if="!loading && dataSource.length === 0" class="space-y-6">
                <div>
                    <img :src="'/images/svg/empty.svg'" class="mx-auto h-[100px] w-[150px] object-cover" alt="empty">
                </div>
                <div class="text-center">
                    <h1 class="mt-3 text-2xl font-semibold">Bạn chưa mua tài khoản nào!</h1>
                </div>
                <div class="text-center">
                    <a href="/" class="btn btn-primary mt-3">Mua Ngay</a>
                </div>
            </div>

            <!-- Table -->
            <div v-else>
                <a-table 
                    :dataSource="dataSource" 
                    :columns="activeTab === 'bulk' ? columnsBulk : columnsAccount" 
                    :pagination="pagination" 
                    size="small"
                    rowKey="id"
                    class="ant-table-striped font-medium whitespace-nowrap"
                    :scroll="{ x: 'max-content' }"
                    @change="handleTableChange"
                >
                    <template #bodyCell="{ column, text, record }">
                        <!-- Shared Status Column -->
                        <template v-if="column.dataIndex === 'status'">
                             <a-tag color="orange" v-if="(record.status && ['cancelled', 'canceled', '2', 'error'].includes(String(record.status).trim().toLowerCase())) || (record.buyer_code && String(record.buyer_code).trim().toUpperCase() === 'REFUNDED')">Đã hoàn tiền</a-tag>
                             <a-tag color="red" v-else-if="record.status && ['destroyed'].includes(String(record.status).trim().toLowerCase())">Đã bị hủy</a-tag>
                             <a-tag color="blue" v-else-if="(record.status && ['pending', '0', 'assigned', 'processing'].includes(String(record.status).trim().toLowerCase())) || record.username === 'PREORDER_WAITING'">Đang xử lý</a-tag>
                             <a-tag color="cyan" v-else-if="record.status && ['active'].includes(String(record.status).trim().toLowerCase())">Đang hoạt động</a-tag>
                             <a-tag color="green" v-else>Hoàn thành</a-tag>
                        </template>

                        <!-- Account Columns -->
                        <template v-if="column.dataIndex === 'product_name'">
                            <div class="flex items-center space-x-2">
                                <img :src="getValidImage(record.image)" 
                                    class="h-10 w-10 rounded border border-gray-200 object-cover" 
                                    :alt="record.product_name || record.name" />
                                <span class="font-bold">{{ record.product_name || record.name || record.service_name || 'Product' }}</span>
                            </div>
                        </template>
                        <template v-if="column.dataIndex === 'username'">
                             <span class="font-semibold" :title="text">{{ extractIdentifier(text) }}</span>
                        </template>
                        <template v-if="column.dataIndex === 'buyer_code'">
                            <span class="font-bold text-gray-600">{{ text || (!isNumeric(record.buyer_paym) ? record.buyer_paym : '') }}</span>
                        </template>
                        <template v-if="column.dataIndex === 'buyer_paym'">
                            <span :class="`font-bold ${getPaymentColor(record.buyer_paym)}`">
                                {{ isNumeric(record.buyer_paym) ? formatCurrency(record.buyer_paym) : '0 ₫' }}
                            </span>
                        </template>

                        <!-- Bulk Columns -->
                         <template v-if="column.dataIndex === 'name'">
                             <div class="flex items-center space-x-2">
                                <img :src="getValidImage(record.image)" 
                                     class="h-10 w-10 rounded border border-gray-200 object-cover" 
                                     :alt="record.name" />
                                <span class="font-bold">{{ record.product_name || record.name || record.service_name || 'Product' }}</span>
                            </div>
                        </template>
                         <template v-if="column.dataIndex === 'quantity'">
                            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-blue-600 rounded-md">
                                {{ record.orders_count || record.items_count || record.quantity || 1 }}
                            </span>
                        </template>
                         <template v-if="column.dataIndex === 'payment'">
                            <span :class="`font-bold ${getPaymentColor(record.payment)}`">
                                {{ isNumeric(record.payment) ? formatCurrency(record.payment) : '0 ₫' }}
                            </span>
                        </template>
                        <template v-if="column.dataIndex === 'code'">
                            <span class="font-bold text-gray-600">{{ text || (!isNumeric(record.payment) ? record.payment : '') }}</span>
                        </template>


                        <!-- Action -->
                        <template v-if="column.dataIndex === 'action'">
                            <a-button type="primary" size="small" @click="viewDetail(activeTab === 'bulk' ? record.code : (record.buyer_code || record.code))">
                                Chi tiết
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>

        </a-spin>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
