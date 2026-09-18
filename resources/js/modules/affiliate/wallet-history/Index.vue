<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'

import moment from 'moment'
import axios from 'axios'

const search = ref('')

const columns = [

  {
    title: 'Trạng Thái',
    dataIndex: 'status',
    align: 'left', // User requested left alignment for "Lịch Sử Rút Tiền", assuming for this too or default
  },
  {
    title: 'Hoa hồng ban đầu',
    dataIndex: 'balance_before',
    align: 'right',
  },
  {
    title: 'Hoa hồng thay đổi',
    dataIndex: 'amount',
    align: 'center',
  },
  {
    title: 'Hoa hồng hiện tại',
    dataIndex: 'balance_after',
    align: 'right',
  },
  {
    title: 'Nội Dung',
    dataIndex: 'reason', 
  },
  {
    title: 'Thời gian',
    dataIndex: 'created_at',
    align: 'right',
  },
]

const queryData = (params) => {
  return window.axios.get('/api/users/affiliates/history', {
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
        config: res.data?.config ?? {}
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
const config = computed(() => data.value?.config ?? {})

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
  <div class="wallet-history">
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

                <template v-if="column.dataIndex === 'status'">
                    <!-- Logic: If commission type is product, show dynamic status. Else show Completed (implied) -->
                    <template v-if="config['commission_type'] === 'order'">
                        <a-tag :color="record.display_status_code === 'danger' ? 'red' : (record.display_status_code === 'warning' ? 'orange' : 'green')">
                            {{ record.display_status_text || 'Hoàn Thành' }}
                        </a-tag> 
                    </template>
                    <template v-else>
                         <a-tag color="green">Hoàn Thành</a-tag>
                    </template>
                </template>
                <template v-if="column.dataIndex === 'balance_before'">
                    {{ formatCurrency(text) }}
                </template>
                <template v-if="column.dataIndex === 'amount'">
                    <span :class="record.display_status_code === 'danger' ? 'text-red-500' : (record.display_status_code === 'warning' ? 'text-orange-500' : 'text-green-500')" class="font-bold">
                        <template v-if="record.display_status_code === 'warning'">
                            ~ {{ formatCurrency(text) }}
                        </template>
                        <template v-else-if="record.display_status_code === 'danger'">
                            - {{ formatCurrency(text) }}
                        </template>
                        <template v-else>
                            {{ record.user_action === 'increment' ? '+' : '-' }} {{ formatCurrency(text) }}
                        </template>
                    </span>
                </template>
                 <template v-if="column.dataIndex === 'balance_after'">
                    {{ formatCurrency(text) }}
                </template>
                <template v-if="column.dataIndex === 'reason'">
                     <span v-if="record.type == 'commission'">
                        Hoa hồng thành viên ({{ record.sys_note }})
                     </span>
                     <span v-else-if="record.type == 'affiliate'">
                        Rút số dư hoa hồng #{{ record.order_id }}
                     </span>
                     <span v-else>
                        {{ record.user_note }}
                     </span>
                </template>
                <template v-if="column.dataIndex === 'created_at'">
                    {{ formatDate(text) }}
                </template>
            </template>
          </a-table>
      </div>
  </div>
</template>
