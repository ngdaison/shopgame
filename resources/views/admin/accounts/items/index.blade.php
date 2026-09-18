@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Item')
@section('css')
  <link rel="stylesheet" href="/_admin/libs/dropzone/dropzone.css">
@endsection
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Thêm sản phẩm vào nhóm</div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.accounts.items.store', ['id' => $group->id]) }}" method="POST" enctype="multipart/form-data" class="default-form axios-form" data-reload="true">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">Tên sản phẩm</label>
          <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Tên sản phẩm cần bán" required>
        </div>
        <div class="mb-3">
          <label for="code" class="form-label">Mã sản phẩm</label>
          <input type="number" class="form-control" id="code" name="code" value="{{ old('code') }}">
          <i>Mã này chỉ có hiệu lực khi bạn nhập 1 tài khoản, nếu lớn hơn 1 tài khoản hệ thống sẽ random tất cả</i>
        </div>
        <div class="mb-3">
          <label for="inp_image" class="form-label">Ảnh sản phẩm</label>
          <input type="file" class="form-control input-upload" data-set="inp_image" accept="image/*">
          <input type="url" class="form-control mt-2" name="image" id="inp_image" placeholder="Nhập link hoặc chọn ảnh để upload" required>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="price" class="form-label">Giá bán</label>
            <input type="text" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
          </div>
          <div class="col-md-4">
            <label for="cost" class="form-label">Giá nhập</label>
            <input type="text" class="form-control" id="cost" name="cost" value="{{ old('cost') }}" required>
          </div>
          <div class="col-md-4">
            <label for="discount" class="form-label">% Giảm giá</label>
            <input type="number" class="form-control" id="discount" name="discount" value="{{ old('discount', 0) }}" required>
          </div>
        </div>
        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-control" id="status" name="status" required>
              <option value="1">Đang bán</option>
              <option value="0">Ngưng bán</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="type" class="form-label">Loại sản phẩm</label>
            <select name="type" id="type" class="form-control" required>
              <option value="account">Mặc Định</option>
              {{-- <option value="account_group">Gọp Tài Khoản Theo Nhóm</option> --}}
            </select>
            {{-- <small>* Khi chọn gọp tài khoản thì nó không chia ra nhiều sản phẩm khác nhau theo list tài khoản nhập</small> --}}
          </div>
        </div>
        <div class="mb-3">
          <label for="list_item" class="form-label">Danh sách tài khoản</label>
          <textarea class="form-control" id="list_item" name="list_item" rows="3" required>{{ old('list_item') }}</textarea>
          <i>* Nhập nhiều hơn 1 tài khoản thì nó sẽ nhân bản sản phẩm giống nhau, chủ yếu dùng cho mua random</i>
          <br />
          <i>* Mỗi dòng là 1 sản phẩm, có thể nhập USERNAME|PASSWORD|2FA, nội dung này sẽ hiển thị cho người mua</i>
        </div>
        <div class="mb-3">
          <label for="highlights" class="form-label">Chi tiết sản phẩm</label>
          <textarea class="form-control" id="highlights" name="highlights" rows="3" required>{{ old('highlights') }}</textarea>
          <i>Chi tiết nhập như sau: NAME:VALUE => Cấp độ:20, hoặc => Cấp độ 20</i>
        </div>
        @if ($group->game_type === 'lien-minh')
          <div class="mb-3">
            <label for="list_champ" class="form-label">Danh sách tướng</label>
            <textarea class="form-control" id="list_champ" name="list_champ" rows="3" required
              placeholder="1-Annie|2-Olaf|3-Galio|4-Twisted Fate|5-Xin Zhao|6-Urgot|7-LeBlanc|8-Vladimir|9-Fiddlesticks|10-Kayle|11-Master Yi|12-Alistar">{{ old('list_champ') }}</textarea>
          </div>
          <div class="mb-3">
            <label for="list_skin" class="form-label">Danh sách skin</label>
            <textarea class="form-control" id="list_skin" name="list_skin" rows="3" required
              placeholder="championsskin_1012-Annie Sinh Nhật|championsskin_2001-Olaf Kẻ Phản Diện|championsskin_2002-Olaf Băng Giá|championsskin_4007-Twisted Fate Âm Phủ|championsskin">{{ old('list_skin') }}</textarea>
          </div>
        @elseif ($group->game_type === 'dot-kich')
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="cf_vip_ig" class="form-label">VIP InGame</label>
              {{-- 0-8 --}}
              <select name="cf_vip_ig" id="cf_vip_ig" class="form-control" required>
                @for ($i = 0; $i < 10; $i++)
                  <option value="{{ $i }}">{{ $i }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-4">
              <label for="cf_vip_amount" class="form-label">Số Lượng VIP</label>
              <input type="number" class="form-control" id="cf_vip_amount" name="cf_vip_amount" value="{{ old('cf_vip_amount', 0) }}" required>
            </div>
            <div class="col-md-4">
              <label for="cf_the_loai" class="form-label">Thể Loại Nick</label>
              <select name="cf_the_loai" id="cf_the_loai" class="form-control" required>
                <option value="">- Tất Cả -</option>
                <option value="ai">AI</option>
                <option value="c4">C4</option>
                <option value="zombie">Zombie</option>
              </select>
            </div>
          </div>
        @endif
        <div class="mb-3">
          <label for="description" class="form-label">Mô tả sản phẩm</label>
          <textarea class="form-control ckeditor" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
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
          <label for="list_image" class="form-label">Danh sách ảnh đã upload ( <span id="image-count">0</span> )</label>
          <textarea class="form-control" id="list_image" name="list_image" rows="3" required>{{ old('list_image') }}</textarea>
          <small>Có thể nhập link ảnh tự do</small>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="priority" class="form-label">Ưu tiên</label>
            <input type="number" class="form-control" id="priority" name="priority" value="0" required>
          </div>
          <div class="col-md-6">
            <label for="warranty_hours" class="form-label">Thời gian bảo hành (giờ)</label>
            <input type="number" class="form-control" id="warranty_hours" name="warranty_hours" value="0" placeholder="0 = không bảo hành">
          </div>
        </div>
        <div class="mb-3 text-center">
          <button class="btn btn-primary">Tạo sản phẩm</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Quản lý sản phẩm của nhóm "{{ $group->name }}" - Loại 1</div>
    </div>
    <div class="card-body">
      <div class="text-center mb-3">
        <button type="button" class="btn btn-danger ids-action" onclick="deleteList()"><i class="fa fa-trash"></i> Xoá <span class="checked-count">0</span> sản phẩm</button>
        <button type="button" class="btn btn-primary ids-action" onclick="copyList()"><i class="fa fa-copy"></i> Sao chép <span class="checked-count">0</span> sản phẩm</button>
        <button type="button" class="btn btn-success ids-action" onclick="updateInfoList()"><i class="fa fa-credit-card"></i> Đổi giá <span class="checked-count">0</span> sản phẩm</button>
      </div>
      <div class="table-responsive theme-scrollbar p-3">
        <table class="display table-bordered table-stripped text-nowrap datatable1_2 table">
          <thead>
            <tr>
              <th data-sortable="false">
                <input type="checkbox" id="check_all" />
              </th>
              <th data-sortable="false">#</th>
              <th data-sortable="false">Thao tác</th>
              <th>Tài khoản</th>
              <th>Tên sản phẩm</th>
              <th>Mã sản phẩm</th>
              <th>Giá bán</th>
              <th>Giá nhập</th>
              <th>% Giảm giá</th>
              <th data-sortable="false">Ảnh sản phẩm</th>
              <th>Người thêm</th>
              <th>Người mua</th>
              <th>Mã đơn hàng</th>
              <th>Trạng thái</th>
              <th>Ngày thêm</th>

              <th>Hoa hồng</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            @foreach ([] as $item)
              <script>
                var accountInfo_{{ $item->id }} = {
                  username: '{{ $item->username }}',
                  password: '{{ $item->password }}',
                  extra_data: '{{ $item->auth }}'
                };
              </script>
              <tr>
                <td>
                  <input type="checkbox" class="check_item" value="{{ $item->id }}" />
                </td>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ route('admin.accounts.items.show', ['id' => $item->id]) }}" class="badge bg-primary-gradient text-white"><i class="fa fa-pencil"></i> sửa</a>
                  <a href="#" class="badge bg-danger-gradient text-white js-delete-row" data-id="{{ $item->id }}"><i class="fa fa-trash"></i> xoá</a>
                </td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->name }}</td>
                <td>#{{ $item->code }}</td>
                <td>{{ Helper::formatCurrency($item->price) }}</td>
                <td>{{ Helper::formatCurrency($item->cost) }}</td>
                <td>{{ $item->discount }}%</td>
                <td><img src="{{ asset($item->image) }}" width="40"></td>
                <td>{{ $item->staff_name ?? '-' }}</td>
                <td>{{ $item->buyer_name ?? '-' }}</td>
                <td>{{ $item->buyer_code ?? '-' }}</td>
                <td>{!! $item->is_sold === true ? '<span class="text-success">Đã Bán</span>' : '<span class="text-danger">Chưa Bán</span>' !!}</td>
                <td>{{ $item->created_at }}</td>

                <td>{{ Helper::formatCurrency($item->staff_payment) }}</td>
                <td>
                  @if ($item->staff_status === 'Completed')
                    <span class="text-success">Đã Duyệt</span>
                  @else
                    <span class="text-danger">Chưa duyệt</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">

      <a href="{{ route('admin.accounts.groups', ['id' => $group->category_id]) }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Quay lại danh sách nhóm</a>
    </div>
  </div>
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
    // checkbox table
    $("#check_all").click(function() {
      if ($(this).is(":checked")) {
        $(".check_item").prop("checked", true);
      } else {
        $(".check_item").prop("checked", false);
      }
    });

    $(".check_item").click(function() {
      if ($(".check_item:checked").length == $(".check_item").length) {
        $("#check_all").prop("checked", true);
      } else {
        $("#check_all").prop("checked", false);
      }
    });

    const getIds = () => {
      let ids = [];
      $(".check_item:checked").each(function() {
        ids.push($(this).val());
      });
      return ids;
    }

    // event to class checked-count, checked-ids
    const updateChecked = () => {
      let ids = getIds();
      $(".checked-count").text(ids.length);

      if (ids.length > 0) {
        $(".ids-action").removeClass('disabled btn-disabled')
      } else {
        $(".ids-action").addClass('disabled btn-disabled')
      }
    }

    $(".check_item").click(function() {
      updateChecked();
    });

    $("#check_all").click(function() {
      updateChecked();
    });

    updateChecked();

    const copyList = async () => {
      const ids = getIds()

      if (ids.length === 0) {
        toastr.error('Vui lòng chọn ít nhất 1 tài khoản để xoá', 'Thất Bại');
        return
      }

      let content = ''

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.accounts.items.copy-list') }}', {
          ids: ids
        })

        content = result.data
      } catch (error) {
        toastr.error($catchMessage(error), 'Thất Bại');
        return
      }

      // copy to clipboard
      const el = document.createElement('textarea');
      el.value = content;
      document.body.appendChild(el);
      el.select();

      document.execCommand('copy');

      document.body.removeChild(el);

      toastr.success('Đã sao chép ' + ids.length + ' tài khoản', 'Thành Công');
    }

    const updateInfoList = async () => {
      const ids = getIds();

      if (ids.length === 0) {
        toastr.error('Vui lòng chọn ít nhất 1 tài khoản để cập nhật', 'Thất Bại');
        return;
      }

      const {
        value: formValues
      } = await Swal.fire({
        title: 'Đổi giá sản phẩm',
        html: `
      <input id="swal-input1" class="swal2-input" placeholder="Giá bán">
      <input id="swal-input2" class="swal2-input" placeholder="Giá nhập">
      <input id="swal-input3" class="swal2-input" placeholder="% Giảm giá">
    `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Cập nhật',
        cancelButtonText: 'Hủy bỏ',
        preConfirm: () => {
          const popup = Swal.getPopup();
          const price = popup.querySelector('#swal-input1').value;
          const cost = popup.querySelector('#swal-input2').value;
          const discount = popup.querySelector('#swal-input3').value;

          // Kiểm tra dữ liệu trước khi trả về
          if (!price || !cost || !discount) {
            Swal.showValidationMessage('Vui lòng nhập đầy đủ thông tin');
            return false;
          }

          return {
            price,
            cost,
            discount
          };
        }
      });

      if (formValues) {
        $showLoading();
        try {
          const {
            data: result
          } = await axios.post('{{ route('admin.accounts.items.update-list') }}', {
            ids: ids,
            data: formValues
          });

          if (result.status) {
            sessionStorage.setItem('pending_notification', JSON.stringify({
              type: 'success',
              title: 'Thành Công',
              message: result.message || 'Cập nhật thành công'
            }));
            window.location.reload();
          } else {
            toastr.error(result.message, 'Thất Bại');
          }
        } catch (error) {
          toastr.error($catchMessage(error), 'Thất Bại');
        } finally {
          $hideLoading();
        }
      }
    };


    const deleteList = async () => {
      const ids = getIds()

      if (ids.length === 0) {
        toastr.error('Vui lòng chọn ít nhất 1 tài khoản để xoá', 'Thất Bại');
        return
      }

      const confirm = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      })

      if (!confirm.isConfirmed) return

      $showLoading()

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.accounts.items.delete-list') }}', {
          ids: ids
        })

        if (result.status) {
          sessionStorage.setItem('pending_notification', JSON.stringify({
            type: 'success',
            title: 'Thành công',
            message: result.message || 'Xoá thành công'
          }));
          location.reload();
        } else {
          $hideLoading();
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        $hideLoading();
        toastr.error($catchMessage(error), 'Thất Bại');
      }
    }


    // CKEditor handled globally by class .ckeditor

    $(document).on('click', '.js-delete-row', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = $(this).data('id');
      if (id) deleteRow(id);
      return false;
    });

    const deleteRow = async (id) => {
      const confirmDelete = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
      });

      if (!confirmDelete.isConfirmed) return;

      $showLoading();

      try {
        const {
          data: result
        } = await axios.post('{{ route('admin.accounts.items.delete') }}', {
          id
        })

        if (result.status) {
          toastr.success(result.message || 'Xoá thành công', 'Thành công');
          try {
            $('.datatable1_2').DataTable().ajax.reload(null, false);
          } catch (e) {}
          try {
            updateChecked();
          } catch (e) {}
        } else {
          toastr.error(result.message, 'Thất Bại');
        }
      } catch (error) {
        toastr.error($catchMessage(error), 'Thất Bại');
      } finally {
        $hideLoading();
      }
    }
  </script>

  <script>
    $(document).ready(function() {
      //DataTable
      $(".datatable1_2").DataTable({
        order: [1, 'desc'],
        responsive: false,
        lengthMenu: [
          [10, 50, 100, 200, 500, 1000, 2000, 10000, -1],
          [10, 50, 100, 200, 500, 1000, 2000, 10000, "All"]
        ],
        language: {
          searchPlaceholder: 'Tìm kiếm...',
          sSearch: '',
          lengthMenu: '_MENU_',
        },
        processing: true,
        serverSide: true,
        ajax: {
          url: '/api/admin/data/accounts-v1',
          async: true,
          type: 'GET',
          dataType: 'json',
          headers: {
            'Authorization': 'Bearer ' + userData.access_token,
            'Accept': 'application/json',
          },
          data: function(data) {
            let payload = {}
            // default params
            payload.group = {{ $group->id }};
            // set params
            payload.page = data.start / data.length + 1;
            payload.limit = data.length;
            payload.search = data.search.value;
            payload.sort_by = data.columns[data.order[0].column].data;
            payload.sort_type = data.order[0].dir;
            // return json
            return payload;
          },
          error: function(xhr) {
            toastr.error($catchMessage(xhr), 'Thất Bại');
          },
          dataFilter: function(data) {
            let json = JSON.parse(data);
            if (json.status) {
              json.recordsTotal = json.data.meta.total
              json.recordsFiltered = json.data.meta.total
              json.data = json.data.data
              return JSON.stringify(json); // return JSON string
            } else {
              toastr.error(json.message, 'Thất Bại');
              return JSON.stringify({
                recordsTotal: 0,
                recordsFiltered: 0,
                data: []
              }); // return JSON string
            }
          }
        },
        columns: [{
          data: 'priority',
          render: function(data, type, row) {
            return `<input type="number" class="form-control form-control-sm mx-auto input-priority" value="${data}" data-id="${row.id}" style="width: 80px;">`
          }
        }, {
          data: 'id'
        }, {
          data: null,
          render: function(data, type, row) {
            return `<a href="/admin/accounts/items/edit/${data.id}" class="badge bg-primary-gradient text-white"><i class="fa fa-pencil"></i> sửa</a>
            <a href="#" class="badge bg-danger-gradient text-white js-delete-row" data-id="${data.id}"><i class="fa fa-trash"></i> xoá</a>`
          }
        }, {
          data: 'username'
        }, {
          data: 'name'
        }, {
          data: 'code'
        }, {
          data: 'price',
          render: function(data, type, row) {
            return $formatCurrency(data)
          }
        }, {
          data: 'cost',
          render: function(data, type, row) {
            return $formatCurrency(data)
          }
        }, {
          data: 'discount',
          render: function(data, type, row) {
            return data + '%'
          }
        }, {
          data: 'image',
          render: function(data, type, row) {
            return `<div class="text-center"><img src="${data}" width="40" /></div>`
          }
        }, {
          data: 'staff_name',
          render: function(data, type, row) {
            return data ?? '-'
          }
        }, {
          data: 'buyer_name',
          render: function(data, type, row) {
            return data ?? '-'
          }
        }, {
          data: 'buyer_code',
          render: function(data, type, row) {
            return data ?? '-'
          }
        }, {
          data: 'is_sold',
          render: function(data, type, row) {
            return data === true ? '<span class="text-success">Đã Bán</span>' : '<span class="text-danger">Chưa Bán</span>'
          }
        }, {
          data: 'created_at',
          render: function(data, type, row) {
            return $formatDate(data)
          }
        }, {
          data: 'staff_payment',
          render: function(data, type, row) {
            return $formatCurrency(data || 0)
          }
        }, {
          data: 'staff_status',
          render: function(data, type, row) {
            if (row.staff_name === null) return '<span class="text-success">Không cần duyệt</span>'
            return data === 'Completed' ? '<span class="text-success">Đã Duyệt</span>' : '<span class="text-danger">Chưa duyệt</span>'
          }
        }],
        columnDefs: [{
          orderable: false,
          targets: [1]
        }],
      })
    })

    const updatePriority = $debounce(function(id, val) {
        axios.post('{{ route('admin.accounts.items.update-priority') }}', {
            id: id,
            priority: val,
            _token: '{{ csrf_token() }}'
        }).then(res => {
            if (res.data.status) {
                toastr.success(res.data.message || 'Cập nhật thứ tự thành công!', 'Thành Công');
            } else {
                toastr.error(res.data.message, 'Thất Bại!');
            }
        }).catch(err => {
            toastr.error('Lỗi cập nhật ưu tiên', 'Lỗi!');
        });
    }, 0);

    $(document).on('change', '.input-priority', function() {
        const id = $(this).data('id');
        const val = $(this).val();
        updatePriority(id, val);
    });
  </script>
@endsection
