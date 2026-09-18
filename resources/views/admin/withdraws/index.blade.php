@extends('admin.layouts.master')
@section('title', $pageTitle)
@section('content')
  <div class="card custom-card">
    <div class="card-header justify-content-between">
      <div class="card-title">Danh sách yêu cầu rút thưởng [MỚI]</div>
    </div>
    <div class="card-body">
      <div class="table-responsive theme-scrollbar">
        <table class="display table table-bordered table-stripped text-nowrap datatable">
          <thead>
            <tr>
              <th>#</th>
              <th data-orderable="false" style="width: 30px">Thao tác</th>
              <th>Mã đơn</th>
              <th>Số lượng</th>
              <th>Trò chơi</th>
              <th>Dữ liệu</th>
              <th>Trạng thái</th>
              <th>Người tạo</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($records as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td class="text-center">
                  <a href="javascript:void(0)" class="badge bg-danger-gradient" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $item->id }}">Sửa</a>
                </td>
                <td>{{ $item->code }}</td>
                <td>{{ number_format($item->amount) }} {{ $item->unit }}</td>
                <td>{{ $item->name }}</td>
                <td>
                  @if($item->user_inputs)
                    @foreach ($item->user_inputs as $input)
                      <span class="me-2">{{ $input['label'] ?? '' }}: <i class="copy cursor-pointer" data-clipboard-text="{{ $input['value'] ?? '' }}">{{ $input['value'] ?? '' }}</i></span>
                    @endforeach
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if ($item->status === 'Pending')
                    <span class="badge bg-warning">Đang chờ</span>
                  @elseif ($item->status === 'Approved')
                    <span class="badge bg-success">Đã duyệt</span>
                  @else
                    <span class="badge bg-danger">Từ chối</span>
                  @endif
                </td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
              </tr>

              <!-- Modal -->
              <div class="modal fade" id="modal-edit-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Cập nhật thông tin #{{ $item->id }}</h5>
                      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('admin.withdraws.update', ['id' => $item->id]) }}" method="POST" enctype="multipart/form-data" class="axios-form" data-reload="1">
                        @csrf
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="amount" class="form-label">Số lượng</label>
                            <input class="form-control" type="text" id="amount" name="amount" value="{{ number_format($item->amount) }}" disabled>
                          </div>
                          <div class="col-md-6">
                            <label for="unit" class="form-label">Đơn vị</label>
                            <input class="form-control" type="text" id="unit" name="unit" value="{{ $item->unit }}" disabled>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label for="status" class="form-label">Trạng thái</label>
                          <select class="form-control" id="status" name="status">
                            <option value="Pending" {{ $item->status === 'Pending' ? 'selected' : '' }}>Đang chờ</option>
                            <option value="Approved" {{ $item->status === 'Approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="Rejected" {{ $item->status === 'Rejected' ? 'selected' : '' }}>Từ chối</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label for="admin_note" class="form-label">Ghi chú</label>
                          <textarea name="admin_note" id="admin_note" class="form-control" rows="4">{{ $item->admin_note }}</textarea>
                        </div>
                        <div class="mb-3">
                          <button class="btn btn-primary w-100" type="submit">Cập nhật</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="9" class="text-center">No data available in table</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
