// Path: resources/js/app.js
'use strict'
console.log("%c© Copyright 2025%c Bản quyền thuộc về KiyoVN", "color: #00bcd4; font-size: 20px; font-weight: bold; background: #333; padding: 10px; border-radius: 5px 0 0 5px;", "color: #fff; font-size: 20px; font-weight: bold; background: #e91e63; padding: 10px; border-radius: 0 5px 5px 0;");


// core functions & require jquery
window.$getResponseMessage = function (error) {
  if (error.response && error.response.data) {
    return error.response.data.message
  }
  return error.message || 'Unknown error'
}

window.$getRequestMessage = function (error) {
  return error.message || 'Error in request'
}

window.$getStatusMessage = function (error) {
  if (error.responseJSON) {
    return error.responseJSON.message
  }
  return error.statusText
}

window.$getErrorMessage = function (error) {
  return error.message || error.stack
}

window.$catchMessage = function (error) {
  let message = 'System error occurred'

  message = error.isAxiosError
    ? error.response
      ? $getResponseMessage(error)
      : error.request
        ? $getRequestMessage(error)
        : message
    : error.status
      ? $getStatusMessage(error)
      : $getErrorMessage(error)

  // console.log(error.response || error.request || error)

  return message
}

window.$parseError = function (error) {
  try {
    let message = 'System error occurred'

    message = error.isAxiosError
      ? error.response
        ? $getResponseMessage(error)
        : error.request
          ? $getRequestMessage(error)
          : message
      : error.status
        ? $getStatusMessage(error)
        : $getErrorMessage(error)

    console.log(error.response || error.request || error)

    return message
  } catch (e) {
    console.log(e)
    return 'Error occurred while handling error'
  }
}

window.$formatCurrency = function (number, currency = 'VND', maxinum = 2) {
  // 1. Auto-switch currency if globals exist
  if (currency === 'VND' && window.__currencyCode && window.__currencyCode !== 'VND') {
    currency = window.__currencyCode
  }

  // 2. Convert from VND if needed
  if (currency !== 'VND' && window.__currencyRate) {
    number = number / window.__currencyRate
  }

  // Ensure currency code is uppercase for lookups
  currency = (currency || 'VND').toUpperCase();



  // 3. Define hardcoded fallbacks for common symbols
  const fallbacks = {
    'VND': { symbol_left: '', symbol_right: ' ₫', decimals: 0, separator: '.' },
    'USD': { symbol_left: '$', symbol_right: '', decimals: 2, separator: '.' },
    'CNY': { symbol_left: '¥', symbol_right: '', decimals: 2, separator: '.' },
    'KRW': { symbol_left: '₩', symbol_right: '', decimals: 0, separator: '.' },
    'GBP': { symbol_left: '£', symbol_right: '', decimals: 2, separator: '.' },
    'EUR': { symbol_left: '€', symbol_right: '', decimals: 2, separator: ',' },
  };

  const isVND = (currency === 'VND');
  const metadata = window.__currencyMetadata || {};
  const fb = fallbacks[currency] || (isVND ? fallbacks['VND'] : null);

  // 4. Resolve formatting parameters (Metadata > Fallback > Defaults)
  let symbolLeft = (metadata.symbol_left !== null && metadata.symbol_left !== undefined) ? metadata.symbol_left : (fb ? fb.symbol_left : '');
  let symbolRight = (metadata.symbol_right !== null && metadata.symbol_right !== undefined) ? metadata.symbol_right : (fb ? fb.symbol_right : '');
  let decimals = (metadata.decimals !== null && metadata.decimals !== undefined) ? metadata.decimals : (fb ? fb.decimals : (isVND ? 0 : 2));
  let separator = (metadata.separator !== null && metadata.separator !== undefined) ? metadata.separator : (fb ? fb.separator : '.');

  // Safety: If metadata provided empty strings but we have a fallback with actual symbols, use fallback
  if (symbolLeft === '' && symbolRight === '' && fb && (fb.symbol_left || fb.symbol_right)) {
    symbolLeft = fb.symbol_left;
    symbolRight = fb.symbol_right;
  }







  // 5. Final fallback for unknown currencies with no metadata
  if (symbolLeft === '' && symbolRight === '' && !fb) {
    try {
      // Use Intl.NumberFormat for basic number formatting, but append currency code manually
      // to avoid weird locale-specific symbol placement in currency style.
      const formatted = new Intl.NumberFormat(isVND ? 'vi-VN' : 'en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: maxinum,
      }).format(number);
      return isVND ? (formatted + ' ₫') : (formatted + ' ' + currency);
    } catch (e) {
      return number.toFixed(decimals) + ' ' + currency;
    }
  }

  // 6. Handle zero/near-zero edge case
  if (Math.abs(number) < 0.0000001) {
    number = 0;
  }

  // 7. Manual number formatting to respect custom separators
  const thousandSeparator = (separator === '.') ? ',' : '.';
  let parts = number.toFixed(decimals).split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
  let formattedNumber = parts.join(separator);

  // If no decimals and we have a decimal part, remove it
  if (decimals === 0 && parts.length > 1) {
    formattedNumber = parts[0];
  }

  return (symbolLeft || '') + formattedNumber + (symbolRight || '');
}

window.$formatNumber = function (number) {
  return new Intl.NumberFormat('en-US').format(number)
}

window.$formatDateTime = function (date, format = 'YYYY-MM-DD HH:mm:ss') {
  return moment(date).format(format)
}

