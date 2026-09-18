<template>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-50 dark:bg-zinc-800">
        <tr>
          <th class="px-4 py-3 font-semibold">Mã đơn</th>
          <th class="px-4 py-3 font-semibold">Loại game</th>
          <th class="px-4 py-3 font-semibold">Số lượng</th>
          <th class="px-4 py-3 font-semibold">Trạng thái</th>
          <th class="px-4 py-3 font-semibold">Thời gian</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
        <tr v-for="item in records.data" :key="item.id">
          <td class="px-4 py-3">#{{ item.code }}</td>
          <td class="px-4 py-3">{{ item.name }}</td>
          <td class="px-4 py-3">{{ item.amount }} {{ item.unit }}</td>
          <td class="px-4 py-3">
            <span v-if="item.status === 'Pending'" class="badge badge-warning">Đang chờ</span>
            <span v-else-if="item.status === 'Approved'" class="badge badge-success">Đã duyệt</span>
            <span v-else class="badge badge-danger">Từ chối</span>
          </td>
          <td class="px-4 py-3">{{ formatDate(item.created_at) }}</td>
        </tr>
        <tr v-if="records.data && records.data.length === 0">
          <td colspan="5" class="px-4 py-10 text-center text-gray-500">
            Chưa có yêu cầu rút thưởng nào.
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="records.last_page > 1" class="mt-4 flex justify-center space-x-2">
      <button 
        v-for="page in records.last_page" 
        :key="page"
        @click="fetchHistories(page)"
        class="px-3 py-1 border rounded"
        :class="records.current_page === page ? 'bg-primary text-white' : 'bg-white text-black'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const records = ref({ data: [] })

const fetchHistories = (page = 1) => {
  axios.get(`/api/users/withdraws?page=${page}`)
    .then(res => {
      records.value = res.data
    })
    .catch(err => {
      console.error(err)
    })
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('vi-VN')
}

onMounted(() => {
  fetchHistories()
})
</script>

<style scoped>
.badge {
  @apply px-2 py-1 rounded text-xs font-semibold;
}
.badge-warning {
  @apply bg-yellow-100 text-yellow-800;
}
.badge-success {
  @apply bg-green-100 text-green-800;
}
.badge-danger {
  @apply bg-red-100 text-red-800;
}
</style>
