import { createApp } from 'vue'
import Antd from 'ant-design-vue';
// import 'ant-design-vue/dist/reset.css'; // Assuming handled globally

import ItemOrderIndex from './items/Index.vue'
import ItemOrderDetail from './items/Detail.vue'
import BoostingOrderIndex from './boosting/Index.vue'
import AccountOrderIndex from './accounts/Index.vue'
import AccountOrderDetail from './accounts/Detail.vue'
import WarrantyRequest from './components/WarrantyRequest.vue'

if (document.getElementById('app-account-order-accounts')) {
    const app = createApp(AccountOrderIndex)
    app.use(Antd)
    app.mount('#app-account-order-accounts')
}

if (document.getElementById('app-account-order-detail')) {
    try {
        const el = document.getElementById('app-account-order-detail');
        if (el.dataset.account) {
            const accountData = JSON.parse(el.dataset.account);
            const app = createApp(AccountOrderDetail, { account: accountData })
            app.use(Antd)
            app.mount('#app-account-order-detail')
            console.log('AccountOrderDetail mounted successfully');
        } else {
            console.error('Missing data-account attribute on #app-account-order-detail');
        }
    } catch (e) {
        console.error("Failed to mount AccountOrderDetail:", e);
    }
}

if (document.getElementById('app-item-order')) {
    const app = createApp(ItemOrderIndex)
    app.use(Antd)
    app.mount('#app-item-order')
}

if (document.getElementById('app-boosting-order')) {
    const app = createApp(BoostingOrderIndex)
    app.use(Antd)
    app.mount('#app-boosting-order')
}

if (document.getElementById('app-warranty-request')) {
    const el = document.getElementById('app-warranty-request');
    const app = createApp(WarrantyRequest, {
        orderCode: el.dataset.code,
        category: el.dataset.category,
        submitUrl: el.dataset.submitUrl,
        redirectUrl: el.dataset.redirectUrl
    })
    app.config.globalProperties.$t = (key) => key; // Simple translation mock if needed
    app.use(Antd)
    app.mount('#app-warranty-request')
}


