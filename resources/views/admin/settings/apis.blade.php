@extends('admin.layouts.master')
@section('title', 'Admin: Apis Settings')
@section('content')

  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">SMTP Mailer | <a href="https://www.cmsnt.co/2022/12/huong-dan-cach-cau-hinh-smtp-e-gui.html" target="_blank">Lấy thông tin SMTP</a></div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'smtp_detail']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="row mb-3">
                 <div class="col-lg-6">
                    <label for="smtp_is_active" class="form-label">SMTP Mail (Tắt/Bật)</label>
                    <select class="form-select" id="smtp_is_active" name="is_active">
                        <option value="1" {{ ($smtp_detail['is_active'] ?? 1) == 1 ? 'selected' : '' }}>Bật</option>
                        <option value="0" {{ ($smtp_detail['is_active'] ?? 1) == 0 ? 'selected' : '' }}>Tắt</option>
                    </select>
                </div>
                <div class="col-lg-6">
                    <label for="smtp_encryption" class="form-label">SMTP Encryption</label>
                    <select class="form-select" id="smtp_encryption" name="encryption">
                        <option value="tls" {{ ($smtp_detail['encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($smtp_detail['encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ ($smtp_detail['encryption'] ?? 'tls') == '' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
              <div class="col-lg-6">
                <label for="host" class="form-label">SMTP Host</label>
                <input type="text" class="form-control" id="host" name="host" value="{{ $smtp_detail['host'] ?? '' }}">
              </div>
              <div class="col-lg-6">
                <label for="port" class="form-label">SMTP Port</label>
                <input type="number" class="form-control" id="port" name="port" value="{{ $smtp_detail['port'] ?? '' }}">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-lg-6">
                <label for="smtp_email" class="form-label">SMTP Email</label>
                <input type="text" class="form-control" id="user" name="user" value="{{ $smtp_detail['user'] ?? '' }}">
              </div>
              <div class="col-lg-6">
                <label for="pass" class="form-label">SMTP Password</label>
                <input type="text" class="form-control" id="pass" name="pass" value="{{ $smtp_detail['pass'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-success-gradient mt-2" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 col-md-12 col-lg-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Authentication | Google App</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'auth_google']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="client_status" class="form-label">Trạng Thái</label>
              <select class="form-select" id="client_status" name="client_status">
                <option value="1" {{ ($auth_google['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                <option value="0" {{ ($auth_google['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="client_key" class="form-label">Client Key</label>
              <input type="text" class="form-control" id="client_key" name="client_key" value="{{ $auth_google['client_key'] ?? '' }}">
            </div>
            <div class="mb-3">
              <label for="client_secret" class="form-label">Client Secret</label>
              <input type="text" class="form-control" id="client_secret" name="client_secret" value="{{ $auth_google['client_secret'] ?? '' }}">
            </div>
            <div class="mb-3">
              <label for="redirect_url">Redirect URL</label>
              <input type="url" class="form-control" id="redirect_url" name="redirect_url" value="{{ route('auth.social.callback', ['provider' => 'google']) }}" readonly>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-success-gradient mt-2" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Authentication | Facebook App</div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'auth_facebook']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="client_status" class="form-label">Trạng Thái</label>
              <select class="form-select" id="client_status" name="client_status">
                <option value="1" {{ ($auth_google['client_status'] ?? 0) == 1 ? 'selected' : '' }}>Bật</option>
                <option value="0" {{ ($auth_google['client_status'] ?? 0) == 0 ? 'selected' : '' }}>Tắt</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="client_key" class="form-label">Client Key</label>
              <input type="text" class="form-control" id="client_key" name="client_key" value="{{ $auth_facebook['client_key'] ?? '' }}">
            </div>
            <div class="mb-3">
              <label for="client_secret" class="form-label">Client Secret</label>
              <input type="text" class="form-control" id="client_secret" name="client_secret" value="{{ $auth_facebook['client_secret'] ?? '' }}">
            </div>
            <div class="mb-3">
              <label for="redirect_url">Redirect URL</label>
              <input type="url" class="form-control" id="redirect_url" name="redirect_url" value="{{ route('auth.social.callback', ['provider' => 'facebook']) }}" readonly>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-success-gradient mt-2" type="submit">Cập nhật ngay</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">

    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <h4 class="card-title">UPLOAD FILE | <a href="https://aws.amazon.com/s3/" target="_blank">S3 Amazon</a></h4>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 's3aws']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="AWS_ACCESS_KEY_ID" class="form-label">AWS_ACCESS_KEY_ID</label>
                <input type="text" class="form-control" id="AWS_ACCESS_KEY_ID" name="AWS_ACCESS_KEY_ID" value="{{ $s3aws['AWS_ACCESS_KEY_ID'] ?? '' }}">
              </div>
              <div class="col-md-6">
                <label for="AWS_SECRET_ACCESS_KEY" class="form-label">AWS_SECRET_ACCESS_KEY</label>
                <input type="text" class="form-control" id="AWS_SECRET_ACCESS_KEY" name="AWS_SECRET_ACCESS_KEY" value="{{ $s3aws['AWS_SECRET_ACCESS_KEY'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="AWS_DEFAULT_REGION" class="form-label">AWS_DEFAULT_REGION</label>
                <input type="text" class="form-control" id="AWS_DEFAULT_REGION" name="AWS_DEFAULT_REGION" value="{{ $s3aws['AWS_DEFAULT_REGION'] ?? '' }}">
              </div>
              <div class="col-md-6">
                <label for="AWS_BUCKET" class="form-label">AWS_BUCKET</label>
                <input type="text" class="form-control" id="AWS_BUCKET" name="AWS_BUCKET" value="{{ $s3aws['AWS_BUCKET'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3">
              <label for="AWS_URL" class="form-label">AWS_URL</label>
              <input type="text" class="form-control" id="AWS_URL" name="AWS_URL" value="{{ $s3aws['AWS_URL'] ?? '' }}" placeholder="AUTO_GEN" readonly>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <h4 class="card-title">UPLOAD FILE | <a href="https://m.do.co/c/9b0d7560bc9e" target="_blank">Digital Ocean Spaces</a></h4>
          <small>$5 per month (250 GiB, 1 TiB outbond)</small>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'do_spaces']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="DO_SPACES_KEY" class="form-label">DO_SPACES_KEY</label>
                <input type="text" class="form-control" id="DO_SPACES_KEY" name="DO_SPACES_KEY" value="{{ $do_spaces['DO_SPACES_KEY'] ?? '' }}">
              </div>
              <div class="col-md-6">
                <label for="DO_SPACES_SECRET" class="form-label">DO_SPACES_SECRET</label>
                <input type="text" class="form-control" id="DO_SPACES_SECRET" name="DO_SPACES_SECRET" value="{{ $do_spaces['DO_SPACES_SECRET'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="DO_SPACES_REGION" class="form-label">DO_SPACES_REGION</label>
                <input type="text" class="form-control" id="DO_SPACES_REGION" name="DO_SPACES_REGION" value="{{ $do_spaces['DO_SPACES_REGION'] ?? '' }}">
              </div>
              <div class="col-md-6">
                <label for="DO_SPACES_BUCKET" class="form-label">DO_SPACES_BUCKET</label>
                <input type="text" class="form-control" id="DO_SPACES_BUCKET" name="DO_SPACES_BUCKET" value="{{ $do_spaces['DO_SPACES_BUCKET'] ?? '' }}">
              </div>
            </div>
            <div class="mb-3">
              <label for="DO_SPACES_URL" class="form-label">DO_SPACES_URL</label>
              <input type="text" class="form-control" id="DO_SPACES_URL" name="DO_SPACES_URL" value="{{ $do_spaces['DO_SPACES_URL'] ?? '' }}">
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <h4 class="card-title">API Tỷ Giá (CurrencyFreaks)</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'currency_freaks']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="api_key" class="form-label">API Key</label>
              <input type="text" class="form-control" id="api_key" name="api_key" value="{{ $currency_freaks['api_key'] ?? '' }}" placeholder="Nhập API Key...">
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <h4 class="card-title">Cấu hình KiyoAI (ChatGPT)</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'chatgpt']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="chatgpt_api_key" class="form-label">ChatGPT API Key</label>
                <input type="password" class="form-control" id="chatgpt_api_key" name="chatgpt_api_key" value="{{ $chatgpt['chatgpt_api_key'] ?? '' }}" placeholder="sk-...">
              </div>
              <div class="col-md-6">
                <label for="chatgpt_model" class="form-label">ChatGPT Model</label>
                <input type="text" class="form-control" id="chatgpt_model" name="chatgpt_model" value="{{ $chatgpt['chatgpt_model'] ?? 'gpt-4o-mini' }}" placeholder="gpt-4o-mini, gpt-4o, ...">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="max_tokens" class="form-label">Max Tokens</label>
                <input type="number" class="form-control" id="max_tokens" name="max_tokens" value="{{ $chatgpt['max_tokens'] ?? '2000' }}">
              </div>
              <div class="col-md-6">
                <label for="temperature" class="form-label">Temperature (0 - 2)</label>
                <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" value="{{ $chatgpt['temperature'] ?? '0.7' }}">
              </div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <h4 class="card-title">UPLOAD FILE | IMG BB</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.apis.update', ['type' => 'imgbb']) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <div class="mb-3 row">
              <div class="col-md-6">
                <label for="client_key" class="form-label">Client Key</label>
                <input type="text" class="form-control" id="client_key" name="client_key" value="{{ $imgbb['client_key'] ?? '' }}">
              </div>
              <div class="col-md-6">
                <label for="client_secret" class="form-label">Client Secret</label>
                <input type="text" class="form-control" id="client_secret" name="client_secret" value="{{ $imgbb['client_secret'] ?? '' }}" readonly>
              </div>
            </div>
            <div class="mb-3 text-end">
              <button class="btn btn-danger-gradient" type="submit">Cập Nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
