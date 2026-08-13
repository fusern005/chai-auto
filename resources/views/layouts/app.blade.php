<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Suchai Auto — ระบบอู่ซ่อมรถ')</title>

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="{{ asset('asset/bootstrap-5.3.8-dist/css/bootstrap.min.css') }}">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('asset/fontawesome-free-7.3.0-web/css/all.min.css') }}">
    {{-- DataTables --}}
    <link rel="stylesheet" href="{{ asset('asset/datatable/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/datatable/responsive.bootstrap5.min.css') }}">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="{{ asset('asset/sweetalert2/dist/sweetalert2.min.css') }}">
    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('asset/css/app.css') }}">

    @stack('styles')
    <style>
        /* Global Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(3px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease-in-out;
        }
        .spinner-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        .spinner-content {
            text-align: center;
            background: #ffffff;
            padding: 24px 36px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .spinner-text {
            font-size: 14px;
            font-weight: 600;
            color: #1E293B;
        }
        @media print {
            @page { margin: 0; }
            body { padding: 10mm; }
            .sidebar, .topbar, .no-print, .d-print-none, .spinner-overlay { display: none !important; }
            .main-content { margin-left: 0 !important; padding-top: 0 !important; min-height: auto !important; }
            .print-only { display: block !important; }
        }
        @media screen {
            .print-only { display: none !important; }
        }
    </style>
</head>
<body>

{{-- GLOBAL LOADING SPINNER --}}
<div id="global-spinner" class="spinner-overlay">
    <div class="spinner-content">
        <div class="spinner-border text-primary" role="status" style="width: 2.8rem; height: 2.8rem; border-width: 0.25em;">
            <span class="visually-hidden">กำลังโหลด...</span>
        </div>
        <div class="spinner-text"><i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังโหลดข้อมูล...</div>
    </div>
</div>

{{-- SIDEBAR --}}

<aside class="sidebar" id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="fa-solid fa-car-wrench"></i></div>
        <div>
            <div class="sidebar-brand-text">Suchai Auto</div>
            <div class="sidebar-brand-sub">ระบบอู่ซ่อมรถ</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section">หลัก</div>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>

        <div class="sidebar-section">งานซ่อม</div>

        <a href="{{ route('repair-orders.index') }}"
           class="sidebar-link {{ request()->routeIs('repair-orders.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-invoice"></i> ใบซ่อม / ใบเสร็จ
        </a>

        <div class="sidebar-section">คลังสินค้า</div>

        <a href="{{ route('parts.index') }}"
           class="sidebar-link {{ request()->routeIs('parts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-boxes-stacked"></i> คลังอะไหล่
        </a>

        <a href="{{ route('goods-receipts.index') }}"
           class="sidebar-link {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-truck-ramp-box"></i> รับอะไหล่เข้า
        </a>

        <a href="{{ route('stock-movements.index') }}"
           class="sidebar-link {{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
            <i class="fa-solid fa-arrow-right-arrow-left"></i> ประวัติ Stock
        </a>

        <div class="sidebar-section">ข้อมูล</div>

        <a href="{{ route('master-data.customers') }}"
           class="sidebar-link {{ request()->routeIs('master-data.customers*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i> ลูกค้า
        </a>

        <a href="{{ route('master-data.vehicles') }}"
           class="sidebar-link {{ request()->routeIs('master-data.vehicles*') ? 'active' : '' }}">
            <i class="fa-solid fa-car"></i> ยานพาหนะ
        </a>

        <a href="{{ route('master-data.suppliers') }}"
           class="sidebar-link {{ request()->routeIs('master-data.suppliers*') ? 'active' : '' }}">
            <i class="fa-solid fa-building"></i> Supplier
        </a>

        <div class="sidebar-section">รายงาน</div>

        <a href="{{ route('reports.index') }}"
           class="sidebar-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-line"></i> รายงานทั่วไป
        </a>

        <a href="{{ route('reports.profit') }}"
           class="sidebar-link {{ request()->routeIs('reports.profit*') ? 'active' : '' }}">
            <i class="fa-solid fa-sack-dollar"></i> กำไร-ต้นทุนรายเดือน
        </a>

        <a href="{{ route('reports.receipt-summary') }}"
           class="sidebar-link {{ request()->routeIs('reports.receipt-summary*') ? 'active' : '' }}">
            <i class="fa-solid fa-truck-ramp-box"></i> สรุปการรับ-เบิกอะไหล่
        </a>
    </nav>

    <div class="sidebar-footer">
        <div style="color:rgba(255,255,255,.5); font-size:12px; margin-bottom:10px;">
            <i class="fa-solid fa-user me-1"></i> {{ session('user_name', 'ผู้ใช้งาน') }}
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="background:rgba(239,68,68,.15); color:#FCA5A5; border:none;
                    padding:8px 14px; border-radius:6px; cursor:pointer; width:100%;
                    font-family:Sarabun,sans-serif; font-size:13px; text-align:left;">
                <i class="fa-solid fa-right-from-bracket me-2"></i>ออกจากระบบ
            </button>
        </form>
    </div>
</aside>

{{-- TOPBAR --}}
<header class="topbar">
    <button onclick="document.getElementById('sidebar').classList.toggle('open')"
            style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px;padding:4px 8px;"
            class="d-md-none">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="topbar-title">@yield('page-title', 'Dashboard')</div>

    <div class="ms-auto d-flex align-items-center gap-3">
        <form action="{{ route('sync-sheets') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                    title="ดึงข้อมูลล่าสุดจาก Google Sheets มาอัปเดต Local DB" style="border-radius:20px; font-size:12px; padding:4px 12px;">
                <i class="fa-solid fa-rotate text-primary"></i> <span class="d-none d-sm-inline">ซิงค์สด Google Sheets</span>
            </button>
        </form>
        <div class="topbar-user">
            <div class="topbar-avatar">{{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}</div>
            <span class="d-none d-md-inline">{{ session('user_name', 'ผู้ใช้งาน') }}</span>
        </div>
    </div>
</header>


{{-- MAIN --}}
<main class="main-content">
    <div class="page-body">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
             style="border-radius:var(--radius); border:none; background:#D1FAE5; color:#065F46;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"
             style="border-radius:var(--radius); border:none; background:#FEE2E2; color:#991B1B;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</main>

{{-- Scripts --}}
<script src="{{ asset('asset/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('asset/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('asset/datatable/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('asset/datatable/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('asset/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('asset/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('asset/chart.js') }}"></script>

<script>
    function showSpinner() {
        const spinner = document.getElementById('global-spinner');
        if (spinner) spinner.classList.add('show');
    }
    function hideSpinner() {
        const spinner = document.getElementById('global-spinner');
        if (spinner) spinner.classList.remove('show');
    }

    // CSRF token for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Global AJAX Spinner Triggers
    $(document).ajaxStart(function() { showSpinner(); });
    $(document).ajaxStop(function() { hideSpinner(); });
    $(document).ajaxError(function() { hideSpinner(); });

    // Form Submit Spinner
    $(document).on('submit', 'form', function() {
        showSpinner();
    });

    // Link Click Spinner (exclude modal toggles, anchors, blank targets)
    $(document).on('click', 'a:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"]):not([data-bs-toggle]):not(.no-spinner)', function() {
        const href = $(this).attr('href');
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            showSpinner();
        }
    });

    // Page Unload / Show events
    window.addEventListener('beforeunload', function() {
        showSpinner();
    });

    window.addEventListener('pageshow', function(event) {
        hideSpinner();
    });
</script>

@stack('scripts')
</body>
</html>

