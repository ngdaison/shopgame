@extends('admin.layouts.master')
@section('title', 'Mail Templates')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Cấu hình Thông báo & Mẫu tin nhắn</h4>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#mail" role="tab">
                                <i class="fas fa-envelope me-1"></i> Mail Templates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#telegram" role="tab">
                                <i class="fab fa-telegram me-1"></i> Thông Báo Về Telegram
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#discord" role="tab">
                                <i class="fab fa-discord me-1"></i> Thông Báo về Discord
                            </a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content border border-top-0 p-3">
                        <!-- MAIL TAB -->
                        <div class="tab-pane active" id="mail" role="tabpanel">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> Cấu hình SMTP vui lòng truy cập: <a href="{{ route('admin.settings.apis') }}" class="fw-bold">Admin > Settings > APIs > SMTP Mailer</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Subject</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates->where('type', 'mail') as $template)
                                            <tr>
                                                <td>{{ $template->key }}</td>
                                                <td>{{ $template->subject }}</td>
                                                <td>{{ $template->description }}</td>
                                                <td>
                                                    <a href="{{ route('admin.template.edit', $template->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TELEGRAM TAB -->
                        <div class="tab-pane" id="telegram" role="tabpanel">
                            <form action="{{ route('admin.template.config') }}" method="POST" class="mb-4 border-bottom pb-4">
                                @csrf
                                <input type="hidden" name="type" value="telegram">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">BOT Token</label>
                                        <input type="text" class="form-control" name="bot_token" value="{{ $telegram['bot_token'] ?? '' }}" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ChatID (Nạp Tiền)</label>
                                        <input type="text" class="form-control" name="chat_id_deposit" value="{{ $telegram['chat_id_deposit'] ?? '' }}" placeholder="ID Group or ID User">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ChatID (Đơn hàng)</label>
                                        <input type="text" class="form-control" name="chat_id_order" value="{{ $telegram['chat_id_order'] ?? '' }}" placeholder="ID Group or ID User">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu Cấu Hình Telegram</button>
                                    </div>
                                </div>
                            </form>

                            <h5 class="mb-3">Mẫu Tin Nhắn Telegram</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates->where('type', 'telegram') as $template)
                                            <tr>
                                                <td>{{ $template->key }}</td>
                                                <td>{{ $template->description }}</td>
                                                <td>
                                                    <a href="{{ route('admin.template.edit', $template->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- DISCORD TAB -->
                        <div class="tab-pane" id="discord" role="tabpanel">
                            <form action="{{ route('admin.template.config') }}" method="POST" class="mb-4 border-bottom pb-4">
                                @csrf
                                <input type="hidden" name="type" value="discord">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Webhook (Nạp Tiền)</label>
                                        <input type="text" class="form-control" name="webhook_deposit" value="{{ $discord['webhook_deposit'] ?? '' }}" placeholder="https://discord.com/api/webhooks/...">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Webhook (Đơn hàng)</label>
                                        <input type="text" class="form-control" name="webhook_order" value="{{ $discord['webhook_order'] ?? '' }}" placeholder="https://discord.com/api/webhooks/...">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu Cấu Hình Discord</button>
                                    </div>
                                </div>
                            </form>

                            <h5 class="mb-3">Mẫu Tin Nhắn Discord</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Key</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates->where('type', 'discord') as $template)
                                            <tr>
                                                <td>{{ $template->key }}</td>
                                                <td>{{ $template->description }}</td>
                                                <td>
                                                    <a href="{{ route('admin.template.edit', $template->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
