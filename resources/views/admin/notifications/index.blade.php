@extends('admin.layouts.master')
@section('title', 'Thông báo')
@section('styles')
<style>
    .choices__inner {
        min-height: 44px;
        border-radius: 0.375rem !important;
    }
    .choices__list--multiple .choices__item {
        background-color: #7367f0;
        border: 1px solid #7367f0;
        border-radius: 4px;
    }
    .field-wrapper {
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        max-height: 1000px;
        opacity: 1;
    }
    .field-wrapper.hidden-field {
        max-height: 0;
        opacity: 0;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        pointer-events: none;
    }
    .swal2-toast .swal2-html-container {
        margin-top: 2px !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12 mb-1">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Gửi thông báo mới</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.notifications.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Gửi đến Username (Bỏ trống là gửi tất cả)</label>
                                <select name="usernames[]" id="usernames" class="form-control" multiple>
                                    @foreach($users as $user)
                                        <option value="{{ $user->username }}">{{ $user->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="title" class="form-control" required placeholder="Tiêu đề chính">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề dưới (Nội dung ngắn hiển thị ở danh sách)</label>
                                <input type="text" name="subtitle" class="form-control" placeholder="Tóm tắt nội dung">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 field-wrapper" id="link_wrapper">
                        <label class="form-label">Link (Nếu nhập link sẽ không hiện Nội dung detail)</label>
                        <input type="text" id="input_link" name="link" class="form-control" placeholder="https://...">
                    </div>

                    <div class="mb-3 field-wrapper" id="body_wrapper">
                        <label class="form-label">Nội dung chi tiết (Dùng CKEditor)</label>
                        <textarea id="editor" name="body" class="form-control ckeditor"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Gửi thông báo</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Lịch sử thông báo</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive theme-scrollbar">
                    <table class="display table table-bordered table-stripped text-center datatable">
                        <thead class="text-nowrap">
                            <tr>
                                <th>ID</th>
                                <th>Thao tác</th>
                                <th>User</th>
                                <th>Tiêu đề</th>
                                <th>Subtitle</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <a href="{{ route('admin.notifications.edit', $item->id) }}" class="badge bg-primary-gradient me-1">
                                        <i class="fa fa-edit"></i> sửa
                                    </a>
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('admin.notifications.destroy') }}" method="POST" class="d-none">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                    </form>
                                    <a href="javascript:void(0)" onclick="if(confirm('Xác nhận xóa?')) document.getElementById('delete-form-{{ $item->id }}').submit();" class="badge bg-danger-gradient">
                                        <i class="fa fa-trash"></i> xóa
                                    </a>
                                </td>
                                <td>
                                    @if($item->user)
                                        <a href="{{ route('admin.users.edit', $item->user_id) }}">{{ $item->user->username }}</a>
                                    @else
                                        <span class="badge bg-info">System/Broadcast</span>
                                    @endif
                                </td>
                                <td>{{ $item->title }}</td>
                                <td>{{ $item->content }}</td>
                                <td class="text-nowrap">{{ $item->created_at->format('H:i d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- DataTables will handle pagination --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Choices
        const usernameChoice = new Choices('#usernames', {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Chọn người nhận...',
            searchEnabled: true,
            searchPlaceholderValue: 'Tìm username...',
            shouldSort: false,
        });

        // CKEditor Logic (Global Init)
        CKEDITOR.on('instanceReady', function(evt) {
            if (evt.editor.name === 'editor') {
                const editorInstance = evt.editor;
                const linkWrapper = $('#link_wrapper');
                
                // Change listener for visibility toggle
                editorInstance.on('change', function() {
                    const data = editorInstance.getData().trim();
                    if (data !== '' && data !== '<p>&nbsp;</p>' && data !== '<p></p>') {
                        linkWrapper.addClass('hidden-field');
                    } else {
                        linkWrapper.removeClass('hidden-field');
                    }
                });
            }
        });

        // Conditional Visibility
        const inputLink = $('#input_link');
        const linkWrapper = $('#link_wrapper');
        const bodyWrapper = $('#body_wrapper');

        inputLink.on('input', function() {
            if ($(this).val().trim() !== '') {
                bodyWrapper.addClass('hidden-field');
            } else {
                bodyWrapper.removeClass('hidden-field');
            }
        });
    });
</script>
@endsection
