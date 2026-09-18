@extends('admin.layouts.master')
@section('title', 'Admin: Accounts Resource')
@section('content')
  <style>
    .modal {
      z-index: 99999;
    }
  </style>
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh sách tài khoản còn trong kho "{{ $item->name }}" - Nhóm "{{ $item->group?->name ?? '-' }}"</div>
    </div>

    <div class="card-body">
      <div class="text-center">
        <button class="btn btn-danger action-ids" onclick="deleteList()">Xoá <span class="checked-ids">0</span> tài khoản</button>
        <button class="btn btn-success action-ids" onclick="exportList()">Xuất <span class="checked-ids">0</span> tài khoản</button>
      </div>
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-center datatable1">
          <thead>
            <tr>
              <th data-sortable="false" style="width: 10px">
                <input type="checkbox" name="checked_all">
              </th>
              <th style="max-width: 30px">STT</th>
              <th>Tài khoản:Mật khẩu:Authen/Cookie</th>
              <th>Mã đơn</th>
              <th>Người mua</th>
              <th>Thanh toán</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($item->resources as $value)
              <tr>
                <td>
                  <input type="checkbox" name="checked_ids[]" value="{{ $value->id }}">
                </td>
                <td>{{ $value->id }}</td>
                <td class="text-start">
                   <div class="p-2 bg-light border rounded mb-0" style="word-break: break-all; white-space: pre-wrap; font-family: monospace; font-size: 13px;">{{ $value->username }}</div>
                </td>
                <td>{{ $value->buyer_code ?? '-' }}</td>
                <td>{{ $value->buyer_name ?? '-' }}</td>
                <td>{{ Helper::formatCurrency($value->buyer_paym) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer">
      <a href="{{ route('admin.accountsv2.items', ['id' => $item->group_id]) }}" class="btn btn-danger-gradient"><i class="fa fa-arrow-left"></i> Quay lại</a>
      <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-create" class="btn btn-primary-gradient">Thêm tài khoản</a>
    </div>
  </div>

  <div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Thêm tài khoản mới</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.accountsv2.resources.store', ['id' => $item->id]) }}" method="POST" class="default-form axios-form" data-reload="true">
            @csrf
            <div class="mb-3">
              <label for="accounts" class="form-label">Danh Sách Tài Khoản</label>
              <textarea class="form-control" id="accounts" name="accounts" rows="10" placeholder="Tài khoản:Mật khẩu:Authen/Cookie"></textarea>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('scripts')
  <script>

    $("[name=checked_all]").change(function(e) {
      if ($(this).is(":checked")) {
        $("[name='checked_ids[]']").prop("checked", true)
      } else {
        $("[name='checked_ids[]']").prop("checked", false)
      }
    })

    function getIds() {
      let ids = []
      $("[name='checked_ids[]']:checked").each(function() {
        ids.push($(this).val())
      })
      return ids
    }

    function setActions() {
      let ids = getIds()

      if (ids.length > 0) {
        $(".action-ids").prop("disabled", false)
        $(".checked-ids").text(ids.length)
      } else {
        $(".action-ids").prop("disabled", true)
        $(".checked-ids").text(0)
      }
    }

    $(document)
      .ready(function() {
        setActions();
      })
      .on('change', 'input[name="checked_all"]:enabled', function() {
        setActions();
      })
      .on('change', 'input[name="checked_ids[]"]:enabled', function() {
        setActions();
      })

    $(document).ready(() => {

      $('.datatable1').DataTable({
        "order": [
          [1, "desc"]
        ],
        "columnDefs": [{
          "targets": [0, 2],
          "orderable": false
        }],
        lengthMenu: [
          [10, 25, 50, 100, 250, 500, 1000, 2000, 5000, -1],
          [10, 25, 50, 100, 250, 500, 1000, 2000, 5000, "All"]
        ],
      })
    })

    //

    function exportList() {
      let ids = getIds();
      if (ids.length == 0) {
        return toastr.error('Vui lòng chọn ít nhất 1 tài khoản để xuất file!', 'Thất Bại');
        return false;
      }
      axios.post(`{{ route('admin.accountsv2.resources.export') }}`, {
          ids: ids,
        })
        .then(({
          data: res
        }) => {
          // blob download
          let blob = new Blob([res.data], {
            type: 'application/octet-stream'
          });
          let link = document.createElement('a');
          link.href = window.URL.createObjectURL(blob);
          link.download = `${res.name}.txt`;
          link.click();
          link.remove();
        })
        .catch(error => toastr.error($catchMessage(error), 'Thất Bại'))
    }

    const deleteList = () => {
      let ids = getIds();
      if (ids.length == 0) {
        return toastr.error('Vui lòng chọn ít nhất 1 tài khoản để xoá!', 'Thất Bại');
        return false;
      }

      Swal.fire({
        icon: 'warning',
        title: 'Bạn chắc chứ?',
        text: 'Bạn không thể hoàn tác thao tác này!',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        reverseButtons: true,
        preConfirm: () => {
          $showLoading();
          return axios.post('{{ route('admin.accountsv2.resources.delete') }}', {
            ids: ids
          }).then(({
            data: res
          }) => {
            if (res.status) {
              sessionStorage.setItem('pending_notification', JSON.stringify({
                type: 'success',
                title: 'Thành công',
                message: res.message || 'Thao tác thành công'
              }));
              location.reload();
            } else {
              $hideLoading();
              toastr.error(res.message, 'Thất bại');
            }
          }).catch(err => {
            $hideLoading();
            toastr.error($catchMessage(err), 'Thất bại');
          })
        },
        allowOutsideClick: () => !Swal.isLoading()
      })
    }
  </script>
@endsection
