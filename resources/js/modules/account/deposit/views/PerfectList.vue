<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'
import axios from 'axios'
import moment from 'moment'
import { message } from 'ant-design-vue';

const search = ref('')
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    sorter: true,
  },
  {
    title: 'Thao Tác',
    dataIndex: 'action',
  },
  {
    title: 'Hoá Đơn',
    dataIndex: 'trans_id',
  },
  {
    title: 'Số Tiền',
    dataIndex: 'amount',
  },
  {
    title: 'Ghi Chú',
    dataIndex: 'content',
  },
  {
    title: 'Trạng Thái',
    dataIndex: 'status',
  },
  {
    title: 'Thời Gian',
    dataIndex: 'created_at',
    sorter: true,
  },
  {
    title: 'Cập Nhật',
    dataIndex: 'updated_at',
    sorter: true,
  },
]

const queryData = (params) => {
  return axios.get('/api/users/invoices', {
    params: {
      type: 'perfect_money',
      search: search.value,
      ...params,
    },
  })
}

const { data, current, totalPage, loading, pageSize, run, refresh } =
  usePagination(queryData, {
    formatResult: (res) => {
      const { data, meta } = res.data.data

      return {
        data: data ?? [],
        totalPage: meta.total_rows ?? 0,
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
  if (date === null) {
    return ''
  }
  const parsedDate = moment(date, moment.ISO_8601)
  if (!parsedDate.isValid()) {
    return ''
  }
  if (format) {
    return parsedDate.format(format)
  }
  return parsedDate.format('HH:mm:ss - DD/MM/YYYY')
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

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

const openPayment = (record) => {
    if (record.payment_details && record.payment_details.url_payment) {
        window.open(record.payment_details.url_payment, '_blank');
    } else {
        message.info('Không tìm thấy liên kết thanh toán.');
    }
}
</script>

<template>
  <div>
    <a-card title="Lịch sử của Nạp Tiền Bằng Perfect Money">
      <div class="overflow-auto">
        <div class="mb-5 flex flex-col gap-5 md:flex-row md:items-center">
          <div class="ltr:ml-auto rtl:mr-auto flex gap-x-2">
            <a-input v-model:value="search" placeholder="Tìm kiếm" />
            <a-button type="primary" :loading="loading" @click="refresh">
              Tìm kiếm
            </a-button>
          </div>
        </div>
        <a-table :dataSource="dataSource" :columns="columns" :loading="loading" :pagination="pagination" size="small" @change="handleTableChange" class="font-medium whitespace-nowrap">
          <template #bodyCell="{ column, text, record }">
            <template v-if="column.dataIndex === 'action'">
              <a-button v-if="record.status === 'processing' && record.payment_details?.url_payment" size="small" @click="openPayment(record)">
                <i class="fas fa-share me-2"></i> Thanh Toán
              </a-button>
              <span v-else></span>
            </template>
            <template v-if="column.dataIndex === 'amount'">
              <strong class="text-green-700">{{ formatCurrency(text) }}</strong>
            </template>
            <template v-if="column.dataIndex === 'status'">
              <span v-if="text === 'processing'" class="font-bold text-warning-600">Đang Chờ</span>
              <span v-else-if="text === 'completed'" class="font-bold text-green-700">Hoàn Thành</span>
              <span v-else-if="text === 'cancelled'" class="font-bold text-red-700">Bị Huỷ/Xoá</span>
              <span v-else class="font-bold text-gray-700">{{ text }}</span>
            </template>
             <template v-if="column.dataIndex === 'content'">
               <span class="text-wrap">{{ text }}</span>
            </template>
            <template v-if="column.dataIndex === 'created_at'">{{
              formatDate(text)
              }}</template>
            <template v-if="column.dataIndex === 'updated_at'">{{
              formatDate(text)
              }}</template>
          </template>
        </a-table>
      </div>
    </a-card>
  </div>
</template>
