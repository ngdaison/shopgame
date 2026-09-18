import { build, defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  build: {
    chunkSizeWarningLimit: 2000,
    rollupOptions: {
      output: {
        assetFileNames: 'assets/chunk-[hash].[ext]',
        chunkFileNames: 'assets/chunk-[hash].js',
        entryFileNames: 'assets/[name]-[hash].js',
      },
    },
  },
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/css/app.scss',
        'resources/js/custom/store.js',
        'resources/js/plugins/jquery-jvectormap-2.0.5.min.js',
        'resources/js/plugins/jquery-jvectormap-world-mill-en.js',
        'resources/js/custom/chart-active.js',
        'resources/js/main.js',
        'resources/js/app.js',
        'resources/js/functions.js',
        'resources/js/modules/store/item/index.js',
        'resources/js/modules/store/account/index.js',
        'resources/js/modules/account/profile/index.js',
        'resources/js/plugins/owl.carousel.min.js',
        'resources/js/modules/store/boosting/index.js',
        'resources/js/modules/store/account-v2/index.js',
        'resources/js/modules/store/robux/index.js',

        'resources/js/modules/account/withdraw-v2/index.js',

        'resources/js/modules/account/deposit/index.js',
        'resources/js/modules/account/ticket/index.js',
        'resources/js/modules/account/ticket/show.js',
        'resources/js/modules/account/order/index.js',
        'resources/js/modules/affiliate/index.js',
      ],
      refresh: true,
    }),
    vue(),
  ],
})
