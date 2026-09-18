@extends('admin.layouts.master')
@section('title', 'Admin: Dashboard')
@section('content')
  <section class="mb-3">
    <div class="mb-3 alert alert-secondary alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
      @php $rsl = checkLicenseKey(env('CLIENT_SECRET_KEY')) @endphp
      @if ($rsl['status'] === false)
        <h5>Thông báo: <strong style="color:red;">{{ $rsl['message'] ?? 'Errors' }}</strong></h5>
      @endif
      <h5>VERSION: <strong style="color:blue;">{{ currentVersion() }}</strong></h5>
      <small>Hệ thống tự động cập nhật.</small>
      <br><br>
      <h6>License Key: <strong style="color:red;" id="copyKey">{{ env('CLIENT_SECRET_KEY') }}</strong>
        <button class="btn btn-info btn-sm shadow-sm btn-wave copy waves-effect waves-light" data-clipboard-target="#copyKey" onclick="copy()">Copy</button>
      </h6>
      <br>
      <hr>
      <p class="text-danger">Changelog:</p>
      <ul>
        @foreach (get_change_logs() as $changed)
          <li class="fw-bold text-blue">{!! $changed !!}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="bi bi-x"></i></button>
    </div>
  </section>
  <div class="text-center">
    @if ($rsl['status'] === false)
    <h4>Thông tin giấy phép không hợp lệ!</h4>
    <code>Key: {{ Helper::hideUsername(env('CLIENT_SECRET_KEY', ''), 20) }}</code>
    <br />
    <code>Error: {{ $check['msg'] }}</code>
    @endif
  </div>
@endsection
