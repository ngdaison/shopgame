import { createApp } from 'vue'
import Index from './views/Index.vue'
import Antd from 'ant-design-vue';

const el = document.getElementById('app');
if (el) {
    const app = createApp(Index, {
        categories: JSON.parse(el.dataset.categories || '[]')
    })
    app.use(Antd)
    app.mount('#app')
}
