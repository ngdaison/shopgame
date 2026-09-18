@extends('admin.layouts.master')
@section('title', 'Admin: Item Data #' . $item->id)
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Chỉnh sửa sản phẩm "{{ $item->name }} - {{ $item->code }}"</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.items.data.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">Tên sản phẩm</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" placeholder="Tên sản phẩm cần bán" required>
        </div>
        <div class="mb-3">
          <label for="code" class="form-label">Mã sản phẩm</label>
          <input type="number" class="form-control" id="code" name="code" value="{{ old('code', $item->code) }}" required>
        </div>
        <div class="mb-3">
          <label for="image" class="form-label">Ảnh sản phẩm - <a href="{{ Helper::getValidImage($item->image) }}" target="_blank">xem ảnh</a></label>
          <input type="file" class="form-control" id="image" name="image">
        </div>
        <div class="mb-3">
          <label for="type" class="form-label">Thông tin cần</label>
          <select name="type" id="type" class="form-control" required>
            <option value="">- Chọn Thông Tin -</option>
            <option value="addfriend" @if ($item->type === 'addfriend') selected @endif>Tài Khoản</option>
            <option value="user_pass" @if ($item->type === 'user_pass') selected @endif>Tài Khoản + Mật Khẩu</option>
            <option value="gamepass" @if ($item->type === 'gamepass') selected @endif>Link GamePass</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="package_id" class="form-label">Chọn Gói</label>
          <select name="package_id[]" id="package_id" class="form-control category-select" multiple>
            @php $selectedPackages = $item->packages->pluck('id')->toArray(); @endphp
            @foreach ($packages as $pkg)
              <option value="{{ $pkg->id }}" @if (in_array($pkg->id, $selectedPackages)) selected @endif>{{ $pkg->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="price" class="form-label">Giá sản phẩm</label>
            <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $item->price) }}" @if ($item->robux) readonly @else required @endif>
          </div>
          <div class="col-md-4">
            <label for="discount" class="form-label">% Giảm giá</label>
            <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', $item->discount) }}" required>
          </div>
          <div class="col-md-4">
            <label for="robux" class="form-label">Giá trị robux</label>
            <input type="number" class="form-control" id="robux" name="robux" value="{{ old('robux', $item->robux) }}" required>
            <small class="text-danger">* Nếu nhập robux thì không cần nhập giá | tổng tiền = rate * robux</small>
          </div>
        </div>
        <div class="mb-3">
          <label for="status" class="form-label">Trạng thái</label>
          <select class="form-control" id="status" name="status" required>
            <option value="1" @if ($item->status === true) selected @endif>Đang bán</option>
            <option value="0" @if ($item->status === false) selected @endif>Chưa bán</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
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
                <button class="btn btn-primary" type="submit">Cập nhật</button>
              </div>
          <br />
          <a href="{{ route('admin.items.data', ['id' => $item->group_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại ngay</a>
        </div>
      </form>
    </div>
  </div>
@endsection
@section('scripts')
  <script>
    $(document).ready(function() {
      $('#robux').change((value) => {
        if (value && value.target.value > 0) {
          $('#price').val(0).attr('readonly', true);
        } else {
          $('#price').val(0).attr('readonly', false);
        }
      })

      $("#type").change(value => {
        if (value.target.value === 'addfriend ,gamepass') {
          $('#ingame_group').show();
        } else {
          $('#ingame_group').hide();
        }
      })

      $("#type").trigger('change');
    })
  </script>
  <script>
    $(function() {
      $('.category-select').each(function() {
          new Choices(this, {
              removeItemButton: true,
              placeholder: true,
              placeholderValue: '-- Chọn Gói --'
          });
      });
    })
  </script>
@endsection
