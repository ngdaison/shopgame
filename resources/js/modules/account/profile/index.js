import { createApp } from 'vue'
import Antd from 'ant-design-vue'

import History from './views/History.vue'
import Transaction from './views/Transaction.vue'
import CardList from './views/CardList.vue'
import BankList from './views/BankList.vue'

// Global functions are now defined in index.blade.php to utilize Blade route helpers correctly.
// This file is reserved for Vue component mounting and imports.


// --- VUE MOUNT ---
const mountVueApp = (containerId, component, componentName) => {
    const el = document.getElementById(containerId);
    if (el) {
        try {
            // Check if app is already mounted to avoid double mounting
            if (el.__vue_app__) {
                return;
            }
            const app = createApp(component);
            app.use(Antd);
            // Optional: Provide global translate function if needed by some components
            app.config.globalProperties.$t = (key) => typeof window.$__t === 'function' ? window.$__t(key) : key;
            app.mount(el);
            el.__vue_app__ = app;
        } catch (e) {
            console.error(`Vue mount failed for ${containerId}:`, e);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Tab initial state
    // Default tab logic is now handled in index.blade.php to prevent conflicts and ensure URL syncing

    // Mount Vue Apps
    mountVueApp('vue-history-app', History, 'account-history');
    mountVueApp('vue-transaction-app', Transaction, 'account-transaction');
    mountVueApp('vue-card-list-app', CardList, 'account-card-list');

    // New mount point for independent transaction history tables
    // We use a component-registry approach so we can use props in Blade
    const historyEl = document.getElementById('vue-transaction-history');
    if (historyEl) {
        const app = createApp({
            components: {
                'account-transaction': Transaction,
                'account-card-list': CardList,
                'account-bank-list': BankList
            }
        });
        app.use(Antd);
        app.config.globalProperties.$t = (key) => typeof window.$__t === 'function' ? window.$__t(key) : key;
        app.mount(historyEl);
    }
});
