@extends('layouts.app')
@section('title', 'ประวัติ Stock')
@section('page-title', 'ประวัติ Stock Movement')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">ประวัติ Stock Movement</div>
        <div style="color:var(--text-muted); font-size:13px;">ตรวจสอบการเคลื่อนไหวของสต๊อกทั้งหมด</div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('stock-movements.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">อะไหล่</label>
                    <select name="part_id" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @foreach($parts as $p)
                        <option value="{{ $p['id'] }}" {{ request('part_id')==$p['id'] ? 'selected' : '' }}>
                            {{ $p['part_code'] }} — {{ $p['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">ประเภท</label>
                    <select name="type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="in"     {{ request('type')=='in' ? 'selected' : '' }}>รับเข้า</option>
                        <option value="out"    {{ request('type')=='out' ? 'selected' : '' }}>ตัดออก</option>
                        <option value="return" {{ request('type')=='return' ? 'selected' : '' }}>คืน Stock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">วันที่เริ่ม</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">วันที่สิ้นสุด</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-navy me-2">
                        <i class="fa-solid fa-filter"></i> กรอง
                    </button>
                    <a href="{{ route('stock-movements.index') }}" class="btn" style="border:1px solid var(--border); font-family:Sarabun,sans-serif;">
                        <i class="fa-solid fa-rotate-left"></i> รีเซ็ต
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="movTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>รหัสอะไหล่</th>
                        <th>ชื่ออะไหล่</th>
                        <th>ประเภท</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">คงเหลือ</th>
                        <th>อ้างอิง</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($movements as $m)
                @php
                $typeMap = ['in'=>['รับเข้า','badge-stock-ok','fa-arrow-down'],
                            'out'=>['ตัดออก','badge-stock-empty','fa-arrow-up'],
                            'return'=>['คืน Stock','badge-stock-low','fa-rotate-left']];
                [$tLabel, $tCls, $tIcon] = $typeMap[$m['movement_type']] ?? [$m['movement_type'],'','fa-circle'];
                @endphp
                <tr>
                    <td style="font-size:12px; white-space:nowrap;">{{ substr($m['created_at'],0,16) }}</td>
                    <td><code style="font-size:12px; color:var(--primary);">{{ $m['part_code'] }}</code></td>
                    <td>{{ $m['part_name'] }}</td>
                    <td>
                        <span class="badge {{ $tCls }}" style="gap:4px;">
                            <i class="fa-solid {{ $tIcon }}" style="font-size:10px;"></i>
                            {{ $tLabel }}
                        </span>
                    </td>
                    <td class="text-center">
                        <strong style="color:{{ $m['movement_type']==='in' ? 'var(--success)' : ($m['movement_type']==='out' ? 'var(--danger)' : 'var(--warning)') }};">
                            {{ $m['movement_type']==='out' ? '-' : '+' }}{{ $m['qty'] }}
                        </strong>
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background:var(--bg); color:var(--text);">{{ $m['balance'] }}</span>
                    </td>
                    <td style="font-size:12px; color:var(--primary);">{{ $m['ref_number'] ?? $m['ref_id'] ?? '-' }}</td>
                    <td style="font-size:12px; color:var(--text-muted);">{{ $m['note'] ?? '-' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if(empty($movements))
        <div class="empty-state py-5">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
            <p>ไม่มีรายการ Stock Movement</p>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#movTable').DataTable({
    language: { url: '{{ asset("asset/datatable/th.json") }}' },
    order: [[0,'desc']], pageLength: 25
});
</script>
@endpush
