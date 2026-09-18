<!-- Popper JS -->
<script src="/_admin/libs/@popperjs/core/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="/_admin/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Defaultmenu JS -->
<script src="/_admin/js/defaultmenu.min.js"></script>

<!-- Node Waves JS-->
<script src="/_admin/libs/node-waves/waves.min.js"></script>

<!-- Sticky JS -->
<script src="/_admin/js/sticky.js"></script>

<!-- Simplebar JS -->
<script src="/_admin/libs/simplebar/simplebar.min.js"></script>
<script src="/_admin/js/simplebar.js"></script>

<!-- Color Picker JS -->
<script src="/_admin/libs/@simonwep/pickr/pickr.es5.min.js"></script>

<!-- Custom JS -->
<script src="/_admin/js/custom.js"></script>

<!-- Custom-Switcher JS -->
<script src="/_admin/js/custom-switcher.min.js"></script>

<!-- Internal Datatables JS -->
<script src="/_admin/js/datatables.js"></script>
<!-- Datatables Cdn -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- extra js-->
<script src="https://unpkg.com/clipboard@2/dist/clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.27/sweetalert2.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@vite('resources/js/functions.js')

<script>
    // Essential Helpers (as fallback for build issues)
    window.$debounce = window.$debounce || function (func, wait) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    };

    window.$catchMessage = window.$catchMessage || function (error) {
        if (error.response && error.response.data && error.response.data.message) {
            return error.response.data.message;
        }
        return error.message || 'System error occurred';
    };

    // Global Axios Setup
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }

    // Process Pending Toasts from alert.blade.php
    if (window.pendingToasts && Array.isArray(window.pendingToasts)) {
        window.pendingToasts.forEach(t => {
            if (typeof toastr[t.type] === 'function') {
                toastr[t.type](t.msg, t.title);
            }
        });
        window.pendingToasts = [];
    }
</script>

<script>
  // Prevent loader overlay being stuck visible (e.g. back-forward cache / interrupted reloads)
  window.addEventListener('pageshow', function () {
    const overlay = document.getElementById('page-overlay');
    if (overlay) {
      overlay.classList.remove('visible', 'active');
      overlay.style.display = 'none';
    }
  });

  $(document).ready(function() {
    window.pageOverlay = $("#page-overlay");
    // Ensure overlay starts hidden even if restored from cached DOM state
    if (window.pageOverlay && window.pageOverlay.length) {
      window.pageOverlay.removeClass('visible active').hide();
    }

    // Check for pending notifications from sessionStorage (set by manual reload scripts)
    const pendingNotif = sessionStorage.getItem('pending_notification');
    if (pendingNotif) {
        try {
            const data = JSON.parse(pendingNotif);
            if (typeof toastr !== 'undefined') {
                const msg = data.message || '';
                if (!window.shownNotifications || !window.shownNotifications.some(s => s.trim() === msg.trim())) {
                    toastr[data.type || 'success'](msg, data.title || 'Thông báo');
                    if (window.shownNotifications) window.shownNotifications.push(msg);
                }
            }
            sessionStorage.removeItem('pending_notification');
        } catch (e) {
            console.error('Error parsing pending notification', e);
        }
    }

    // .axios-form
    $('.default-form').submit(function(e) {
      // show page overlay
      pageOverlay.addClass('visible').show();
    });

    // Global handler for standard forms to show loader
    $(document).on('submit', 'form:not(.axios-form)', function(e) {
        if (!e.isDefaultPrevented()) {
             pageOverlay.addClass('visible').show();
        }
    });

    $(document).on('submit', '.axios-form', async function(e) {
      e.preventDefault();

      let form = $(this);
      let reload = form.data('reload'),
        button = form.find('button[type="submit"]'),
        confirm = form.data('confirm'),
        callback = form.data('callback');

      if (confirm) {
        const confirmResult = await Swal.fire({
          title: 'Are you sure?',
          text: 'You will not be undo this action!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ok',
          cancelButtonText: 'No, cancel!',
          reverseButtons: true
        })

        if (!confirmResult.isConfirmed) {
          return;
        }
      }

      let url = form.attr('action');
      let method = form.attr('method');
      let data = new FormData(this);

      pageOverlay.addClass('visible').show()

      axios({
        method: method,
        url: url,
        data: data,
        headers: {
            'Content-Type': 'multipart/form-data'
        }
      }).then(function(response) {
        if (response.data.status == 200 || response.data.status === true || response.data.status == 1) {
          if (reload) {
            sessionStorage.setItem('pending_notification', JSON.stringify({
              type: 'success',
              title: 'Thành Công',
              message: response.data.message || 'Thao tác thành công'
            }));
            location.reload();
          } else {
            if (typeof toastr !== 'undefined') toastr.success(response.data.message || 'Thao tác thành công', 'Thành Công');
            if (window.shownNotifications) window.shownNotifications.push(response.data.message);
          }
        } else {
            if (typeof toastr !== 'undefined') toastr.error(response.data.message || 'Đã có lỗi xảy ra', 'Thất Bại');
          if (window.shownNotifications) window.shownNotifications.push(response.data.message || 'Đã có lỗi xảy ra');
        }
      }).catch(function(error) {
        const msg = $catchMessage(error);
        if (typeof toastr !== 'undefined') toastr.error(msg, 'Thất Bại');
        if (window.shownNotifications) window.shownNotifications.push(msg);
      }).finally(function() {
        if (typeof pageOverlay !== 'undefined') pageOverlay.removeClass('visible').hide();
      });
    });

    // basic datatable
    $('.datatable').DataTable({
      language: {
        searchPlaceholder: 'Search...',
        sSearch: '',
      },
      response: false,
      order: [
        [0, 'desc']
      ],
      pageLength: 10,
      lengthMenu: [
        [10, 25, 50, 100, 500, 1000, 5000, -1],
        [10, 25, 50, 100, 500, 1000, 5000, 'All']
      ]
    });



  })
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var dropdownElement = document.getElementById('mainHeaderProfile');
    dropdownElement.addEventListener('click', function() {
      var dropdown = new bootstrap.Dropdown(dropdownElement);
      dropdown.toggle();
    });
  });
