@extends('template.index')

@section('title', 'Pusat Notifikasi | Pressent Studio & Cafe')

@section('content')
<div class="container-fluid px-0 notification-page">

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="notification-header-copy">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <span class="badge {{ $user->role === 'admin' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-emerald-subtle text-emerald border border-emerald-subtle' }} px-2 py-1 rounded-pill fw-semibold">
                    <i class="bi {{ $user->role === 'admin' ? 'bi-shield-lock-fill' : 'bi-calculator' }} me-1"></i>
                    Notifikasi {{ $user->role === 'admin' ? 'Administrator' : 'Kasir POS' }}
                </span>
                @if($unreadCount > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-1">
                        {{ $unreadCount }} belum dibaca
                    </span>
                @endif
            </div>
            <h3 class="fw-bold text-heading mb-0 fs-5">Notifikasi</h3>
        </div>

        <div class="notification-header-actions d-flex flex-wrap align-items-center gap-2">
            @if($unreadCount > 0)
                <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1 fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-check2-all text-success"></i>
                        <span>Tandai dibaca</span>
                    </button>
                </form>
            @endif

            @if($user->role === 'kasir')
                <a href="{{ route('kasir.kasir') }}" class="btn btn-sm btn-emerald text-white rounded-3 px-2 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-calculator"></i>
                    <span>Menu Kasir</span>
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-brand rounded-3 px-2 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-3">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('notifikasi.index') }}" method="GET" class="row g-2 align-items-center">
                
                <!-- Category Tabs / Filter Pills -->
                <div class="col-12 col-lg-8">
                    <div class="notification-filters d-flex flex-wrap gap-1">
                        <a href="{{ route('notifikasi.index', ['filter' => 'all', 'q' => $search]) }}"
                           class="btn btn-sm rounded-pill px-2 py-1 fw-semibold {{ $currentFilter === 'all' ? 'btn-dark' : 'btn-light border' }}">
                            Semua <span class="badge bg-secondary-subtle text-dark ms-1">{{ $totalCount }}</span>
                        </a>

                        <a href="{{ route('notifikasi.index', ['filter' => 'unread', 'q' => $search]) }}"
                           class="btn btn-sm rounded-pill px-2 py-1 fw-semibold {{ $currentFilter === 'unread' ? 'btn-danger' : 'btn-light border' }}">
                            <i class="bi bi-envelope-exclamation me-1"></i>Belum dibaca
                            @if($unreadCount > 0)
                                <span class="badge bg-white text-danger ms-1">{{ $unreadCount }}</span>
                            @endif
                        </a>

                        @foreach($categories as $category)
                            <a href="{{ route('notifikasi.index', ['filter' => $category, 'q' => $search]) }}"
                               class="btn btn-sm rounded-pill px-2 py-1 fw-semibold {{ $currentFilter === $category ? 'btn-primary' : 'btn-light border' }}">
                                {{ $category }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Search Input -->
                <div class="col-12 col-lg-4">
                    <div class="input-group input-group-sm notification-search">
                        <input type="hidden" name="filter" value="{{ $currentFilter }}">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" 
                               placeholder="Cari notifikasi..." value="{{ $search }}">
                        @if($search)
                            <a href="{{ route('notifikasi.index', ['filter' => $currentFilter]) }}" class="btn btn-outline-secondary" title="Reset cari">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-brand px-3">Cari</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Notification List Cards -->
    <div class="row g-2">
        @forelse($items as $item)
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden {{ !$item['is_read'] ? 'border-start border-4 border-primary bg-white' : 'bg-white' }} transition-hover">
                    <div class="card-body p-2 p-md-3">
                        <div class="d-flex align-items-start gap-2">
                            
                            <div class="notification-icon rounded-3 {{ $item['icon_bg'] }} d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </div>

                            <div class="flex-grow-1 min-w-0 notification-content">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-1 mb-1">
                                    <h5 class="fw-bold text-heading mb-0 notification-title">
                                        {{ $item['title'] }}
                                        @if(!$item['is_read'])
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill notification-badge ms-1">Baru</span>
                                        @endif
                                    </h5>
                                    <div class="d-flex flex-wrap align-items-center gap-1">
                                        <span class="badge bg-light text-dark border notification-badge">{{ $item['category'] }}</span>
                                        <span class="badge {{ $item['badge_class'] }} rounded-pill notification-badge">{{ $item['badge_text'] }}</span>
                                    </div>
                                </div>

                                <p class="text-secondary small mb-2 notification-message">
                                    {{ $item['message'] }}
                                </p>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                                    <a href="{{ $item['url'] }}" class="btn btn-sm btn-primary rounded-3 px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <span>Buka</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5 px-3">
                    <div class="mb-3">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4">
                            <i class="bi bi-bell-slash fs-1 text-muted opacity-50"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark">Tidak Ada Notifikasi</h5>
                    <p class="text-muted small mb-3">
                        @if($currentFilter !== 'all' || $search)
                            Tidak ada notifikasi yang sesuai dengan filter atau kata kunci pencarian Anda.
                        @else
                            Saat ini belum ada notifikasi untuk akun Anda. Semua aktivitas sistem berjalan lancar!
                        @endif
                    </p>
                    @if($currentFilter !== 'all' || $search)
                        <div>
                            <a href="{{ route('notifikasi.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">Reset Filter</a>
                        </div>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
            <div class="small text-muted font-monospace">
                Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} Notifikasi
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
    @endif

</div>
@endsection

@section('styles')
<style>
    .notification-page {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-x: hidden;
    }

    .notification-header-copy,
    .notification-content,
    .notification-header-actions,
    .notification-header-actions form,
    .notification-search {
        min-width: 0;
        max-width: 100%;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
    }

    .notification-title,
    .notification-message {
        overflow-wrap: anywhere;
    }

    .notification-title {
        font-size: 0.9rem;
    }

    .notification-message {
        line-height: 1.4;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .notification-badge {
        white-space: normal;
        overflow-wrap: anywhere;
        font-size: 0.68rem;
    }

    @media (max-width: 575.98px) {
        .notification-header-actions {
            width: 100%;
        }

        .notification-search .form-control {
            width: 1%;
            min-width: 0;
        }
    }
</style>
@endsection
