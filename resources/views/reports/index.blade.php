@extends('layouts.app')
@section('title', 'รายงาน')
@section('page-title', 'รายงาน')

@section('content')

{{-- Report Type Tabs --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @php
    $tabs = [
        'repair_orders' => ['ใบซ่อม','fa-file-invoice'],
        'parts_usage'   => ['การใช้อะไหล่','fa-boxes-stacked'],
        'stock_balance' => ['Stock คงเหลือ','fa-warehouse'],
        'low_stock'     => ['อะไหล่ใกล้หมด','fa-triangle-exclamation'],
        'goods_receipts'=> ['รับอะไหล่เข้า','fa-truck-ramp-box'],
    ];
    @endphp
    <a href="{{ route('reports.receipt-summary') }}"
       style="padding:8px 18px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:600; display:flex; align-items:center; gap:7px;
              background:#fff; color:var(--primary); border:1px solid var(--primary); transition:all .2s;">
        <i class="fa-solid fa-truck-ramp-box"></i> สรุปการรับ-เบิกอะไหล่
    </a>
    <a href="{{ route('reports.profit') }}"
       style="padding:8px 18px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:600; display:flex; align-items:center; gap:7px;
              background:#fff; color:var(--primary); border:1px solid var(--primary); transition:all .2s;">
        <i class="fa-solid fa-sack-dollar"></i> สรุปกำไร-ต้นทุนรายเดือน
    </a>
    @foreach($tabs as $key => [$label,$icon])
    <a href="{{ route('reports.index', ['type'=>$key,'date_from'=>$dateFrom,'date_to'=>$dateTo]) }}"
       style="padding:8px 18px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:600; display:flex; align-items:center; gap:7px;
              background:{{ $type===$key ? 'var(--primary)' : '#fff' }};
              color:{{ $type===$key ? '#fff' : 'var(--text-muted)' }};
              border:1px solid {{ $type===$key ? 'var(--primary)' : 'var(--border)' }};
              transition:all .2s;">
        <i class="fa-solid {{ $icon }}"></i> {{ $label }}
    </a>
    @endforeach
</div>

{{-- Date Filter (ไม่แสดงเมื่อเป็น stock_balance / low_stock) --}}
@if(!in_array($type, ['stock_balance','low_stock']))
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex align-items-end gap-3 flex-wrap">
            <input type="hidden" name="type" value="{{ $type }}">
            <div>
                <label class="form-label">วันที่เริ่ม</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" style="width:160px;">
            </div>
            <div>
                <label class="form-label">วันที่สิ้นสุด</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" style="width:160px;">
            </div>
            <button type="submit" class="btn-navy">
                <i class="fa-solid fa-search"></i> ค้นหา
            </button>
            @if($data['total'] !== null)
            <div class="ms-auto" style="background:var(--bg); border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px 18px;">
                <div style="font-size:11px; color:var(--text-muted);">ยอดรวม</div>
                <div style="font-size:20px; font-weight:800; color:var(--accent);">฿{{ number_format($data['total'],2) }}</div>
            </div>
            @endif
        </form>
    </div>
</div>
@endif

{{-- Report Table --}}
<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-table me-1 text-primary"></i>
        @php echo array_values($tabs)[$type==='repair_orders'?0:($type==='parts_usage'?1:($type==='stock_balance'?2:($type==='low_stock'?3:4)))][0] ?? 'รายงาน' @endphp
        ({{ count($data['rows']) }} รายการ)
        <button onclick="window.print()" class="btn-navy ms-auto" style="font-size:12px; padding:5px 12px;">
            <i class="fa-solid fa-print"></i> พิมพ์
        </button>
    </div>
    <div class="card-body p-0">
        @if(empty($data['rows']))
        <div class="empty-state py-5">
            <i class="fa-solid fa-chart-line"></i>
            <p>ไม่มีข้อมูลในช่วงที่เลือก</p>
        </div>
        @else
        <div class="table-responsive">
            <table id="reportTable" class="table mb-0">
                @if($type === 'repair_orders')
                <thead><tr><th>เลขที่</th><th>ลูกค้า</th><th class="text-end">ค่าแรง</th><th class="text-end">ค่าอะไหล่</th><th class="text-end">ยอดรวม</th><th>วันที่</th></tr></thead>
                <tbody>
                @foreach($data['rows'] as $r)
                <tr><td><strong style="color:var(--primary);">{{ $r['ro_number'] }}</strong></td><td>{{ $r['customer_name'] }}</td>
                <td class="text-end">฿{{ number_format($r['labor_total']??0,2) }}</td>
                <td class="text-end">฿{{ number_format($r['parts_total']??0,2) }}</td>
                <td class="text-end"><strong>฿{{ number_format($r['grand_total']??0,2) }}</strong></td>
                <td>{{ substr($r['created_at'],0,10) }}</td></tr>
                @endforeach
                </tbody>

                @elseif($type === 'parts_usage')
                <thead><tr><th>อะไหล่</th><th>ใบซ่อม</th><th class="text-center">จำนวน</th><th class="text-end">ราคาทุน</th><th class="text-end">ต้นทุนรวม</th><th class="text-end">ราคาขาย</th><th class="text-end">รวมขาย</th><th class="text-end">กำไร</th></tr></thead>
                <tbody>
                @foreach($data['rows'] as $r)
                <tr>
                    <td>{{ $r['part_name'] }}</td>
                    <td style="color:var(--primary);">{{ $r['ro_id'] }}</td>
                    <td class="text-center">{{ $r['qty'] }}</td>
                    <td class="text-end text-muted">฿{{ number_format($r['cost_price']??0,2) }}</td>
                    <td class="text-end text-danger">฿{{ number_format($r['total_cost']??0,2) }}</td>
                    <td class="text-end">฿{{ number_format($r['unit_price']??0,2) }}</td>
                    <td class="text-end"><strong>฿{{ number_format($r['subtotal']??0,2) }}</strong></td>
                    <td class="text-end fw-bold {{ ($r['profit']??0) >= 0 ? 'text-success' : 'text-danger' }}">
                        ฿{{ number_format($r['profit']??0,2) }}
                    </td>
                </tr>
                @endforeach
                </tbody>

                @elseif($type === 'stock_balance')
                <thead><tr><th>รหัส</th><th>ชื่ออะไหล่</th><th>หน่วย</th><th class="text-center">Stock</th><th class="text-end">ราคาขาย</th><th>สถานะ</th></tr></thead>
                <tbody>
                @foreach($data['rows'] as $r)
                @php $s=(float)$r['stock_qty']; $m=(float)$r['min_stock']; @endphp
                <tr><td><code style="font-size:12px;">{{ $r['part_code'] }}</code></td><td>{{ $r['name'] }}</td>
                <td>{{ $r['unit'] }}</td>
                <td class="text-center"><span class="badge {{ $s<=0 ? 'badge-stock-empty' : ($s<=$m ? 'badge-stock-low' : 'badge-stock-ok') }}">{{ $s }}</span></td>
                <td class="text-end">฿{{ number_format($r['sell_price'],2) }}</td>
                <td><span class="badge-status {{ $s<=0 ? 'cancelled' : ($s<=$m ? 'pending' : 'done') }}">
                    {{ $s<=0 ? 'หมด' : ($s<=$m ? 'ใกล้หมด' : 'ปกติ') }}
                </span></td></tr>
                @endforeach
                </tbody>

                @elseif($type === 'low_stock')
                <thead><tr><th>รหัส</th><th>ชื่ออะไหล่</th><th>หน่วย</th><th class="text-center">Stock</th><th class="text-center">ขั้นต่ำ</th><th class="text-end">ราคาขาย</th></tr></thead>
                <tbody>
                @foreach($data['rows'] as $r)
                <tr><td><code style="font-size:12px; color:var(--danger);">{{ $r['part_code'] }}</code></td>
                <td><strong>{{ $r['name'] }}</strong></td><td>{{ $r['unit'] }}</td>
                <td class="text-center"><span class="badge {{ (float)$r['stock_qty']<=0 ? 'badge-stock-empty' : 'badge-stock-low' }}">{{ $r['stock_qty'] }}</span></td>
                <td class="text-center">{{ $r['min_stock'] }}</td>
                <td class="text-end">฿{{ number_format($r['sell_price'],2) }}</td></tr>
                @endforeach
                </tbody>

                @elseif($type === 'goods_receipts')
                <thead><tr><th>เลขที่</th><th>Supplier</th><th>วันที่รับ</th><th class="text-end">มูลค่า</th></tr></thead>
                <tbody>
                @foreach($data['rows'] as $r)
                <tr><td><strong style="color:var(--primary);">{{ $r['gr_number'] }}</strong></td>
                <td>{{ $r['supplier_name'] }}</td><td>{{ $r['receipt_date'] }}</td>
                <td class="text-end"><strong>฿{{ number_format($r['total_cost'],2) }}</strong></td></tr>
                @endforeach
                </tbody>
                @endif
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
@if(!empty($data['rows']))
$('#reportTable').DataTable({
    language: { url: '{{ asset("asset/datatable/th.json") }}' },
    pageLength: 50
});
@endif
</script>
@endpush
