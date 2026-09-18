@extends('admin.layouts.master')
@section('title', 'Admin: User Detail')
@section('content')
  <style>
    .nav-tabs .nav-link.active {
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: bold;
        color: #7367f0;
    }
    .user-avatar-header {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      /* background-color: #696cff; */
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: bold;
    }
  </style>

  <section>
    
    {{-- HEADER SECTION --}}
    <div class="card custom-card mb-3">
      <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="user-avatar-header me-4 flex-shrink-0" style="overflow: hidden; display: flex; align-items: center; justify-content: center; border-radius: 50% !important; aspect-ratio: 1/1 !important;">
               @if(!empty($user->avatar))
                  <img src="{{ $user->avatar }}" alt="AVG" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50% !important;">
               @else
                  <img src="/images/avatar/av-1.svg" alt="Default Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50% !important;">
               @endif
            </div>
            <div>
               <h3 class="mb-1">{{ $user->username }}</h3>
               <div class="mb-2">
                 <span class="badge bg-secondary me-2">ID: {{ $user->id }}</span>
                 @if($user->status == 'active')
                    <span class="badge bg-success">Active</span>
                 @else
                    <span class="badge bg-danger">Banned</span>
                 @endif
               </div>
               <small class="text-muted"><i class="fa fa-calendar me-1"></i> Join Date: {{ $user->created_at->format('Y-m-d') }}</small>
            </div>
        </div>

        {{-- ACTION BUTTONS ROW --}}
        <div class="mt-4 pt-3 border-top">
            <button type="button" class="btn btn-outline-success me-2 mb-2" data-bs-toggle="modal" data-bs-target="#modalAddBalance"><i class="fa fa-plus me-1"></i> Cộng số dư</button>
            <button type="button" class="btn btn-outline-danger me-2 mb-2" data-bs-toggle="modal" data-bs-target="#modalSubBalance"><i class="fa fa-minus me-1"></i> Trừ số dư</button>

            <button type="button" class="btn btn-outline-primary me-2 mb-2" data-bs-toggle="modal" data-bs-target="#modalCreateTicket"><i class="fa fa-ticket me-1"></i> Create Ticket</button>
            <a href="#activity-log" class="btn btn-outline-info me-2 mb-2"><i class="fa fa-history me-1"></i> View Activity Log</a>
            <a href="#transaction-log" class="btn btn-outline-warning me-2 mb-2"><i class="fa fa-money me-1"></i> View Balance History</a>
            <a href="#orders-log" class="btn btn-outline-secondary me-2 mb-2"><i class="fa fa-shopping-cart me-1"></i> View Orders</a>
            <a href="#" class="btn btn-dark mb-2"><i class="fa fa-sign-out me-1"></i> Logout User</a>
        </div>
      </div>
    </div>

    {{-- STATS STRIP --}}
    <div class="card custom-card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4 border-end">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-primary-subtle text-primary p-3 rounded-3">
                                 <i class="fa fa-wallet fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Ví chính</p>
                            <h5 class="mb-0 text-primary">{{ number_format($user->balance) }}đ</h5>
                        </div>
                    </div>
                </div>
                 <div class="col-md-4 border-end">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-success-subtle text-success p-3 rounded-3">
                                 <i class="fa fa-dollar fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Tổng tiền nạp</p>
                            <h5 class="mb-0 text-success">{{ number_format($user->total_deposit) }}đ</h5>
                        </div>
                    </div>
                </div>
                 <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-warning-subtle text-warning p-3 rounded-3">
                                 <i class="fa fa-shopping-cart fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Đã sử dụng</p>
                            <h5 class="mb-0 text-warning">{{ number_format(max(0, $user->total_deposit - $user->balance)) }}đ</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 mt-2 pt-3 border-top">
                <div class="col-md-4 border-end">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-info-subtle text-info p-3 rounded-3">
                                 <i class="fa fa-users fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Ví CTV</p>
                            <h5 class="mb-0 text-info">{{ number_format($user->colla_balance) }}đ</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 border-end">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-secondary-subtle text-secondary p-3 rounded-3">
                                 <i class="fa fa-credit-card fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Ví Ghi Nợ</p>
                            <h5 class="mb-0 text-secondary">{{ number_format($user->balance_1) }}đ</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-danger-subtle text-danger p-3 rounded-3">
                                 <i class="fa fa-refresh fs-4"></i>
                            </span>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Hoàn Tiền</p>
                            <h5 class="mb-0 text-danger">{{ number_format($user->balance_2) }}đ</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- MAIN TABS --}}
    <div class="card custom-card">
      <div class="card-body">
        
        <style>
            .nav-pills .nav-link {
                color: #52526C;
                font-weight: 500;
                padding: 10px 20px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .nav-pills .nav-link.active {
                background-color: #f3f2ff; /* Light purple */
                color: #7366ff; /* Primary purple */
                font-weight: 600;
            }
            .nav-pills .nav-link i {
                font-size: 16px;
            }
        </style>
        @php
            $requestedTab = request()->query('category', 'basic');
            $tabMap = [
                'basic' => 'basic',
                'security' => 'security',
                'power' => 'permissions',
                'system' => 'system',
            ];
            $currentTab = array_key_exists($requestedTab, $tabMap) ? $tabMap[$requestedTab] : 'basic';
        @endphp
        <ul class="nav nav-pills mb-4 gap-3" id="userTab" role="tablist">
          <li class="nav-item">
            <button class="nav-link {{ $currentTab == 'basic' ? 'active' : '' }}" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic" type="button">
                <i class="fa fa-user"></i> Thông tin cơ bản
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link {{ $currentTab == 'security' ? 'active' : '' }}" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button">
                <i class="fa fa-shield"></i> Bảo mật
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link {{ $currentTab == 'permissions' ? 'active' : '' }}" id="permissions-tab" data-bs-toggle="pill" data-bs-target="#permissions" type="button">
                 <i class="fa fa-user-tag"></i> Quyền hạn
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link {{ $currentTab == 'system' ? 'active' : '' }}" id="system-tab" data-bs-toggle="pill" data-bs-target="#system" type="button">
                <i class="fa fa-desktop"></i> Thông tin hệ thống
            </button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">
          
          {{-- TAB 1: BASIC INFORMATION --}}
          <div class="tab-pane fade {{ $currentTab == 'basic' ? 'show active' : '' }}" id="basic" role="tabpanel">
            <form action="{{ route('admin.users.update', ['id' => $user->id]) }}" method="POST" class="axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="action" value="update-info">
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Username <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Full name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="full_name" value="{{ $user->full_name }}" required>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" class="form-control" name="phone" value="{{ $user->phone }}">
                </div>
              </div>

             <div class="row mb-3">
                <div class="col-md-6">
                   <label class="form-label">Giới tính</label>
                   <select class="form-control" name="gender">
                       <option value="female" {{ ($user->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                       <option value="lesbian" {{ ($user->gender) == 'lesbian' ? 'selected' : '' }}>Lesbian (Đồng tính nữ)</option>
                       <option value="male" {{ ($user->gender) == 'male' ? 'selected' : '' }}>Nam 🏳️‍🌈</option>
                       <option value="bisexual" {{ ($user->gender) == 'bisexual' ? 'selected' : '' }}>Bisexual (Song tính)</option>
                       <option value="gay" {{ (empty($user->gender) || $user->gender == 'gay' || $user->gender == 'other') ? 'selected' : '' }}>Gay (Đồng tính nam)</option>
                       <option value="transgender" {{ ($user->gender) == 'transgender' ? 'selected' : '' }}>Transgender (Chuyển giới) 🏳️‍⚧️</option>
                   </select>
                </div>
                <div class="col-md-6">
                   <label class="form-label">Referrer ID</label>
                   <input type="text" class="form-control" value="{{ $user->referral_by ?? 'N/A' }}" readonly>
                </div>
             </div>

              <div class="mt-4 d-flex justify-content-end">
                 <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Cập nhật</button>
              </div>
            </form>
          </div>

          {{-- TAB 2: SECURITY --}}
          <div class="tab-pane fade {{ $currentTab == 'security' ? 'show active' : '' }}" id="security" role="tabpanel">
             <form action="{{ route('admin.users.update', ['id' => $user->id]) }}" method="POST" class="axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="action" value="update-info"> <!-- Using same action for now, assuming unified update -->

                <div class="mb-3">
                   <label class="form-label fw-bold">Token <span class="text-danger">*</span></label>
                   <div class="input-group">
                       <span class="input-group-text bg-white"><i class="fa fa-key"></i></span>
                       <input type="password" class="form-control" value="{{ $user->access_token }}" readonly>
                       <button class="btn btn-primary toggle-visibility" type="button"><i class="fa fa-eye"></i></button>
                   </div>
                   <small class="text-warning mt-1 d-block"><i class="fa fa-exclamation-triangle"></i> Bảo mật thông tin này vì kẻ xấu có thể thực hiện đăng nhập tài khoản bằng Token</small>
               </div>

               <div class="row mb-3">
                   <div class="col-md-6">
                       <label class="form-label fw-bold">Mật khẩu mới</label>
                       <div class="input-group">
                           <span class="input-group-text bg-light"><i class="fa fa-lock"></i></span>
                           <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu mới">
                           <button class="btn btn-outline-secondary toggle-visibility" type="button"><i class="fa fa-eye"></i></button>
                       </div>
                       <small class="text-muted mt-1 d-block"><i class="fa fa-info-circle"></i> Bỏ trống nếu không muốn thay đổi mật khẩu</small>
                   </div>
                   <div class="col-md-6">
                       <label class="form-label fw-bold">Secret Key Google 2FA</label>
                       <div class="input-group">
                           <span class="input-group-text bg-light"><i class="fa fa-shield"></i></span>
                           <input type="password" class="form-control" value="{{ $user->google2fa_secret ?? 'Chưa kích hoạt' }}" readonly>
                           <button class="btn btn-outline-secondary toggle-visibility" type="button"><i class="fa fa-eye"></i></button>
                       </div>
                        <small class="text-danger mt-1 d-block"><i class="fa fa-exclamation-circle"></i> Lộ thông tin này có thể khiến kẻ xấu bỏ qua bước xác minh 2FA.</small>
                   </div>
               </div>

              <div class="mt-4 d-flex justify-content-end">
                 <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Cập nhật</button>
              </div>
            </form>
          </div>

          {{-- TAB 3: PERMISSIONS --}}
          <div class="tab-pane fade {{ $currentTab == 'permissions' ? 'show active' : '' }}" id="permissions" role="tabpanel">
             <form action="{{ route('admin.users.update', ['id' => $user->id]) }}" method="POST" class="axios-form" data-reload="true">
              @csrf
              <input type="hidden" name="action" value="update-info">

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Loại tài khoản</label>
                  <select class="form-control category-select" name="role[]" id="role-select" multiple>
                    @php 
                      $currentRoles = explode(',', $user->role ?? '');
                      $currentRoles = array_map('trim', $currentRoles);
                    @endphp
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" @if (in_array(trim($r->name), $currentRoles)) selected @endif>{{ $r->name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Trạng thái</label>
                  <select class="form-control" name="status">
                    <option value="active" @if ($user->status == 'active') selected @endif>Hoạt động</option>
                    <option value="locked" @if ($user->status == 'locked') selected @endif>Đã khóa</option>
                  </select>
                </div>
              </div>

              <!-- PARTNER FIELDS -->
              <div id="partner-fields" style="display: {{ (is_array($currentRoles) && (in_array('partner', $currentRoles) || in_array('Đối tác', $currentRoles) || in_array('Đối Tác', $currentRoles))) ? 'block' : 'none' }}">
                  <div class="row mb-3">
                      <div class="col-md-12">
                          <label class="form-label fw-bold">Tên miền quản lý (Domain)</label>
                          <select name="domain" class="form-control" id="domain-select">
                              <option value="">-- Chọn tên miền --</option>
                              @foreach($available_domains as $d)
                                  <option value="{{ $d }}" {{ $user->domain === $d ? 'selected' : '' }}>{{ $d }}</option>
                              @endforeach
                          </select>
                          <small class="text-muted">Chỉ dành cho tài khoản Đối tác. Họ sẽ quản lý và xem thống kê của tên miền này.</small>
                      </div>
                  </div>
              </div>

              <div id="collaborator-fields" style="display: {{ (is_array($currentRoles) && (in_array('collaborator', $currentRoles) || in_array('Cộng tác viên', $currentRoles) || in_array('Cộng Tác Viên', $currentRoles))) ? 'block' : 'none' }}">
                <div class="row mb-3">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Cộng tác viên</label>
                    <select name="colla_type[]" class="form-control category-select" multiple>
                      @php $selectedCollaTypes = (array) ($user->colla_type ?? []); @endphp
                      <option value="items" @if (in_array('items', $selectedCollaTypes)) selected @endif>Vật phẩm</option>
                      <option value="account" @if (in_array('account', $selectedCollaTypes)) selected @endif>Tài khoản</option>
                      <option value="boosting" @if (in_array('boosting', $selectedCollaTypes)) selected @endif>Cày thuê</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">% Hoa hồng mỗi đơn</label>
                    <input type="number" class="form-control" name="colla_percent" value="{{ $user->colla_percent ?? 0 }}" min="0" max="100">
                  </div>
                </div>
                <div class="row mb-3" id="account-groups-wrapper" style="display: {{ (is_array($user->colla_type) && in_array('account', $user->colla_type)) ? 'block' : 'none' }}">
                  <div class="col-md-12">
                    <label class="form-label">Danh sách nhóm tài khoản được phép bán</label>
                    <select name="staff_group_ids[]" class="form-control category-select" multiple>
                      @php $selectedStaffGroups = (array) ($user->staff_group_ids ?? []); @endphp
                      @foreach ($account_groups as $group)
                        <option value="{{ $group->id }}" @if (in_array($group->id, $selectedStaffGroups)) selected @endif>{{ $group->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

               <div class="mt-4 d-flex justify-content-end">
                 <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Cập nhật</button>
              </div>
            </form>
          </div>

          {{-- TAB 4: SYSTEM INFORMATION --}}
          <div class="tab-pane fade {{ $currentTab == 'system' ? 'show active' : '' }}" id="system" role="tabpanel">
             <div class="row mb-3">
                <div class="col-md-6 mb-3">
                   <label class="form-label">IP Address</label>
                   <input type="text" class="form-control" value="{{ $user->ip_address }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                   <label class="form-label">IP WebRTC</label>
                   <input type="text" class="form-control" value="{{ $user->webrtc_ip ?? 'Không xác định' }}" readonly>
                </div>
                 <div class="col-md-6 mb-3">
                   <label class="form-label fw-bold">Thiết bị đăng nhập</label>
                   <div class="input-group">
                       <span class="input-group-text bg-light"><i class="fa fa-desktop"></i></span>
                       <input type="text" class="form-control" value="{{ $user->user_agent ?? 'Chưa xác định' }}" readonly>
                   </div>
                </div>
                 <div class="col-md-6 mb-3">
                    <label class="form-label">Register Date</label>
                    <input type="text" class="form-control" value="{{ $user->created_at }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Registered Domain</label>
                    <input type="text" class="form-control" value="{{ $user->domain ?? 'System' }}" readonly>
                </div>
                 <div class="col-md-6 mb-3">
                   <label class="form-label">Last Login Time</label>
                   <input type="text" class="form-control" value="{{ $user->last_login_at ?? 'Never' }}" readonly>
                </div>
             </div>
          </div>

        </div>
      </div>
    </div>
    
     <!-- HISTORY TABLES PRESERVED BELOW (Using IDs for anchor links) -->
    <div class="card custom-card mt-4" id="transaction-log">
      <div class="card-header justify-content-between">
        <div class="card-title">Latest Transactions</div>
      </div>
      <div class="card-body">
        <div class="table-responsive theme-scrollbar">
          <table class="display table table-bordered table-stripped text-nowrap datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Type</th>
                <th>Code</th>
                <th>Amount</th>
                <th>Balance Before</th>
                <th>Balance After</th>
                <th>Content</th>
                <th>Status</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($user->transactions()->orderBy('id', 'desc')->limit(50)->get() as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->username }}</td>
                  <td>{!! Helper::formatTransType($item->type) !!}</td>
                  <td>{{ $item->code }}</td>
                  <td>{{ $item->prefix . ' ' . Helper::formatCurrency($item->amount) }}</td>
                  <td>{{ Helper::formatCurrency($item->balance_before) }}</td>
                  <td>{{ Helper::formatCurrency($item->balance_after) }}</td>
                  <td class="text-wrap">{{ $item->content }} </td>
                  <td>{!! Helper::formatStatus($item->status) !!}</td>
                  <th>{{ $item->created_at }}</th>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card custom-card mt-4" id="activity-log">
      <div class="card-header justify-content-between">
        <div class="card-title">Activity Log</div>
      </div>
      <div class="card-body">
         <div class="table-responsive theme-scrollbar">
          <table class="display table table-bordered table-stripped text-nowrap datatable">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Content</th>
                <th>IP</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($user->histories()->orderBy('id', 'desc')->limit(50)->get() as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->username }}</td>
                  <td>{{ $item->content }}</td>
                  <td>{{ $item->ip_address }}</td>
                  <td>{{ $item->created_at }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </section>

  {{-- MODALS FOR ADD/SUB BALANCE --}}
  <div class="modal fade" id="modalAddBalance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cộng tiền vào tài khoản</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.users.update', ['id' => $user->id]) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <input type="hidden" name="action" value="plus-money">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Loại ví</label>
                    <select class="form-control" name="wallet_type">
                        <option value="balance">Ví chính ({{ number_format($user->balance) }}đ)</option>
                        <option value="colla_balance" @if($user->role === 'collaborator') selected @endif>Ví CTV ({{ number_format($user->colla_balance) }}đ)</option>
                        <option value="balance_1">Ví Ghi Nợ ({{ number_format($user->balance_1) }}đ)</option>
                        <option value="balance_2">Hoàn Tiền ({{ number_format($user->balance_2) }}đ)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số tiền cần cộng</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-money"></i></span>
                        <input type="number" class="form-control" name="amount" required min="1" placeholder="Nhập số tiền">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lý do cộng tiền (Không bắt buộc)</label>
                    <textarea class="form-control" name="reason" placeholder="Nhập lý do"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-success">Xác nhận cộng</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalSubBalance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Trừ tiền tài khoản</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
         <form action="{{ route('admin.users.update', ['id' => $user->id]) }}" method="POST" class="axios-form" data-reload="true">
            @csrf
            <input type="hidden" name="action" value="sub-money">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Loại ví</label>
                    <select class="form-control" name="wallet_type">
                        <option value="balance">Ví chính ({{ number_format($user->balance) }}đ)</option>
                        <option value="colla_balance" @if($user->role === 'collaborator') selected @endif>Ví CTV ({{ number_format($user->colla_balance) }}đ)</option>
                        <option value="balance_1">Ví Ghi Nợ ({{ number_format($user->balance_1) }}đ)</option>
                        <option value="balance_2">Hoàn Tiền ({{ number_format($user->balance_2) }}đ)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số tiền cần trừ</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-money-bill-wave"></i></span>
                        <input type="number" class="form-control" name="amount" required min="1" placeholder="Nhập số tiền muốn trừ">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lý do trừ tiền (Không bắt buộc)</label>
                    <textarea class="form-control" name="reason" placeholder="Nhập lý do"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-danger">Xác nhận trừ</button>
            </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalCreateTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tạo Ticket hỗ trợ cho {{ $user->username }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.tickets.store') }}" method="POST" id="form-create-ticket">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" class="form-control" name="title" required placeholder="Nhập tiêu đề ticket">
                </div>
                @php
                    $ticket_config = Helper::getConfig('ticket_config');
                    $raw_categories = $ticket_config['ticket_categories'] ?? '';
                    $categories = preg_split('/[\r\n,]+/', $raw_categories, -1, PREG_SPLIT_NO_EMPTY);
                    $categories = array_map('trim', $categories);
                    $categories = array_filter($categories, function($c) { return $c !== ''; });
                @endphp
                
                @if(count($categories) > 0)
                <div class="mb-3">
                    <label class="form-label">Chủ đề / Category</label>
                    <select class="form-control" name="category" required>
                            @foreach($categories as $cat)
                                <option value="{{ trim($cat) }}">{{ trim($cat) }}</option>
                            @endforeach
                    </select>
                </div>
                @else
                    <input type="hidden" name="category" value="General">
                @endif
                <div class="mb-3">
                    <label class="form-label">Nội dung / Message</label>
                    <textarea class="form-control" name="content" required rows="5" placeholder="Nhập nội dung tin nhắn ban đầu"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">Tạo Ticket</button>
            </div>
        </form>
      </div>
    </div>
  </div>


@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Custom Ticket Creation Handler
        $('#form-create-ticket').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            const originalText = btn.text();
            
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            axios.post(form.attr('action'), new FormData(this))
                .then(res => {
                    if (res.data.status) {
                        toastr.success(res.data.message);
                        /* Redirect to Ticket Detail */
                        window.location.href = "{{ route('admin.tickets') }}?messages=" + res.data.data.id;
                    } else {
                        toastr.error(res.data.message || 'Error creating ticket');
                        btn.prop('disabled', false).text(originalText);
                    }
                })
                .catch(err => {
                    console.error(err);
                    toastr.error('Failed to create ticket');
                    btn.prop('disabled', false).text(originalText);
                });
        });

        $(document).on('click', '.toggle-visibility', function() {
            let btn = $(this);
            let input = btn.closest('.input-group').find('input');
            let icon = btn.find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Update URL on Tab Click
        const tabMap = {
            'basic-tab': 'basic',
            'security-tab': 'security',
            'permissions-tab': 'power',
            'system-tab': 'system'
        };
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
            const tabId = e.target.id;
            const category = tabMap[tabId];
            if (category) {
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('category', category);
                window.history.pushState({}, '', newUrl);
            }
        });

        // Collaborator Fields Toggle
        $('#role-select').on('change', function() {
            let selected = $(this).val(); 
            // Select2/Choices returns array or null.
            if (!selected) selected = [];
            if (!Array.isArray(selected)) selected = [selected];

            if (selected.includes('collaborator') || selected.includes('Cộng tác viên') || selected.includes('Cộng Tác Viên')) {
                $('#collaborator-fields').slideDown();
            } else {
                $('#collaborator-fields').slideUp();
            }

            if (selected.includes('partner') || selected.includes('Đối tác') || selected.includes('Đối Tác')) {
                $('#partner-fields').slideDown();
            } else {
                $('#partner-fields').slideUp();
            }
        });

        // Trigger change on load
        $('#role-select').trigger('change');

        // Initialize Choices.js
        $('.category-select').each(function() {
            const choices = new Choices(this, {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: '-- Chọn --'
            });

            // Handle colla_type multi-select change to toggle account groups
            if ($(this).attr('name') === 'colla_type[]') {
                this.addEventListener('change', function(event) {
                    const selectedValues = choices.getValue(true);
                    if (selectedValues.includes('account')) {
                        $('#account-groups-wrapper').slideDown();
                    } else {
                        $('#account-groups-wrapper').slideUp();
                    }
                });
            }
        });
    });
</script>
@endsection
