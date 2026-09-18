@if($ticket)
<div class="p-3">
    <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 12px;">Ticket Info</h6>
    <div class="mb-3">
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">ID:</span>
            <span class="fw-bold">#{{ $ticket->id }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Status:</span>
            <span class="badge {{ $ticket->status == 'open' ? 'bg-success' : ($ticket->status == 'closed' ? 'bg-danger' : 'bg-warning') }}">
                {{ $ticket->status == 'open' ? 'Đang mở' : ($ticket->status == 'closed' ? 'Đóng' : 'Đang chờ') }}
            </span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Category:</span>
            <span>{{ $ticket->category }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Created:</span>
            <span>{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Updated:</span>
            <span>{{ $ticket->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <hr>

    <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 12px;">Customer Info</h6>
    <div class="mb-3">
        <div class="d-flex align-items-center mb-2">
            <img src="{{ !empty($ticket->user->avatar) ? $ticket->user->avatar : asset('/images/avatar/av-1.svg') }}" 
                 class="rounded-circle me-2" 
                 width="30" height="30" 
                 style="border-radius: 50% !important; aspect-ratio: 1/1 !important; object-fit: cover;">
            <span class="fw-bold">{{ $ticket->user->username ?? 'Unknown' }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Balance:</span>
            <span class="text-success fw-bold">{{ number_format($ticket->user->balance ?? 0) }} đ</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Total Spent:</span>
            <span>{{ number_format($ticket->user->total_deposit ?? 0) }} đ</span>
        </div>
         <div class="mt-2">
            <a href="{{ route('admin.users.edit', $ticket->user_id) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">Edit Member</a>
        </div>
    </div>

    <hr>
    
    <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 12px;">Admin Note</h6>
    <div class="mb-2">
         <textarea class="form-control" rows="3" placeholder="Private note..." id="admin-note-input" onblur="saveAdminNote()">{{ $ticket->admin_note }}</textarea>
         <small class="text-muted">Only visible to admin</small>
    </div>
</div>
@else
<div class="p-3 text-muted text-center">
    No info
</div>
@endif