</script>
<script>
  $(function() {
    // Image Deletion Handler
    $(document).on('click', '.delete-image-btn', function() {
        const btn = $(this);
        const field = btn.data('field');
        const form = btn.closest('form');
        const wrapper = btn.closest('.image-preview-wrapper');
        
        // Remove existing delete flag if any
        form.find(`input[name="delete_${field}"]`).remove();
        
        // Add hidden input to the form
        $('<input>').attr({
            type: 'hidden',
            name: 'delete_' + field,
            value: '1'
        }).appendTo(form);
        
        // Visually remove the image preview
        wrapper.fadeOut(300, function() {
            $(this).remove();
        });
    });

    $(".input-upload").change((e) => {
      const element = e.target,
        input_name = element.getAttribute('data-set');
      // upload image
      const formData = new FormData();
      formData.append('file', element.files[0]);

      $.ajax({
        url: '/api/admin/tools/upload?json=1',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,

        beforeSend: function() {
          toastr.info('Vui lòng đợi trong giây lát...', 'Đang xử lý');
        },
        success: function(res) {
          element.value = '';
          $(`input[id="${input_name}"]`).val(res.data.link);
          toastr.success('Upload ảnh thành công!', 'Thành Công');
        },
        error: function(err) {
          toastr.error($catchMessage(err), 'Thất Bại');
          console.log(err);
        }
      })
    })
  })
</script>

<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
  if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.config.versionCheck = false;
  }

  function initAdminCkeditor() {
    if (typeof CKEDITOR !== 'undefined' && $('.ckeditor').length > 0) {
        $('.ckeditor').each(function() {
            if (!this.id) {
                this.id = 'ckeditor-' + Math.random().toString(36).substring(7);
            }
            if (CKEDITOR.instances[this.id]) {
                return;
            }
            if (document.getElementById(this.id)) {
                CKEDITOR.replace(this.id, {
                    versionCheck: false,
                    height: 400,
                    clipboard_handleImages: false,
                    filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
                    filebrowserUploadMethod: 'form'
                });
            }
        });
    }
  }

  $(function() {
    initAdminCkeditor();
    
    $(document).on('shown.bs.modal shown.bs.tab', function() {
        initAdminCkeditor();
    });

    $(document).on('submit', 'form', function() {
        if (typeof CKEDITOR !== 'undefined') {
            for (var instance in CKEDITOR.instances) {
                try {
                    CKEDITOR.instances[instance].updateElement();
                } catch(e) {}
            }
        }
    });

    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.on('instanceReady', function(evt) {
            evt.editor.on('fileUploadRequest', function(evt) {
                var xhr = evt.data.fileLoader.xhr;
                xhr.setRequestHeader('Cache-Control', 'no-cache');
                if(window.userData && window.userData.access_token) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + window.userData.access_token);
                }
            });
        });
    }
  });
</script>
@yield('scripts')
@stack('scripts')
