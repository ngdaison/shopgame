<script setup>
import { computed, ref } from 'vue'
import { usePagination } from 'vue-request'
import axios from 'axios'
import moment from 'moment'

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
      title: t('Tài Khoản'),
      dataIndex: 'username',
    },
    {
      title: t('Nội Dung'),
      dataIndex: 'content',
    },
    {
      title: t('Địa Chỉ IP'),
      dataIndex: 'ip_address',
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
  return axios.get('/api/accounts/histories', {
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
      <template #bodyCell="{ column, text }">
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
