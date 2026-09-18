@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Item')
@section('css')
  <link rel="stylesheet" href="/_admin/libs/dropzone/dropzone.css">
@endsection
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Chỉnh sửa sản phẩm "#{{ $item->code }}"</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accounts.items.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <input type="hidden" name="id" value="{{ $item->id }}">
        <div class="mb-3">
          <label for="name" class="form-label">Tên sản phẩm</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" placeholder="Có thể bỏ trống">
        </div>
        <div class="mb-3">
          <label for="code" class="form-label">Mã sản phẩm</label>
          <input type="number" class="form-control" id="code" name="code" value="{{ old('code', $item->code) }}" required>
        </div>
        @if ($item->is_old)
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="buyer_name" class="form-label">Người mua</label>
              <input type="text" class="form-control" id="buyer_name" name="buyer_name" value="{{ old('buyer_name', $item->buyer_name) }}" readonly>
            </div>
            <div class="col-md-6">
              <label for="buyer_code" class="form-label">Mã đơn hàng</label>
              <input type="text" class="form-control" id="buyer_code" name="buyer_code" value="{{ old('buyer_code', $item->buyer_code) }}" readonly>
            </div>
          </div>
        @endif
        <div class="mb-3">
          <label for="inp_image" class="form-label">Ảnh sản phẩm</label>
          <input type="file" class="form-control input-upload" data-set="inp_image" accept="image/*">
          <input type="url" class="form-control mt-2" name="image" id="inp_image" placeholder="Nhập link hoặc chọn ảnh để upload" value="{{ asset($item->image) }}" required>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="price" class="form-label">Giá bán</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price', $item->price) }}" required>
          </div>
          <div class="col-md-4">
            <label for="cost" class="form-label">Giá nhập</label>
            <input type="text" class="form-control" id="cost" name="cost" value="{{ old('cost', $item->cost) }}" required>
          </div>
          <div class="col-md-4">
            <label for="discount" class="form-label">% Giảm giá</label>
            <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $item->discount) }}" required>
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1" @if ($item->status === true) selected @endif>Đang bán</option>
            <option value="0" @if ($item->status === false) selected @endif>Ngưng bán</option>
          </select>
        </div>
        @if ($item->type === 'account')
          <div class="mb-3 row">
            <div class="col-md-4">
              <label for="username" class="form-label">Tài khoản</label>
              <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $item->username) }}" required>
            </div>
            <div class="col-md-4">
              <label for="password" class="form-label">Mật khẩu</label>
              <input type="text" class="form-control" id="password" name="password" value="{{ old('password', $item->password) }}" required>
            </div>
            <div class="col-md-4">
              <label for="extra_data" class="form-label">Tuỳ chọn</label>
              <input type="text" class="form-control" id="extra_data" name="extra_data" value="{{ old('extra_data', $item->extra_data) }}">
            </div>
          </div>
        @endif
        <div class="mb-3">
          <label for="highlights" class="form-label">Chi tiết sản phẩm</label>
          @php
            $highlights = '';
            if (is_array($item->highlights)) {
                foreach ($item->highlights as $key => $value) {
                    if (!isset($value['name']) && !isset($value['value'])) {
                        if (is_string($value)) {
                            $highlights .= $value . "\n";
                        } elseif (is_array($value)) {
                            if (isset($value[0])) {
                                $highlights .= $value[0] . "\n";
                            } else {
                                $highlights .= $key . "\n";
                            }
                        }
                    } else {
                        $highlights .= ($value['name'] ?? '') . ':' . ($value['value'] ?? '') . "\n";
                    }
                }
            }
          @endphp
          <textarea class="form-control" id="highlights" name="highlights" rows="3" required>{{ old('highlights', $highlights) }}</textarea>
          <i>Chi tiết nhập như sau: NAME:VALUE => Cấp độ:20, hoặc => Cấp độ 20</i>
        </div>
        @if ($item->group?->game_type === 'lien-minh')
          @php
            $list_champion = '';
            foreach ($item->list_champ ?? [] as $value) {
                if (isset($value['id']) && isset($value['name'])) {
                    $list_champion .= $value['id'] . '-' . $value['name'] . '|';
                }
            }
            $list_skin = '';
            foreach ($item->list_skin ?? [] as $value) {
                if (isset($value['id']) && isset($value['name'])) {
                    //Akali|championsskin_86013-Garen Sư Vương
                    $list_skin .= 'championsskin_' . $value['id'] . '-' . $value['name'] . '|';
                }
            }
          @endphp

          <div class="mb-3">
            <label for="list_champ" class="form-label">Danh sách tướng</label>
            <textarea class="form-control" id="list_champ" name="list_champ" rows="3" required
              placeholder="1-Annie|2-Olaf|3-Galio|4-Twisted Fate|5-Xin Zhao|6-Urgot|7-LeBlanc|8-Vladimir|9-Fiddlesticks|10-Kayle|11-Master Yi|12-Alistar">{{ old('list_champ', $list_champion) }}</textarea>
          </div>
          <div class="mb-3">
            <label for="list_skin" class="form-label">Danh sách skin</label>
            <textarea class="form-control" id="list_skin" name="list_skin" rows="3" required
              placeholder="championsskin_1012-Annie Sinh Nhật|championsskin_2001-Olaf Kẻ Phản Diện|championsskin_2002-Olaf Băng Giá|championsskin_4007-Twisted Fate Âm Phủ|championsskin">{{ old('list_skin', $list_skin) }}</textarea>
          </div>
        @endif
        <div class="mb-3">
          <label for="description" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control ckeditor" id="description" name="description" rows="3" required>{{ old('description', $item->description) }}</textarea>
        </div>
        <div class="mb-3">
          <label for="list_image" class="form-label">Hình ảnh sản phẩm</label>

          <i>Có thể nhiều hình ảnh cho sản phẩm</i>
          <br />
          <i>Hệ thống sẽ upload ảnh lên hosting</i>
        </div>

        <div class="dropzone dropzone-info mb-3" id="fileTypeValidation" action="/api/admin/tools/upload">
          <div class="dz-message needsclick"><i class="icon-cloud-up"></i>
            <h6>Drop files here or click to upload.</h6><span class="note needsclick">(The system will automatically upload after you have finished selecting the photo.)</span>
          </div>
        </div>

        <div class="mb-3">
          <label for="list_image" class="form-label">Danh sách ảnh đã upload ( <span id="image-count">{{ count($item->list_image ?? []) }}</span> )</label>
          <textarea class="form-control" id="list_image" name="list_image" rows="3" required>{{ implode("\n", $item->list_image) . "\n" }}</textarea>
          <small>Có thể nhập link ảnh tự do</small>
        </div>

        <div class="image-uploaded mb-3 mt-3">
          <div class="row">
            @foreach ($item->list_image as $key => $img)
              <div class="col-md-3 mb-4 text-center" data-file-id="img-{{ $key }}">
                <img src="{{ Helper::getValidImage($img) }}" style="width: 100%; height: 100%;">
                <input type="hidden" id="img-{{ $key }}" value="{{ $img }}">
                <a href="javascript:deleteImage('img-{{ $key }}')" class="text-danger">Xóa</a>
              </div>
            @endforeach
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label for="priority" class="form-label">Ưu tiên</label>
            <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $item->priority) }}" required>
          </div>
          <div class="col-md-6">
            <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
            <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="{{ old('warranty_hours', $item->warranty_hours) }}" placeholder="0 = không bảo hành">
          </div>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary"><i class="fa fa-save"></i> Cập nhật ngay</button>
          <br />
          <br />
          <a href="{{ route('admin.accounts.items', ['id' => $item->group_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại ngay</a>
        </div>
      </form>
    </div>
  </div>

  @if ($item->type === 'account_group')
    <div class="card">
      <div class="card-header">
        <h4>Danh sách tài khoản còn trong kho "#{{ $item->code }}"</h4>
      </div>

      <div class="card-body">
        <div class="table-responsive theme-scrollbar">
          <table class="display table table-bordered table-stripped text-center datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>Thao tác</th>
                <th>Tài khoản</th>
                <th>Mật khẩu</th>
                <th>Authen/Cookie</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($item->archive as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>
                    <a href="javascript:deleteArchive({{ $item->id }})" class="shadow btn btn-danger btn-xs sharp me-1"><i class="fa fa-trash"></i> Xoá</a>
                  </td>
                  <td>{{ $item->username }}</td>
                  <td>{{ $item->password }}</td>
                  <td>{{ $item->extra_data }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer">
        <a href="javascript:void(0)" class="btn btn-danger btn-sm">Thêm tài khoản</a>
      </div>
    </div>

    @push('scripts')
      <script>
        const deleteArchive = async (id) => {
          let res = await fetch(`/api/admin/accounts/items/edit/${id}/archive`, {
            method: 'DELETE',
            headers: {
              'Authorization': 'Bearer {{ auth()->user()->access_token }}',
            },
          });
          let json = await res.json();
          if (json.status) {
            sessionStorage.setItem('pending_notification', JSON.stringify({
              type: 'success',
              title: 'Thành Công',
              message: json.message || 'Xóa thành công'
            }));
            window.location.reload();
          }
        }
      </script>
    @endpush
  @endif
@endsection
@section('scripts')
  <script src="/_admin/libs/dropzone/dropzone-min.js"></script>
  <script>
    $(document).ready(function() {
      let queue = [];

      Dropzone.options.fileTypeValidation = {
        paramName: "file",
        maxFiles: 100,
        maxFilesize: 30,
        acceptedFiles: "image/*",
        headers: {
          'Authorization': 'Bearer {{ auth()->user()->access_token }}',
        },
        dictRemoveFile: 'Xóa',
        addRemoveLinks: true,
        autoProcessQueue: false,
        init: function() {
          let myDropzone = this;

          myDropzone.on("addedfiles", function(files) {
            queue.push(...files);
            processQueue();
          });

          myDropzone.on("complete", function(file) {
            if (file.xhr) {
              let response = file.xhr.response;

              if (isJson(response)) {
                let obj = JSON.parse(response);
                if (file.xhr.status == 200 && obj.data) {
                  let info = obj.data;

                  let elm = document.querySelector('#list_image');
                  elm.value += `${info.path}\n`;

                  let count = document.querySelector('#image-count');
                  count.textContent = parseInt(count.textContent) + 1;
                } else {
                  toastr.error(obj.message || 'Unknown error', 'Thất Bại');
                  setFileError(file);
                }
              } else {
                console.log('Invalid JSON response');
                setFileError(file);
              }
            }
            queue.shift();
            processQueue();
          });

          myDropzone.on("removedfile", function(file) {
            document.querySelector(`.image-uploaded [data-file-id="${file.upload.uuid}"]`).remove();
            let _ref;
            return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
          });

          myDropzone.on("error", function(file, message) {
            console.log(message);
            setFileError(file, message);
          });

          function processQueue() {
            if (queue.length > 0 && myDropzone.getUploadingFiles().length === 0) {
              myDropzone.processFile(queue[0]);
            }
          }

          function isJson(str) {
            try {
              JSON.parse(str);
            } catch (e) {
              return false;
            }
            return true;
          }

          function setFileError(file, message = 'Invalid JSON or Server Error') {
            file.previewElement.classList.add("dz-error");
            let errorMsgElement = file.previewElement.querySelector("[data-dz-errormessage]");
            if (errorMsgElement) {
              errorMsgElement.textContent = message;
            }
          }
        }
      };

      let myDropzone = new Dropzone(".dropzone");
    });
  </script>
  <script src="/plugins/ckeditor/ckeditor.js"></script>

  <script>
    // CKEditor handled globally by class .ckeditor

    const deleteImage = (id) => {
      let elm = document.querySelector(`[data-file-id="${id}"]`);

      let img = document.querySelector(`#${id}`).value;

      let listImage = document.querySelector('#list_image');

      listImage.value = listImage.value.replace(img + '\n', '');

      let count = document.querySelector('#image-count');

      count.textContent = parseInt(count.textContent) - 1;

      elm.remove();

    }
  </script>
@endsection
