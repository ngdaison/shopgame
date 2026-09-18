import { createApp } from 'vue'
import Antd from 'ant-design-vue'

import PaypalList from './views/PaypalList.vue'
import CryptoList from './views/CryptoList.vue'
import PerfectList from './views/PerfectList.vue'

const appEl = document.getElementById('app');

if (appEl) {
    const app = createApp({
        components: {
            'account-paypal-list': PaypalList,
            'account-crypto-list': CryptoList,
            'account-perfect-list': PerfectList
        }
    });

    app.use(Antd);

    // Global translation helper
    app.config.globalProperties.$t = (key) => typeof window.$__t === 'function' ? window.$__t(key) : key;

    app.mount(appEl);
}
