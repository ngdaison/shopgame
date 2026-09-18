import { createApp } from 'vue'
import Antd from 'ant-design-vue'

import Index from './views/Index.vue'

const app = createApp({})

// Plugins
app.use(Antd)

// Components
app.component('boosting-index', Index)

// Mount
app.mount('#app')
