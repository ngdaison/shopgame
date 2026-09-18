<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'
import moment from 'moment'

const search = ref('')
const columns = [
  {
    title: 'Tài khoản',
    dataIndex: 'input_user',
  },
  {
    title: 'Trạng thái',
    dataIndex: 'status',
  },
  {
    title: 'Thanh toán',
    dataIndex: 'payment',
  },
  {
    title: 'Tên vật phẩm',
    dataIndex: 'name',
  },
  {
    title: 'Mã Đơn Hàng',
    dataIndex: 'code',
  },
  {
    title: 'Ngày mua',
    dataIndex: 'created_at',
  },
  {
    title: 'Thao tác',
    dataIndex: 'action',
    fixed: 'right',
    width: 80,
  },
]

const queryData = (params) => {
  return window.axios.get('/account/orders/boosting', {
    params: {
        search: search.value,
        ...params,
    },
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
  })
}

const { data, current, totalPage, loading, pageSize, run, refresh } =
  usePagination(queryData, {
    formatResult: (res) => {
      const paginator = res.data.data
      return {
        data: paginator.data ?? [],
        totalPage: paginator.total ?? 0,
      }
    },
    defaultParams: [
      {
        sort_by: 'id',
        sort_type: 'desc',
        limit: 10,
      },
    ],
    pagination: {
      currentKey: 'page',
      pageSizeKey: 'limit',
    },
  })

const formatDate = (date, format = null) => {
  if (date === null) return ''
  const parsedDate = moment(date, moment.ISO_8601)
  if (!parsedDate.isValid()) return ''
  return parsedDate.format(format || 'HH:mm - DD/MM/YYYY')
}

const formatCurrency = (value) => {
   if (typeof window.$formatCurrency === 'function') {
       return window.$formatCurrency(value);
   }
   return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

const dataSource = computed(() => data.value?.data ?? [])

const pagination = computed(() => ({
  page: current.value,
  total: totalPage.value,
  limit: pageSize.value,
}))

const handleTableChange = (pag, filters, sorter) => {
  run({
    page: pag?.current,
    limit: pag.pageSize,
    sort_by: sorter.field,
    sort_type: sorter.order === 'ascend' ? 'asc' : 'desc',
    ...filters,
  })
}

const viewDetail = (record) => {
    window.location.href = `/account/orders/boosting/${record.code}`
}
</script>

<template>
  <div class="boosting-order-index">
    <div class="card">
      <header class="card-header noborder">
        <h4 class="card-title">Lịch sử cày thuê</h4>
      </header>
      <div class="card-body px-6 pb-6">
        <div class="mb-5 flex flex-col gap-5 md:flex-row md:items-center">
          <div class="ltr:ml-auto rtl:mr-auto flex gap-x-2">
            <a-input v-model:value="search" placeholder="Tìm theo mã đơn" />
            <a-button type="primary" class="!bg-primary-500" :loading="loading" @click="refresh">
              Tìm kiếm
            </a-button>
          </div>
        </div>
        <div>
          <a-table 
            :dataSource="dataSource" 
            :columns="columns" 
            :loading="loading" 
            :pagination="pagination" 
            size="small" 
            @change="handleTableChange" 
            class="font-medium whitespace-nowrap"
            :scroll="{ x: 'max-content' }"
          >
            <template #bodyCell="{ column, text, record }">
               <template v-if="column.dataIndex === 'name'">
                    <span class="font-medium text-slate-600 dark:text-slate-300" :title="text">
                        {{ text.length > 50 ? text.substring(0, 50) + '...' : text }}
                    </span>
               </template>
                <template v-if="column.dataIndex === 'code'">
                    <span class="font-semibold">{{ text }}</span>
                </template>
                <template v-if="column.dataIndex === 'created_at'">
                    {{ formatDate(text) }}
                </template>
                 <template v-if="column.dataIndex === 'status'">
                     <a-tag color="green" v-if="['completed', '1', 'success'].includes(String(record.status).toLowerCase())">Hoàn thành</a-tag>
                     <a-tag color="red" v-else-if="['cancelled', 'canceled', '2', 'error'].includes(String(record.status).toLowerCase())">Đã hoàn tiền</a-tag>
                     <a-tag color="red" v-else-if="['destroyed'].includes(String(record.status).toLowerCase())">Đã bị hủy</a-tag>
                     <a-tag color="orange" v-else-if="['pending', 'assigned', '0'].includes(String(record.status).toLowerCase())">Chờ xử lý</a-tag>
                     <a-tag color="blue" v-else-if="['processing'].includes(String(record.status).toLowerCase())">Đang xử lý</a-tag>
                     <a-tag color="cyan" v-else-if="['active'].includes(String(record.status).toLowerCase())">Đang hoạt động</a-tag>
                     <a-tag color="red" v-else>{{ record.status }}</a-tag>
                 </template>
                <template v-if="column.dataIndex === 'input_user'">
                   <div class="space-y-1">
                     <div>{{ text }}</div>
                   </div>
                </template>
                <template v-if="column.dataIndex === 'payment'">
                    <span class="font-medium" :class="['cancelled', 'canceled', '2', 'error', 'destroyed'].includes(String(record.status).toLowerCase()) || record.payment <= 0 ? 'text-red-500' : 'text-green-500'">
                        {{ formatCurrency(record.payment) }}
                    </span>
                </template>
               <template v-if="column.dataIndex === 'action'">
                 <a-button size="small" @click="viewDetail(record)">Chi tiết</a-button>
               </template>
            </template>
          </a-table>
        </div>
      </div>
    </div>
  </div>
</template>
