<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'
import axios from 'axios'
import moment from 'moment'
import { ReloadOutlined } from '@ant-design/icons-vue'

const search = ref('')
const columns = computed(() => {
  const t = (key) => typeof window.$__t === 'function' ? window.$__t(key) : key;
  return [
    {
      title: 'ID',
      dataIndex: 'id',
      sorter: true,
    },
    {
      title: t('Mã Giao Dịch'),
      dataIndex: 'transaction_code',
      width: '13%'
    },
    {
      title: t('Số Serial'),
      dataIndex: 'serial',
    },
    {
      title: t('Mã Thẻ'),
      dataIndex: 'code',
    },
    {
      title: t('Thực Nhận'),
      dataIndex: 'amount',
    },
    {
      title: t('Mệnh Giá'),
      dataIndex: 'value',
    },
    {
      title: t('Ghi Chú'),
      dataIndex: 'content',
    },
    {
      title: t('Trạng Thái'),
      dataIndex: 'status',
    },
    {
      title: t('Tài Khoản'),
      dataIndex: 'username',
    },
    {
      title: t('Thời Gian'),
      dataIndex: 'created_at',
      sorter: true,
    },
    {
      title: t('Cập Nhật'),
      dataIndex: 'updated_at',
      sorter: true,
    },
  ]
})

const queryData = (params) => {
  return axios.get('/api/accounts/card-list', {
    params: {
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

const formatCurrency = (number, currency = 'VND', maxinum = 2) => {
  return $formatCurrency(number, currency, maxinum)
}

const dataSource = computed(() => data.value?.data ?? [])

const pagination = computed(() => ({
  current: current.value,
  total: totalPage.value,
  pageSize: pageSize.value,
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
</script>

<template>
  <div class="overflow-auto">
    <div class="mb-5 flex flex-col gap-5 md:flex-row md:items-center">
      <div class="ltr:ml-auto rtl:mr-auto flex gap-x-2">
        <a-input v-model:value="search" placeholder="Search..." />
        <a-button type="primary" :loading="loading" @click="refresh">
          <i class="fas fa-search"></i>
        </a-button>
      </div>
    </div>
    <a-table :dataSource="dataSource" :columns="columns" :loading="loading" :pagination="pagination" size="small" @change="handleTableChange" class="font-medium whitespace-nowrap">
      <template #bodyCell="{ column, text, record }">
        <template v-if="column.dataIndex === 'transaction_code'">
          <b style="color: #191D88" class="copy cursor-pointer" :data-clipboard-text="text">{{ text ?? '- KO CÓ -' }}</b>
        </template>
        <template v-if="column.dataIndex === 'balance_before'">{{
          formatCurrency(text)
        }}</template>
        <template v-if="column.dataIndex === 'amount'">
          <span :class="text === 0 ? 'text-danger-600' : 'text-green-600'">{{ formatCurrency(text) }}</span>
        </template>
        <template v-if="column.dataIndex === 'status'">
          <a-tag v-if="text === 'Processing'" color="warning">
            {{ record.status_str }}
          </a-tag>
          <a-tag v-else-if="text === 'Cancelled'" color="red">
            {{ record.status_str }}
          </a-tag>
          <a-tag v-else-if="text === 'Error'" color="red">
            {{ record.status_str }}
          </a-tag>
          <a-tag v-else-if="text === 'Completed'" color="success">
            {{ record.status_str }}
          </a-tag>
        </template>
        <template v-if="column.dataIndex === 'value'">{{
          formatCurrency(text)
        }}</template>
        <template v-if="column.dataIndex === 'created_at'">{{
          formatDate(text)
        }}</template>
        <template v-if="column.dataIndex === 'updated_at'">{{
          formatDate(text)
        }}</template>
      </template>
    </a-table>
  </div>
</template>
