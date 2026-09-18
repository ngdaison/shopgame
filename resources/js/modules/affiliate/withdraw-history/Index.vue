<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'

import moment from 'moment'
import axios from 'axios'

const search = ref('')

const columns = [
  {
    title: 'Hoa hồng ban đầu',
    dataIndex: 'balance_before',
    align: 'left',
  },
  {
    title: 'Số Tiền',
    dataIndex: 'amount',
    align: 'left',
  },
  {
    title: 'Trạng Thái',
    dataIndex: 'status',
    align: 'left',
  },

  {
    title: 'Thông Tin',
    dataIndex: 'info',
    align: 'left',
  },
  {
    title: 'Ghi Chú',
    dataIndex: 'note',
    align: 'left',
  },
  {
    title: 'Mã Giao Dịch',
    dataIndex: 'order_id',
    align: 'left',
  },
  {
    title: 'Thời Gian',
    dataIndex: 'created_at',
    align: 'left',
  },
]

const queryData = (params) => {
  return window.axios.get('/api/users/affiliates/withdraw-history', {
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
      const paginator = res.data?.data || {}
      return {
        data: paginator.data ?? [],
        totalPage: paginator.total ?? 0,
      }
    },
    defaultParams: [
      {
         limit: 10,
      },
    ],
    pagination: {
      currentKey: 'page',
      pageSizeKey: 'limit',
    },
  })

const formatDate = (date) => {
  if (!date) return ''
  return moment(date).format('HH:mm DD/MM/YYYY')
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
  })
}
</script>

<template>
  <div class="withdraw-history">
      <div class="mb-4 flex flex-col gap-5 md:flex-row md:items-center justify-end">
          <div class="flex gap-x-2">
            <a-input v-model:value="search" placeholder="Tìm theo Mã GD" allow-clear />
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
                <template v-if="column.dataIndex === 'order_id'">
                    <span class="font-semibold text-primary-500">#{{ text }}</span>
                </template>
                <template v-if="column.dataIndex === 'created_at'">
                    {{ formatDate(text) }}
                </template>
                 <template v-if="column.dataIndex === 'balance_before'">
                    {{ formatCurrency(record.balance_before) }}
                </template>
                 <template v-if="column.dataIndex === 'amount'">
                    {{ formatCurrency(text) }}
                </template>
                <template v-if="column.dataIndex === 'info'">
                     <template v-if="record.payment_info && record.payment_info.method === 'wallet'">
                        <span>Ví Tài Khoản</span>
                     </template>
                     <template v-else>
                        <div class="text-start">
                             <div class="fw-bold">{{ record.payment_info?.bank_name || 'N/A' }}</div>
                             <div v-if="record.payment_info?.account_number" class="text-muted text-xs">
                                 {{ record.payment_info.account_number }}
                             </div>
                        </div>
                     </template>
                </template>
                <template v-if="column.dataIndex === 'status'">
                    <!-- Matches logic: Cancelled/Declined = 'Đã bị hủy' -->
                    <a-tag color="green" v-if="['completed', 'success'].includes(String(record.status).toLowerCase())">Hoàn thành</a-tag>
                    <a-tag color="orange" v-else-if="['pending'].includes(String(record.status).toLowerCase())">Chờ xử lý</a-tag>
                    <a-tag color="red" v-else-if="['cancelled', 'declined', 'rejected', 'destroyed'].includes(String(record.status).toLowerCase())">Đã bị hủy</a-tag>
                    <a-tag color="blue" v-else-if="['processing'].includes(String(record.status).toLowerCase())">Đang xử lý</a-tag>
                    <a-tag color="default" v-else>{{ record.status }}</a-tag>
                </template>

                <template v-if="column.dataIndex === 'note'">
                     {{ record.sys_note || record.user_note || '' }}
                </template>
            </template>
          </a-table>
      </div>
  </div>
</template>
