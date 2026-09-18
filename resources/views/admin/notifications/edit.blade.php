@extends('admin.layouts.master')
@section('title', 'Chỉnh sửa thông báo')
@section('styles')
<style>
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
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Chỉnh sửa thông báo #{{ $notification->id }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.notifications.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $notification->id }}">
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Người nhận</label>
                            <input type="text" class="form-control" value="{{ $notification->user ? $notification->user->username : 'System/Broadcast' }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="title" class="form-control" required value="{{ $notification->title }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề dưới (Nội dung ngắn)</label>
                                <input type="text" name="subtitle" class="form-control" value="{{ $notification->content }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 field-wrapper {{ $notification->body ? 'hidden-field' : '' }}" id="link_wrapper">
                        <label class="form-label">Link (Nếu có link sẽ không hiện Nội dung detail)</label>
                        <input type="text" id="input_link" name="link" class="form-control" value="{{ $notification->link }}">
                    </div>

                    <div class="mb-3 field-wrapper {{ $notification->link ? 'hidden-field' : '' }}" id="body_wrapper">
                        <label class="form-label">Nội dung chi tiết</label>
                        <textarea id="editor" name="body" class="form-control ckeditor">{{ $notification->body }}</textarea>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-light">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    // CKEditor handled globally

    const inputLink = document.getElementById('input_link');
    const linkWrapper = document.getElementById('link_wrapper');
    const bodyWrapper = document.getElementById('body_wrapper');

    function toggleFields() {
        if (inputLink.value.trim() !== "") {
            bodyWrapper.classList.add('hidden-field');
        } else {
            bodyWrapper.classList.remove('hidden-field');
        }
    }

    inputLink.addEventListener('input', toggleFields);
    toggleFields();
</script>
@endsection
