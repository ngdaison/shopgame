import { createApp } from 'vue'
import Antd from 'ant-design-vue' // Assuming global usage or remove if not needed
import ItemRobux from '../../../components/store/ItemRobux.vue'

const app = createApp({})

// Plugins
app.use(Antd)

// Components
app.component('item-robux', ItemRobux)

// Mount
app.mount('#app')
