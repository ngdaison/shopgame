@if (Session::has('message'))
  <script>
    (function() {
      const msg = "{{ Session::get('message') }}".trim();
      if (!msg) return;
      if (window.shownNotifications && window.shownNotifications.some(s => s.trim() === msg)) return;
      window.pendingToasts = window.pendingToasts || [];
      window.pendingToasts.push({ type: 'info', msg: msg, title: "Thông báo" });
      if (window.shownNotifications) window.shownNotifications.push(msg);
    })();
  </script>
@endif

@if (Session::has('success'))
  <script>
    (function() {
        if (!window.shownNotifications) window.shownNotifications = [];
        @if (is_array(Session::get('success')))
          @foreach (Session::get('success') as $message)
            {
               const m = "{{ $message }}".trim();
               if (m && !window.shownNotifications.some(s => s.trim() === m)) {
                   window.pendingToasts = window.pendingToasts || [];
                   window.pendingToasts.push({ type: 'success', msg: m, title: "Thành Công" });
                   window.shownNotifications.push(m);
               }
            }
          @endforeach
        @else
          const msg = "{{ Session::get('success') }}".trim();
          if (msg && !window.shownNotifications.some(s => s.trim() === msg)) {
              window.pendingToasts = window.pendingToasts || [];
              window.pendingToasts.push({ type: 'success', msg: msg, title: "Thành Công" });
              window.shownNotifications.push(msg);
          }
        @endif
    })();
  </script>
@endif

@if (Session::has('error'))
  <script>
    (function() {
        if (!window.shownNotifications) window.shownNotifications = [];
        @if (is_array(Session::get('error')))
          @foreach (Session::get('error') as $message)
            {
               const m = "{{ $message }}".trim();
               if (m && !window.shownNotifications.some(s => s.trim() === m)) {
                   window.pendingToasts = window.pendingToasts || [];
                   window.pendingToasts.push({ type: 'error', msg: m, title: "Thất Bại" });
                   window.shownNotifications.push(m);
               }
            }
          @endforeach
        @else
          const msg = "{{ Session::get('error') }}".trim();
          if (msg && !window.shownNotifications.some(s => s.trim() === msg)) {
              window.pendingToasts = window.pendingToasts || [];
              window.pendingToasts.push({ type: 'error', msg: msg, title: "Thất Bại" });
              window.shownNotifications.push(msg);
          }
        @endif
    })();
  </script>
@endif

@if ($errors->any())
  <script>
    (function() {
        if (!window.shownNotifications) window.shownNotifications = [];
        @foreach ($errors->all() as $error)
          {
             const m = "{{ $error }}".trim();
             if (m && !window.shownNotifications.some(s => s.trim() === m)) {
                 window.pendingToasts = window.pendingToasts || [];
                 window.pendingToasts.push({ type: 'error', msg: m, title: "Thất Bại" });
                 window.shownNotifications.push(m);
             }
          }
        @endforeach
    })();
  </script>
@endif
