<script setup>
import { computed, onMounted, ref } from 'vue'
import { usePagination } from 'vue-request'
import moment from 'moment'
import { message } from 'ant-design-vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
})

const search = ref('')
const columns = [
  {
    title: 'Mã',
    dataIndex: 'code',
    sorter: true,
  },
  {
    title: 'Tiêu đề',
    dataIndex: 'title',
  },
  {
    title: 'Chủ đề',
    dataIndex: 'category',
  },
  {
    title: 'Trạng thái',
    dataIndex: 'status',
  },
  {
    title: 'Ngày tạo',
    dataIndex: 'created_at',
    sorter: true,
  },
  {
    title: 'Cập nhật',
    dataIndex: 'updated_at',
    sorter: true,
  },
  {
    title: 'Thao tác',
    dataIndex: 'action',
    fixed: 'right',
    width: 80,
  },
]

const queryData = (params) => {
  return window.axios.get('/api/users/tickets', {
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
  if (date === null) return ''
  const parsedDate = moment(date, moment.ISO_8601)
  if (!parsedDate.isValid()) return ''
  return parsedDate.format(format || 'HH:mm - DD/MM/YYYY')
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

const open = ref(false);
const showModal = () => {
  open.value = true;
};

// Compute categories list from prop or fallback
const categoriesList = computed(() => {
    if (props.categories && props.categories.length > 0 && props.categories[0] !== '') {
        return props.categories.filter(c => c.trim() !== '').map(c => ({ value: c.trim(), label: c.trim() }));
    }
    return []; // Return empty if no config, hiding the field
});

const form = ref({
  title: '',
  category: categoriesList.value.length > 0 ? categoriesList.value[0].value : 'General',
  content: '',
})

const submiting = ref(false)
const onSubmit = async (values) => {
  submiting.value = true
  try {
    const { data: result } = await window.axios.post('/api/users/tickets', values)
    
    // Set pending toast for next page
    sessionStorage.setItem('pending_toast', JSON.stringify({
        icon: 'success',
        title: 'Thành công',
        message: 'Tạo yêu cầu hỗ trợ thành công!'
    }));

    open.value = false
    // Redirect to detail
    window.location.href = `/account/tickets/${result.data.code || result.data.id}`
  } catch (error) {
    // Standardized Error Toast
    const errorMsg = error.response?.data?.message || error.message || 'Có lỗi xảy ra, vui lòng thử lại';
    
    // Use standard Swal if available (window.Swal), else fallback or no-op
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
        message.error(errorMsg); // Fallback to AntD if Swal missing
    }
  } finally {
    submiting.value = false
  }
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const title = urlParams.get('title');
    const category = urlParams.get('category');
    const content = urlParams.get('content');

    if (title || category || content) {
        if (title) form.value.title = title;
        if (category) {
            // Check if category exists in list
            if (categoriesList.value.some(c => c.value === category)) {
                form.value.category = category;
            } else if (categoriesList.value.length > 0) {
                 // Try fuzzy match? Or just use first one as default was already set.
            }
        }
        if (content) form.value.content = content;
        open.value = true;
    }
});

const viewDetail = (record) => {
    window.location.href = `/account/tickets/${record.code || record.id}`
}

</script>

<template>
  <div class="ticket-index">
    <div class="card">
      <header class="card-header noborder">
        <h4 class="card-title">Tickets</h4>
        <div class="flex items-center space-x-2">
           <a-button type="primary" class="!bg-orange-500" @click="showModal"><i class="fas fa-plus me-2"></i> Tạo yêu cầu hỗ trợ mới</a-button>
        </div>
      </header>
      <div class="card-body px-6 pb-6">
        <div class="mb-5 flex flex-col gap-5 md:flex-row md:items-center">
          <div class="ltr:ml-auto rtl:mr-auto flex gap-x-2">
            <a-input v-model:value="search" placeholder="Tìm theo tiêu đề" />
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
              <template v-if="column.dataIndex === 'code'">
                  #{{ text || record.id }}
              </template>
               <template v-if="column.dataIndex === 'category'">
                   <a-tag color="blue">{{ categoriesList.find(c => c.value === text)?.label || text }}</a-tag>
              </template>
              <template v-if="column.dataIndex === 'title'">
               <span :title="text">{{ text.length > 50 ? text.substring(0, 50) + '...' : text }}</span>
            </template>
            <template v-if="column.dataIndex === 'status'">
                 <a-tag v-if="text === 'open'" color="green">Đang mở</a-tag>
                 <a-tag v-else-if="text === 'pending'" color="orange">Đang chờ</a-tag>
                 <a-tag v-else-if="text === 'closed'" color="red">Đã đóng</a-tag>
                 <a-tag v-else>{{ text }}</a-tag>
              </template>
              <template v-if="column.dataIndex === 'created_at'">{{ formatDate(text) }}</template>
              <template v-if="column.dataIndex === 'updated_at'">{{ formatDate(text) }}</template>
              <template v-if="column.dataIndex === 'action'">
                <a-button size="small" @click="viewDetail(record)">Xem</a-button>
              </template>
            </template>
          </a-table>
        </div>
      </div>
    </div>

    <a-modal v-model:open="open" title="Tạo yêu cầu hỗ trợ mới" :footer="null">
      <a-form :model="form" layout="vertical" @finish="onSubmit">
        <a-form-item label="Tiêu đề" name="title" :rules="[{ required: true, message: 'Vui lòng nhập tiêu đề' }]">
          <a-input v-model:value="form.title" placeholder="Nhập tiêu đề vấn đề..." />
        </a-form-item>
        
        <a-form-item v-if="categoriesList.length > 0" label="Chủ đề" name="category" :rules="[{ required: true, message: 'Vui lòng chọn chủ đề' }]">
          <a-select v-model:value="form.category">
             <a-select-option v-for="cat in categoriesList" :key="cat.value" :value="cat.value">
                 {{ cat.label }}
             </a-select-option>
          </a-select>
        </a-form-item>


        <a-form-item label="Nội dung chi tiết" name="content" :rules="[{ required: true, message: 'Vui lòng nhập nội dung' }]">
          <a-textarea v-model:value="form.content" :rows="4" placeholder="Mô tả chi tiết vấn đề bạn gặp phải..." />
        </a-form-item>

        <a-form-item class="text-right mb-0">
          <a-button @click="open = false" class="mr-2 !text-orange-500">Đóng</a-button>
          <a-button type="primary" htmlType="submit" class="!bg-primary-500" :loading="submiting">
             <i class="fas fa-paper-plane mr-2"></i> Tạo yêu cầu hỗ trợ
          </a-button>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<style scoped>
/* Removed specific padding as card class handles it */
</style>
