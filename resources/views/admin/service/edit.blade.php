@extends('admin.layouts.master')
@section('title', 'Sửa Dịch Vụ: ' . $item->name)
@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card custom-card">
        <div class="card-header justify-content-between">
          <div class="card-title">Cấu hình dịch vụ</div>
          <div class="card-options">
            <a href="{{ route('admin.service.index') }}" class="btn btn-secondary btn-sm">Quay lại</a>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.service.update') }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
            @csrf
            <input type="hidden" name="id" value="{{ $item->id }}">
            <div class="row">
              @if ($item->product_type === 'spin')
                {{-- LAYOUT VÒNG QUAY --}}
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên vòng quay</label>
                  <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên vòng quay dưới</label>
                  <input type="text" class="form-control" name="sub_name" value="{{ $item->sub_name }}">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Giá Lượt Quay</label>
                  <input type="number" class="form-control" name="price" value="{{ $item->price }}">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Loại Phần Thưởng</label>
                  <select class="form-control" name="invar_id">
                    @foreach ($inventoryVars as $inventory)
                      <option value="{{ $inventory->id }}" {{ $item->invar_id == $inventory->id ? 'selected' : '' }}>
                        ID {{ $inventory->id }}: {{ $inventory->name }} - {{ $inventory->unit }}
                      </option>
                    @endforeach
                  </select>
                </div>


                <div class="col-md-6 mb-3">
                  <label class="form-label">Trạng Thái</label>
                  <select class="form-control" name="status">
                    <option value="1" {{ $item->status ? 'selected' : '' }}>Hiển Thị</option>
                    <option value="0" {{ !$item->status ? 'selected' : '' }}>Ẩn</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="priority" class="form-label">Ưu tiên</label>
                  <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $item->priority ?? '') }}" required>
                </div>


                <div class="col-md-6 mb-3">
                  <label class="form-label">Ảnh Đại Diện (Thay đổi)</label>
                  <input type="file" class="form-control" name="image">
                  @if ($item->image)
                    <img src="{{ $item->image }}" class="mt-2 rounded" style="max-height: 100px;">
                  @endif
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Ảnh Bìa Vòng Quay (Cover)</label>
                  <input type="file" class="form-control" name="cover">
                  @if ($item->cover)
                    <img src="{{ $item->cover }}" class="mt-2 rounded" style="max-height: 100px;">
                  @endif
                </div>

                <input type="hidden" name="product_type" value="{{ $item->product_type }}">

                <div class="col-12 mb-3">
                  <label class="form-label">Chọn Danh Mục Hiển Thị</label>
                  <select class="form-control group-select" name="groups[]" multiple>
                    @foreach ($categories as $group)
                      @php
                        $isSelected = optional($item->pivot_groups)->contains(function ($pg) use ($group) {
                            return $pg->group_id == $group->id && $pg->group_type == $group->model;
                        }) ?? false;
                      @endphp
                      <option value="{{ $group->model }}:{{ $group->id }}" {{ $isSelected ? 'selected' : '' }}>
                        {{ $group->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12 mb-4">
                  <label class="form-label">Hướng Dẫn / Mô Tả</label>
                  <textarea class="form-control ckeditor" name="descr" id="descr" rows="3">{{ $item->descr }}</textarea>
                </div>

                <div class="col-12 mb-3">
                  <h5 class="mb-3 border-bottom pb-2">Cấu Hình Giải Thưởng (8 ô)</h5>
                  <div class="row">
                    @for ($i = 0; $i < 8; $i++)
                      @php $p = ($item->prizes[$i] ?? []); @endphp
                      <div class="col-md-3 mb-3">
                        <div class="card border shadow-none">
                          <div class="card-header py-2 bg-light">Giải #{{ $i + 1 }}</div>
                          <div class="card-body p-3">
                            <div class="mb-2">
                              <label class="small fw-bold">Giá trị (Số / Min-Max)</label>
                              <input type="text" class="form-control form-control-sm" name="prizes[{{ $i }}][value]" value="{{ $p['value'] ?? '' }}">
                            </div>
                            <div class="mb-0">
                              <label class="small fw-bold">Tỷ lệ (%)</label>
                              <input type="number" class="form-control form-control-sm" name="prizes[{{ $i }}][percent]" value="{{ $p['percent'] ?? 0 }}">
                            </div>
                          </div>
                        </div>
                      </div>
                    @endfor
                  </div>
                </div>
              @elseif ($item->product_type === 'robux')
                {{-- LAYOUT ROBUX --}}
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên Dịch Vụ</label>
                  <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên Dịch Vụ Dưới</label>
                  @php
                      $subtitle = $item->sub_name;
                      // Old logic used to clear subtitle if it contained rates (e.g. 100|150)
                      // but this caused confusion and cleared user inputs.
                      // Now we simply show it as is.
@endphp
                  <input type="text" class="form-control" name="sub_name" value="{{ $subtitle }}">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Trạng Thái</label>
                  <select class="form-control" name="status">
                    <option value="1" {{ $item->status ? 'selected' : '' }}>Hiển Thị</option>
                    <option value="0" {{ !$item->status ? 'selected' : '' }}>Ẩn</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="priority" class="form-label">Ưu tiên</label>
                  <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $item->priority ?? '') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Kiểu Hiển Thị</label>
                  <select class="form-control" name="display_mode">
                    <option value="list" {{ $item->display_mode === 'list' ? 'selected' : '' }}>Danh Sách</option>
                    <option value="grid" {{ $item->display_mode === 'grid' ? 'selected' : '' }}>Lưới / Card</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Thời gian bảo hành (giờ)</label>
                  <input type="number" class="form-control" name="warranty_hours" value="{{ $item->warranty_hours ?? 0 }}">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Loại Robux</label>
                  <select class="form-control" name="robux_type" id="robux_type_select" onchange="toggleRobuxFields()">
                    <option value="120h" {{ ($item->robux_type ?? '120h') === '120h' ? 'selected' : '' }}>Robux 120h (làm như cũ)</option>
                    <option value="genuine" {{ ($item->robux_type ?? '') === 'genuine' ? 'selected' : '' }}>Robux chính hãng</option>
                  </select>
                </div>
                
                @php
                    $isGenuine = ($item->robux_type ?? '') === 'genuine';
                @endphp

                <div class="col-md-6 mb-3 robux-120h-field" @if($isGenuine) style="display: none;" @endif>
                  <label class="form-label">Tỉ Giá (Rate)</label>
                  @php
                      $rateConfig = $item->rate_config;
                      if (empty($rateConfig) && is_string($item->sub_name) && strpos($item->sub_name, '|') !== false) {
                          // Backward compat: old tier config stored in sub_name
                          $rateConfig = $item->sub_name;
                      }
                      $displayPrice = $rateConfig ?: $item->price;
                  @endphp
                  <input type="text" class="form-control" name="price" value="{{ $displayPrice }}" placeholder="VD: 10 hoặc 100|150, 500|140...">
                  <small class="text-muted">Nhập 1 số để dùng Rate cố định. Nhập dãy <b>Mốc|Rate</b> để tính rate theo mốc (VD: mua < 100 tính rate 150).</small>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Ảnh Đại Diện (Thay đổi)</label>
                  <input type="file" class="form-control" name="image">
                  @if ($item->image)
                    <img src="{{ $item->image }}" class="mt-2 rounded" style="max-height: 100px;">
                  @endif
                </div>

                <input type="hidden" name="product_type" value="{{ $item->product_type }}">

                <div class="col-12 mb-3">
                      <label class="form-label">Chọn Danh Mục Hiển Thị (Category)</label>
                      <select class="form-control group-select" name="groups[]" multiple>
                        @foreach ($categories as $group)
                          @php
                            $isSelected = optional($item->pivot_groups)->contains(function ($pg) use ($group) {
                                return $pg->group_id == $group->id && $pg->group_type == $group->model;
                            }) ?? false;
                          @endphp
                          <option value="{{ $group->model }}:{{ $group->id }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $group->name }}
                          </option>
                        @endforeach
                      </select>
                </div>
                
                <div class="col-12 mb-3">
                  <h5 class="mb-3 border-bottom pb-2">Cấu Hình Gói Robux</h5>
                  <div class="mb-2">
                       <label class="form-label" id="package_config_label">{{ $isGenuine ? 'Danh sách gói (robux|giá tiền)' : 'Danh sách gói' }}</label>
                       @php
                           $configText = '';
                           if (!empty($item->prizes) && is_array($item->prizes)) {
                               $values = [];
                               foreach ($item->prizes as $prize) {
                                   $val = $prize['value'] ?? '';
                                   if ($val !== '') {
                                       if ($isGenuine) {
                                           $priceVal = $prize['percent'] ?? 0;
                                           $values[] = "{$val}|{$priceVal}";
                                       } else {
                                           $values[] = $val;
                                       }
                                   }
                               }
                               $configText = implode(', ', $values);
                           }
                       @endphp
                       <textarea class="form-control" name="package_config" id="package_config_input" rows="2" placeholder="{{ $isGenuine ? '100|10000, 200|19000' : '100, 200, 500, 1000' }}">{{ trim($configText) }}</textarea>
                       <small class="text-muted" id="package_config_help">
                           @if($isGenuine)
                               Nhập định dạng <b>robux|giá_tiền</b>. Ví dụ: <code>100|10000, 200|19000</code>
                           @else
                               Nhập các mốc gói Robux muốn bán, hệ thống sẽ tự tính tiền theo Rate đã nhập ở trên.
                           @endif
                       </small>
                  </div>
                </div>

                <div class="col-md-12 mb-3 robux-120h-field" @if($isGenuine) style="display: none;" @endif>
                   <label class="form-label">Thuế Robux (%)</label>
                   <input type="number" class="form-control" name="play_times" value="{{ $item->play_times }}" placeholder="30">
                   <small class="text-muted">Nhập 30 nều thuế là 30%. Nhập 0 để dùng mặc định hệ thống.</small>
                </div>

                <div class="col-12 mb-4">
                  <label class="form-label">Hướng Dẫn / Mô Tả</label>
                  <textarea class="form-control ckeditor" name="descr" rows="3">{{ $item->descr }}</textarea>
                </div>


                </div>
              @else
                {{-- LAYOUT CHUYÊN MỤC (DEFAULT) --}}
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên Dịch Vụ</label>
                  <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tên Dịch Vụ Dưới</label>
                  <input type="text" class="form-control" name="sub_name" value="{{ $item->sub_name }}">
                </div>


                <div class="col-md-6 mb-3">
                  <label class="form-label">Trạng Thái</label>
                  <select class="form-control" name="status">
                    <option value="1" {{ $item->status ? 'selected' : '' }}>Hiển Thị</option>
                    <option value="0" {{ !$item->status ? 'selected' : '' }}>Ẩn</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="priority" class="form-label">Ưu tiên</label>
                  <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $item->priority ?? '') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Ảnh Đại Diện (Thay đổi)</label>
                  <input type="file" class="form-control" name="image">
                  @if ($item->image)
                    <img src="{{ $item->image }}" class="mt-2 rounded" style="max-height: 100px;">
                  @endif
                </div>

                <input type="hidden" name="product_type" value="{{ $item->product_type }}">

                <div class="col-12 mb-3">
                      <label class="form-label">Chọn Nhóm Sản Phẩm (Groups)</label>
                      <select class="form-control group-select" name="groups[]" multiple>
                        @foreach ($shopGroups as $group)
                          @php
                            $isSelected = optional($item->pivot_groups)->contains(function ($pg) use ($group) {
                                return $pg->group_id == $group->id && $pg->group_type == $group->model;
                            }) ?? false;
                          @endphp
                          <option value="{{ $group->model }}:{{ $group->id }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $group->name }}
                          </option>
                        @endforeach
                      </select>
                </div>
              @endif
            </div>


        <div class="mb-3 text-center">
              <button class="btn btn-primary" type="submit">Cập Nhật Dịch Vụ</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        if ($('.group-select').length) {
            new Choices('.group-select', {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: '-- Chọn nhóm sản phẩm --',
                searchPlaceholderValue: 'Tìm kiếm nhóm...',
                noChoicesText: 'Không còn nhóm nào',
                itemSelectText: 'Nhấn để chọn',
            });
        }

    $('.ckeditor').each(function () {
            if(document.getElementById(this.id)) {
                const ed = CKEDITOR.replace(this.id, {
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
                });
            }
        });
    });

    window.toggleRobuxFields = function() {
        const type = $('#robux_type_select').val();
        if (type === 'genuine') {
            $('.robux-120h-field').hide();
            $('#package_config_label').text('Danh sách gói (robux|giá tiền)');
            $('#package_config_input').attr('placeholder', '100|10000, 200|19000');
            $('#package_config_help').html('Nhập định dạng <b>robux|giá_tiền</b>. Ví dụ: <code>100|10000, 200|19000</code>');
        } else {
            $('.robux-120h-field').show();
            $('#package_config_label').text('Danh sách gói');
            $('#package_config_input').attr('placeholder', '100, 200, 500, 1000');
            $('#package_config_help').text('Nhập các mốc gói Robux muốn bán, hệ thống sẽ tự tính tiền theo Rate đã nhập ở trên.');
        }
    };

    $(document).ready(function() {
        $('#robux_type_select').on('change', window.toggleRobuxFields);
        window.toggleRobuxFields();
    });
</script>
@endsection
