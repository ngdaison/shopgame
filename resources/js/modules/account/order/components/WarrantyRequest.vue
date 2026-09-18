<script setup>
import { ref } from 'vue'
import { message } from 'ant-design-vue';

const props = defineProps({
  orderCode: {
    type: String,
    required: true
  },
  category: {
    type: String,
    required: true
  },
  submitUrl: {
    type: String,
    required: true
  },
  redirectUrl: {
    type: String, // Base redirect URL with __code__ placeholder
    required: true
  }
})

const open = ref(false)
const showModal = () => {
  open.value = true
}

const form = ref({
  title: `Bảo hành Sản phẩm ${props.orderCode}`,
  category: props.category,
  content: '',
})

const submiting = ref(false)
const onSubmit = async () => {
  if (!form.value.content.trim()) {
      message.warning('Vui lòng nhập nội dung chi tiết');
      return;
  }

  submiting.value = true
  try {
    const { data: result } = await window.axios.post(props.submitUrl, form.value)
    
    if (window.Swal) {
        window.Swal.fire({
            icon: 'success',
            title: 'Thành công',
            text: 'Gửi yêu cầu bảo hành thành công!'
        }).then(() => {
            const redirect = props.redirectUrl.replace('__code__', result.data.code);
            window.location.href = redirect;
        });
    } else {
        message.success('Gửi yêu cầu bảo hành thành công!');
        setTimeout(() => {
            const redirect = props.redirectUrl.replace('__code__', result.data.code);
            window.location.href = redirect;
        }, 1500);
    }
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message || 'Có lỗi xảy ra, vui lòng thử lại';
    message.error(errorMsg);
  } finally {
    submiting.value = false
  }
}
</script>

<template>
  <div>
    <a-button type="primary" class="!bg-orange-500 !w-full !h-auto !py-1.5 !px-3 !text-[13px] !font-bold rounded-md flex items-center justify-center gap-1.5 border-none shadow-none" @click="showModal">
       <iconify-icon icon="heroicons-outline:shield-check" class="text-lg"></iconify-icon>
       {{ $t('Gửi Yêu Cầu Bảo Hành') }}
    </a-button>

    <a-modal v-model:open="open" title="Tạo yêu cầu hỗ trợ mới" :footer="null">
      <a-form :model="form" layout="vertical" @finish="onSubmit">
        <a-form-item label="Tiêu đề" name="title" :rules="[{ required: true, message: 'Vui lòng nhập tiêu đề' }]">
          <a-input v-model:value="form.title" placeholder="Nhập tiêu đề vấn đề..." readonly disabled />
        </a-form-item>
        
        <a-form-item label="Chủ đề" name="category" :rules="[{ required: true, message: 'Vui lòng chọn chủ đề' }]">
           <a-input v-model:value="form.category" placeholder="Chủ đề..." readonly disabled />
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
:deep(.ant-modal-title) {
  font-weight: 700;
  text-transform: uppercase;
}
:deep(.ant-form-item-label label) {
  font-weight: 600;
  color: #4b5563;
}
</style>
