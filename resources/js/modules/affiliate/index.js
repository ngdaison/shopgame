import { createApp } from 'vue';
import AntDesignVue from 'ant-design-vue';
import WalletHistory from './wallet-history/Index.vue';
import WithdrawHistory from './withdraw-history/Index.vue';

const AffiliateModule = () => {
    console.log('[DEBUG] AffiliateModule Started');

    const mountApp = (id, component, name) => {
        try {
            const el = document.getElementById(id);
            if (!el) {
                console.log(`[DEBUG] Mount Point #${id} not found for ${name}. Skipping.`);
                return;
            }

            console.log(`[DEBUG] Found #${id}. Initializing ${name}...`);
            if (el.__vue_app__) {
                console.log(`[DEBUG] #${id} already has a Vue app. Skipping.`);
                return;
            }

            const app = createApp(component);

            // Safety for AntDesignVue
            try {
                app.use(AntDesignVue);
            } catch (antdErr) {
                console.error(`[DEBUG] Antd use error in ${name}:`, antdErr);
            }

            // Helper for translations
            app.config.globalProperties.$t = (key) => typeof window.$__t === 'function' ? window.$__t(key) : key;

            app.mount(el);
            el.__vue_app__ = app;
            console.log(`[DEBUG] ${name} Mounted Successfully on #${id}.`);
        } catch (e) {
            console.error(`[DEBUG] CRITICAL ERROR during mount of ${name} on #${id}:`, e);
        }
    };

    // Sequential safe mounts
    mountApp('vue-wallet-history', WalletHistory, 'WalletHistory (Main)');
    mountApp('vue-wallet-history-info', WalletHistory, 'WalletHistory (Info Tab)');
    mountApp('vue-withdraw-history', WithdrawHistory, 'WithdrawHistory');

    // Counters
    try {
        console.log('[DEBUG] Initializing counters...');
        document.querySelectorAll('.counter-value').forEach(counter => {
            const val = counter.getAttribute('data-value');
            const formatType = counter.getAttribute('data-type') || 'currency'; // 'currency' or 'number'
            const target = parseInt(val) || 0;

            const formatValue = (num) => {
                if (formatType === 'number') {
                    return new Intl.NumberFormat('vi-VN').format(num);
                }
                if (typeof window.$formatCurrency === 'function') {
                    return window.$formatCurrency(num);
                }
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);
            };

            counter.innerText = formatValue(target);
        });
        console.log('[DEBUG] Counters initialized.');
    } catch (countErr) {
        console.error('[DEBUG] Counters Error:', countErr);
    }

    console.log('[DEBUG] AffiliateModule Finished Execution');
};

console.log('[DEBUG] Affiliate Index JS Loaded');
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', AffiliateModule);
} else {
    AffiliateModule();
}