window.$formatStatus = function (status) {
  switch (status) {
    case 'Running':
      return `<span class="badge bg-primary rounded-lg" style="background-color: #0174BE">Đang chạy</span>`
    case 'Pending':
      return `<span class="badge bg-warning rounded-lg" style="background-color: #FFC436">Đang chờ</span>`
    case 'Preparing':
      return `<span class="badge bg-info rounded-lg" style="background-color: #B15EFF">Đang chuẩn bị</span>`
    case 'Canceled':
      return `<span class="badge bg-danger rounded-lg" style="background-color: #CE5A67">Đã hủy</span>`
    case 'Completed':
      return `<span class="badge bg-success rounded-lg text-white" style="background-color: #1A5D1A">Thành công</span>`
    case 'Refund':
      return `<span class="badge bg-danger rounded-lg text-white" style="background-color: #862B0D">Hoàn tiền</span>`
    case 'WaitingForRefund':
      return `<span class="badge bg-secondary rounded-lg text-white" style="background-color: #4E4FEB">Đang huỷ</span>`
    case 'Holding':
      return `<span class="badge bg-warning rounded-lg text-white" style="background-color: #3F2305">Đang giữ</span>`
    case 'Paused':
      return `<span class="badge bg-danger rounded-lg" style="background-color: #FF2171">Tạm dừng</span>`
    case 'Expired':
      return `<span class="badge bg-danger rounded-lg" style="background-color: #FF6666">Hết hạn</span>`
    case 'Active':
      return `<span class="badge bg-success rounded-lg" style="background-color: #4FC0D0">Hoạt động</span>`
    case 'Cancelled':
      return `<span class="badge bg-danger rounded-lg" style="background-color: #FF6666">Đã hủy</span>`
    case 'Rejected':
      return `<span class="badge bg-danger rounded-lg" style="background-color: #FF6666">Từ chối</span>`
    case 'Approved':
      return `<span class="badge bg-success rounded-lg" style="background-color: #4FC0D0">Đã duyệt</span>`
    default:
      return `<span class="badge bg-secondary rounded-lg text-white" style="background-color: #213363">${status}</span>`
  }

  return status
}

window.$setLoading = function (elm) {
  $(elm).attr('disabled', true).addClass('process')
}

window.$removeLoading = function (elm) {
  $(elm).attr('disabled', false).removeClass('process')
}

window.$formatDate = function (date, format = 'YYYY-MM-DD HH:mm:ss') {
  return moment(date).format(format)
}

window.$isURL = function (str) {
  let regex =
    /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/
  let pattern = new RegExp(regex)
  return pattern.test(str)
}

window.$swal = function (type, message, options = {}) {
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  });

  return Toast.fire({
    icon: type === 'success' ? 'success' : 'error',
    title: type === 'success' ? 'Thành Công' : 'Thất Bại',
    text: message,
    ...options,
  })
}

window.$showLoading = function (message = null) {
  if (window.pageOverlay) {
    window.pageOverlay.addClass('visible').show();
  } else {
    // Fallback if pageOverlay not ready
    Swal.fire({
      icon: 'info',
      title: 'Đang xử lý!',
      html: message ?? 'Không được tắt trang này, vui lòng đợi trong giây lát!',
      timerProgressBar: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      didOpen: () => {
        Swal.showLoading()
      },
    })
  }
}

window.$hideLoading = function () {
  if (window.pageOverlay) {
    window.pageOverlay.removeClass('visible').hide();
  }
  Swal.close()
}

window.$base64_decode = function (str) {
  // Going backwards: from bytestream, to percent-encoding, to original string.
  return decodeURIComponent(
    atob(str)
      .split('')
      .map(function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
      })
      .join('')
  )
}

window.$getCountryName = function (code) {
  return new Intl.DisplayNames(['en'], { type: 'region' }).of(
    code?.toUpperCase()
  )
}

window.$formDataToPayload = function (data) {
  const payload = {}
  for (let [key, value] of data.entries()) {
    payload[key] = value
  }
  return payload
}

// network checking
window.addEventListener('online', function (e) {
  console.log('online')
  $swal('success', 'Network is online')
})
window.addEventListener('offline', function (e) {
  console.log('offline')
  $swal('error', 'Network is offline')
})
// image error
// Image error handler: Use spinner if image fails to load
window.addEventListener('error', function (e) {
  if (e.target.tagName === 'IMG') {
    var fallback = window.fallbackImgSrc || '/images/svg/spinner.svg';
    if (e.target.src.indexOf(fallback) === -1) {
      e.target.src = fallback;
    }
  }
}, true);

// Initial check for images already in DOM that might have failed before global handler
document.querySelectorAll('img').forEach((img) => {
  if (img.complete && img.naturalWidth === 0) {
    var fallback = window.fallbackImgSrc || '/images/svg/spinner.svg';
    if (img.src.indexOf(fallback) === -1) {
      img.src = fallback;
    }
  }
});

// copy function
var clipboard = new ClipboardJS('.copy')

clipboard.on('success', function (e) {
  $swal('success', 'Copied: ' + e.text)
})

clipboard.on('error', function (e) {
  $swal('error', 'Copy failed')
})
// variables
window.$userLevelName = function (level) {
  return level.charAt(0).toUpperCase() + level.slice(1)
}

// extra
window.$logout = async function () {
  try {
    const result = await axios.post('/logout')
  } finally {
    window.location.href = '/login'
  }
}

window.$debounce = function (func, wait) {
  let timeout
  return function (...args) {
    const context = this
    clearTimeout(timeout)
    timeout = setTimeout(() => func.apply(context, args), wait)
  }
}
