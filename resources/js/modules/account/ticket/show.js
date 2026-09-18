import { createApp } from 'vue'
import Show from './views/Show.vue'
import Antd from 'ant-design-vue';

const el = document.getElementById('app');
if (el) {
    const app = createApp(Show, {
        ticketCode: el.dataset.ticketCode
    })
    app.use(Antd)
    app.mount('#app')
}
