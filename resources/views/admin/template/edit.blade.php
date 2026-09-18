@extends('admin.layouts.master')
@section('title', 'Edit Mail Template')
@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Edit Template: {{ $template->key }}</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.template.update', $template->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="{{ $template->subject }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" id="content-editor" class="form-control" rows="10">{{ $template->content }}</textarea>
                </div>
                
                @if($template->variables)
                <div class="mb-3">
                    <div class="alert alert-info">
                        <strong>Available Variables:</strong> <br>
                        <ul>
                        @foreach($template->variables as $var)
                            <li>{{ $var }}</li>
                        @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.template.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
    {{-- Assuming CKEditor or Summernote is available via cdn or local --}}
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    $(function() {
      const editor = document.querySelector('#content-editor');

      if (editor) {
        const ed = CKEDITOR.replace(editor, {
          // extraPlugins: 'notification', // Included in Full package
          height: 400,
          clipboard_handleImages: false,
          filebrowserImageUploadUrl: '/api/admin/tools/upload?form=ckeditor',
          filebrowserUploadMethod: 'form'
        });

        ed.on('fileUploadRequest', function(evt) {
          var xhr = evt.data.fileLoader.xhr;

          xhr.setRequestHeader('Cache-Control', 'no-cache');
          if(window.userData && window.userData.access_token) {
              xhr.setRequestHeader('Authorization', 'Bearer ' + window.userData.access_token);
          }
        })
      }
    })
  </script>
@endsection
