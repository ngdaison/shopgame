@extends('admin.layouts.master')
@section('title', 'Thống Kê Chiến Dịch')
@section('content')

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">Danh Sách Chiến Dịch</div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#campaignModal" onclick="resetModal()">
            <i class="fa fa-plus"></i> Tạo Chiến Dịch Mới
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
             <i class="fa fa-info-circle me-2 fs-4"></i>
             <div>
                 Hệ thống tự động ghi nhận <code>?a=...</code> hoặc <code>?utm_source=...</code> từ đường dẫn giới thiệu.
             </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Chiến Dịch</th>
                        <th>Theo Dõi Link</th>
                        <th>Thống Kê</th>
                        <th>Trạng Thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-primary">{{ $campaign->name }}</div>
                                <div class="small text-muted">Mã: <code class="text-danger">{{ $campaign->tracking_code }}</code></div>
                                <div class="small text-muted">Hoa hồng: <span class="text-success">{{ $campaign->comm_percent }}%</span> ({{ $campaign->commission_type === 'deposit' ? 'Nạp tiền' : 'Đơn hàng' }})</div>
                             </td>
                            <td>
                                <div class="input-group input-group-sm mb-1" style="max-width: 300px;">
                                    <input type="text" class="form-control" value="{{ url('/?a=' . $campaign->tracking_code) }}" readonly id="link-{{ $campaign->id }}">
                                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('link-{{ $campaign->id }}')">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                                @if($campaign->referral_link)
                                <div class="small text-muted text-truncate" style="max-width: 300px;">Đích: {{ $campaign->referral_link }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-info-transparent" title="Clicks">
                                        <i class="fa fa-mouse-pointer me-1"></i> {{ number_format($campaign->clicks) }}
                                    </span>
                                    <span class="badge bg-success-transparent" title="Đăng ký">
                                        <i class="fa fa-user-plus me-1"></i> {{ number_format($campaign->registrations) }}
                                    </span>
                                    <span class="badge bg-primary-transparent" title="Đơn hàng">
                                        <i class="fa fa-shopping-cart me-1"></i> {{ number_format($campaign->orders) }}
                                    </span>
                                </div>
                                <div class="mt-1 small fw-bold text-primary">
                                    Doanh thu: {{ Helper::formatCurrency($campaign->total_commission) }}
                                </div>
                            </td>
                            <td>
                                @if($campaign->status)
                                    <span class="badge bg-success">Đang chạy</span>
                                @else
                                    <span class="badge bg-danger">Dừng</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.statistical.show', $campaign->id) }}" class="btn btn-info" title="Xem chi tiết">
                                        <i class="fa fa-chart-bar"></i>
                                    </a>
                                    <button class="btn btn-warning" onclick="editCampaign({{ json_encode($campaign) }})" title="Chỉnh sửa">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="deleteCampaign({{ $campaign->id }})" title="Xóa">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <span class="text-muted">Chưa có dữ liệu chiến dịch nào.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>

<!-- Campaign Modal -->
<div class="modal fade" id="campaignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="campaignModalTitle">Tạo Chiến Dịch Mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="campaignForm" action="{{ route('admin.statistical.store') }}" method="POST" class="default-form axios-form" data-reload="true">
          @csrf
          <input type="hidden" id="campaign_id" name="id">
          <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên Chiến Dịch</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="VD: Quảng cáo Facebook 01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mã Theo Dõi (a=...)</label>
                    <input type="text" class="form-control" name="tracking_code" id="tracking_code" placeholder="VD: fb01" required>
                    <small class="text-muted">Chỉ dùng chữ cái, số, gạch dưới và gạch ngang.</small>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Link Đích (Tùy chọn)</label>
                    <input type="text" class="form-control" name="referral_link" id="referral_link" placeholder="VD: /vat-pham/robux">
                    <small class="text-muted">Nếu không nhập, hệ thống sẽ mặc định chuyển hướng về trang chủ.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tính Hoa Hồng</label>
                    <select class="form-select" name="commission_type" id="commission_type">
                        <option value="deposit">Nạp Tiền</option>
                        <option value="order">Đơn Hàng</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng Thái</label>
                    <select class="form-select" name="status" id="status">
                        <option value="1">Hoạt động</option>
                        <option value="0">Tạm dừng</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phần trăm hoa hồng chuyển khoản</label>
                    <input type="number" class="form-control" name="comm_percent" id="comm_percent" step="0.01" value="0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giới Hạn Nhận</label>
                    <select class="form-select" name="limit_mode" id="limit_mode">
                        <option value="count">Số Lần</option>
                        <option value="days">Số Ngày</option>
                        <option value="both">Số Ngày + Số Lần</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="group_limit_days">
                    <label class="form-label">Giới Hạn Số Ngày</label>
                    <input type="number" class="form-control" name="limit_days" id="limit_days" value="0">
                    <small class="text-muted" id="help_limit_days">Số ngày kể từ khi thành viên đăng ký được phép nhận hoa hồng.</small>
                </div>
                <div class="col-md-6 mb-3" id="group_limit_count">
                    <label class="form-label">Giới Hạn Số Lần</label>
                    <input type="number" class="form-control" name="limit_count" id="limit_count" value="0">
                    <small class="text-muted" id="help_limit_count">Số lần tối đa được nhận hoa hồng từ mỗi thành viên.</small>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary" id="btnSave">
                <i class="fa fa-save"></i> Lưu Lại
            </button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    const campaignModal = new bootstrap.Modal(document.getElementById('campaignModal'));

    function resetModal() {
        document.getElementById('campaignForm').reset();
        document.getElementById('campaignForm').action = `{{ route('admin.statistical.store') }}`;
        document.getElementById('campaign_id').value = '';
        document.getElementById('campaignModalTitle').innerText = 'Tạo Chiến Dịch Mới';
    }

    function editCampaign(campaign) {
        resetModal();
        document.getElementById('campaignForm').action = `{{ url('admin/statistical/update') }}/${campaign.id}`;
        document.getElementById('campaignModalTitle').innerText = 'Chỉnh Sửa Chiến Dịch: ' + campaign.name;
        document.getElementById('campaign_id').value = campaign.id;
        document.getElementById('name').value = campaign.name;
        document.getElementById('tracking_code').value = campaign.tracking_code;
        document.getElementById('referral_link').value = campaign.referral_link;
        document.getElementById('commission_type').value = campaign.commission_type || 'deposit';
        document.getElementById('status').value = campaign.status ? '1' : '0';
        document.getElementById('comm_percent').value = campaign.comm_percent;
        document.getElementById('limit_mode').value = campaign.limit_mode;
        document.getElementById('limit_days').value = campaign.limit_days;
        document.getElementById('limit_count').value = campaign.limit_count;
        
        updateVisibility();
        campaignModal.show();
    }

    // Modal Visibility Toggle
    const limitMode = document.getElementById('limit_mode');
    const groupCount = document.getElementById('group_limit_count');
    const groupDays = document.getElementById('group_limit_days');
    const helpDays = document.getElementById('help_limit_days');
    const helpCount = document.getElementById('help_limit_count');

    function updateVisibility() {
        const val = limitMode.value;
        if (val === 'count') {
            groupCount.style.display = 'block';
            groupDays.style.display = 'none';
            helpCount.innerText = 'Số lần tối đa được nhận hoa hồng từ mỗi thành viên';
        } else if (val === 'days') {
            groupCount.style.display = 'none';
            groupDays.style.display = 'block';
            helpDays.innerText = 'Số ngày kể từ khi thành viên đăng ký được phép nhận hoa hồng';
        } else if (val === 'both') {
            groupCount.style.display = 'block';
            groupDays.style.display = 'block';
            helpDays.innerText = 'Số lần tối đa được nhận hoa hồng từ mỗi thành viên';
            helpCount.innerText = 'Số lần tối đa trong khoảng thời gian trên';
        }
    }

    limitMode.addEventListener('change', updateVisibility);
    updateVisibility();

    function deleteCampaign(id) {
        if (confirm('Bạn có chắc chắn muốn xóa chiến dịch này? Dữ liệu thống kê cũng sẽ bị xóa.')) {
            if (typeof pageOverlay !== 'undefined') pageOverlay.show();
            axios.post(`{{ url('admin/statistical/delete') }}/${id}`)
            .then(response => {
                if (response.data.status) {
                    sessionStorage.setItem('pending_notification', JSON.stringify({
                        type: 'success',
                        title: 'Thành Công',
                        message: response.data.message
                    }));
                    location.reload();
                } else {
                    if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
                    toastr.error(response.data.message, 'Thất Bại');
                }
            })
            .catch(error => {
                if (typeof pageOverlay !== 'undefined') pageOverlay.hide();
                toastr.error($catchMessage(error), 'Thất Bại');
            });
        }
    }

    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => {
            toastr.success('Đã sao chép link!');
        });
    }
</script>
@endsection
