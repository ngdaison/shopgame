import _ from 'lodash'
window._ = _

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios'
import Swal from 'sweetalert2'
import Alpine from 'alpinejs'

window.Alpine = Alpine
Alpine.start()

window.axios = axios

window.axios.defaults.headers.common['X-CSRF-TOKEN'] = `${webData?.csrfToken}`
window.axios.defaults.headers.common['X-Requested-With'] = 'application/json'

// Add a request interceptor
axios.interceptors.request.use(
  function (config) {
    // Do something before request is sent
    config.headers['Api-Version'] = '1.0.0'
    if (userData?.access_token) {
      config.headers['Authorization'] = `Bearer ${userData.access_token}`
    }
    return config
  },
  function (error) {
    // Do something with request error
    return Promise.reject(error)
  }
)
// Add a response interceptor
axios.interceptors.response.use(
  function (response) {
    // Any status code that lie within the range of 2xx cause this function to trigger
    // Do something with response data
    return response
  },
  function (error) {
    // Any status codes that falls outside the range of 2xx cause this function to trigger

    if (error.response.status === 401 && !userData) {
      return Swal.fire({
        title: 'Thông báo',
        text: 'Vui lòng đăng nhập để sử dụng dịch vụ',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đăng nhập',
        cancelButtonText: 'Để sau',
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '/login'
        }
      })
    }

    return Promise.reject(error)
  }
)
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
