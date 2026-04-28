@extends('layouts.admin')

@section('title', 'Semua Notifikasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-6">
            <h4 class="fw-bold mb-1"><i class="icon-base ri ri-notification-4-line me-2 text-primary"></i>Semua Notifikasi</h4>
            <p class="text-muted mb-0 small">Lihat riwayat lengkap peringatan dan error di dalam sistem.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="javascript:void(0)" onclick="markAllAsReadPage()" class="btn btn-outline-secondary shadow-sm">
                <i class="icon-base ri ri-check-double-line me-1"></i> Tandai Semua Dibaca
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('notifications.index') }}" method="GET" class="d-flex align-items-center">
                <label class="form-label me-3 mb-0 fw-bold text-muted text-uppercase small">Filter Status:</label>
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">Semua Notifikasi</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card shadow-sm border-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $data = $notif->data;
                    $statusColor = 'primary';
                    $icon = 'ri-notification-3-line';
                    
                    if (isset($data['status'])) {
                        switch ($data['status']) {
                            case 'error': case 'danger': $statusColor = 'danger'; $icon = 'ri-error-warning-line'; break;
                            case 'warning': $statusColor = 'warning'; $icon = 'ri-alert-line'; break;
                            case 'success': $statusColor = 'success'; $icon = 'ri-checkbox-circle-line'; break;
                            default: $statusColor = 'primary'; $icon = 'ri-information-line'; break;
                        }
                    }
                    $url = $data['action_url'] ?? '#';
                @endphp
                <div class="list-group-item list-group-item-action d-flex align-items-start p-4 {{ $isUnread ? 'bg-lighter' : '' }}" style="cursor: pointer;" onclick="handleNotifClick(event, '{{ $notif->id }}', '{{ $url }}')">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded-circle bg-label-{{ $statusColor }}">
                                <i class="icon-base ri {{ $icon }}"></i>
                            </span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 fw-bold {{ $isUnread ? 'text-dark' : 'text-muted' }}">
                                {{ $data['message'] ?? 'Notification' }}
                            </h6>
                            <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-muted small">
                            Modul: <span class="fw-medium">{{ $data['module'] ?? 'System' }}</span> | 
                            User: <span class="fw-medium">{{ $data['user_name'] ?? 'Sistem' }}</span>
                        </p>
                        @if(isset($data['details']) && is_array($data['details']))
                            <div class="mt-2 text-muted small bg-light p-2 rounded">
                                @foreach($data['details'] as $key => $val)
                                    <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if($isUnread)
                        <div class="flex-shrink-0 ms-3 d-flex align-items-center h-100">
                            <span class="badge bg-danger rounded-pill badge-dot"></span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="icon-base ri ri-notification-off-line ri-3x text-light mb-3"></i>
                    <h6 class="text-muted">Tidak ada notifikasi ditemukan.</h6>
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
    function handleNotifClick(e, id, url) {
        // Prevent click if clicking on a button inside
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
        
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(() => {
            if (url && url !== '#') {
                window.location.href = url;
            } else {
                window.location.reload();
            }
        });
    }

    function markAllAsReadPage() {
        fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if(data.success) window.location.reload();
        });
    }
</script>
@endsection
