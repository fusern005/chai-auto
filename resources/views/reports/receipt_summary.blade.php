@extends('layouts.app')
@section('title', 'สรุปการรับอะไหล่เข้าคลัง')
@section('page-title', 'สรุปการรับอะไหล่เข้าคลัง')

@section('content')

{{-- Filter --}}
<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.receipt-summary') }}" class="d-flex align-items-end gap-3 flex-wrap">
            <div>
                <label class="form-label fw-bold">เลือกเดือน/ปี</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" style="width:180px;">
            </div>
            <button type="submit" class="btn-navy">
                <i class="fa-solid fa-search me-1"></i> ค้นหา
            </button>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary ms-auto">
                <i class="fa-solid fa-print me-1"></i> พิมพ์
            </button>
        </form>
    </div>
</div>

{{-- Print Header --}}
<div class="print-only mb-4 text-center">
    <h2 style="font-weight:800; font-size:24px;">อู่ช่างชัยไดนาโม</h2>
    <h4>รายงานสรุปการรับอะไหล่เข้าคลัง ประจำเดือน {{ date('m/Y', strtotime($month . '-01')) }}</h4>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10B981);">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">ยอดรับเข้ารวม (บาท)</div>
                <div style="font-size:20px; font-weight:800; color:#059669;">฿{{ number_format($stats['total_in_amount'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-arrow-down-to-bracket"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">รับเข้ารวม (ชิ้น)</div>
                <div style="font-size:20px; font-weight:800; color:var(--primary);">{{ number_format($stats['total_in_qty']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #DC2626, #EF4444);">
                <i class="fa-solid fa-dolly"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">เบิกออกรวม (ราคาทุน)</div>
                <div style="font-size:20px; font-weight:800; color:#DC2626;">฿{{ number_format($stats['total_out_amount'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #7C3AED, #8B5CF6);">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">จำนวนรายการอะไหล่</div>
                <div style="font-size:20px; font-weight:800; color:#7C3AED;">{{ $stats['total_parts'] }} รายการ</div>
            </div>
        </div>
    </div>
</div>

{{-- Main Table --}}
<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-table me-1 text-primary"></i>
        รายการรับอะไหล่เข้าคลัง ประจำเดือน {{ date('m/Y', strtotime($month . '-01')) }}
        ({{ count($rows) }} รายการ)
    </div>
    <div class="card-body p-0">
        @if(empty($rows))
        <div class="empty-state py-5">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <p>ไม่มีรายการรับอะไหล่เข้าในเดือนที่เลือก</p>
        </div>
        @else
        <div class="table-responsive">
            <table id="receiptTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>รหัสอะไหล่</th>
                        <th>ชื่ออะไหล่</th>
                        <th>Supplier</th>
                        <th class="text-center">รับเข้า<br><small class="text-muted">(ชิ้น)</small></th>
                        <th class="text-end">ต้นทุนรวม (รับเข้า)</th>
                        <th class="text-center text-danger">แบกออก<br><small class="text-muted">(ชิ้น)</small></th>
                        <th class="text-end text-danger">ต้นทุนรวม (แบก)</th>
                        <th class="text-center">คงเหลือ<br><small class="text-muted">(รับ - แบก)</small></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $idx => $row)
                    <tr>
                        <td><code style="font-size:12px; color:var(--primary);">{{ $row['part_code'] }}</code></td>
                        <td><strong>{{ $row['part_name'] }}</strong></td>
                        <td style="font-size:13px; color:var(--text-muted);">
                            @if(!empty($row['in_receipts']))
                            {{ collect($row['in_receipts'])->pluck('supplier')->unique()->implode(', ') }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-success">{{ number_format($row['in_qty']) }}</span>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $row['unit'] }}</div>
                        </td>
                        <td class="text-end text-success fw-bold">
                            ฿{{ number_format($row['in_amount'], 2) }}
                        </td>
                        <td class="text-center">
                            @if($row['out_qty'] > 0)
                            <span class="fw-bold text-danger">{{ number_format($row['out_qty']) }}</span>
                            <div style="font-size:11px; color:var(--text-muted);">{{ $row['unit'] }}</div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end text-danger fw-bold">
                            @if($row['out_qty'] > 0)
                            ฿{{ number_format($row['out_amount'], 2) }}
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $stock = $row['balance_month'];
                                $cls   = $stock <= 0 ? 'badge-stock-empty' : 'badge-stock-ok';
                            @endphp
                            <span class="badge {{ $cls }}" style="font-size:13px; padding:4px 10px;"
                                  title="รับเข้า ({{ number_format($row['in_qty']) }}) - แบกออก ({{ number_format($row['out_qty']) }}) = คงเหลือ {{ number_format($stock) }}">
                                {{ number_format($stock) }} {{ $row['unit'] }}
                            </span>
                            @if(!empty($row['out_details']))
                            <button type="button" class="btn btn-sm ms-1 no-print"
                                    style="background:none; border:none; color:var(--primary); padding:0 4px;"
                                    onclick="showOutDetails({{ $idx }})"
                                    title="ดูรายการที่แบกออก">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @endif
                        </td>
                    </tr>


                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Out Details Modal --}}
<div class="modal fade" id="outDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none; padding-bottom:0;">
                <div>
                    <h6 class="modal-title fw-bold mb-0">
                        <i class="fa-solid fa-dolly me-2 text-danger"></i>
                        รายการเบิกออก: <span id="outPartName"></span>
                    </h6>
                    <div id="outPartCode" style="font-size:12px; color:var(--text-muted);"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="padding-top:8px !important;">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>วันที่-เวลา</th>
                                <th>เลขที่ใบซ่อม</th>
                                <th class="text-center">จำนวนเบิก</th>
                                <th class="text-end">ราคาทุน/ชิ้น</th>
                                <th class="text-end">ต้นทุนรวม</th>
                            </tr>
                        </thead>
                        <tbody id="outDetailsBody"></tbody>
                        <tfoot>
                            <tr class="fw-bold" style="background:var(--bg);">
                                <td colspan="2">รวมทั้งหมด</td>
                                <td class="text-center" id="outTotalQty"></td>
                                <td></td>
                                <td class="text-end text-danger" id="outTotalAmount"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Embed rows data for JS
const rowsData = @json($rows);

const outModal = new bootstrap.Modal(document.getElementById('outDetailsModal'));

function showOutDetails(idx) {
    const row = rowsData[idx];
    document.getElementById('outPartName').textContent = row.part_name;
    document.getElementById('outPartCode').textContent = row.part_code;

    let html = '';
    let totalQty = 0;
    let totalAmt = 0;

    row.out_details.forEach(d => {
        totalQty += parseFloat(d.qty) || 0;
        totalAmt += parseFloat(d.subtotal) || 0;
        const url = d.ro_id ? `{{ url('/repair-orders') }}/${d.ro_id}` : null;
        const roLink = url
            ? `<a href="${url}" target="_blank" style="color:var(--primary); font-weight:600;">${d.ro_number}</a>`
            : `<span style="color:var(--text-muted);">${d.ro_number}</span>`;

        html += `<tr>
            <td style="font-size:13px;">${d.date}</td>
            <td>${roLink}</td>
            <td class="text-center fw-bold text-danger">${d.qty} ${row.unit}</td>
            <td class="text-end text-muted">฿${Number(d.unit_cost).toLocaleString('th-TH', {minimumFractionDigits:2})}</td>
            <td class="text-end fw-bold">฿${Number(d.subtotal).toLocaleString('th-TH', {minimumFractionDigits:2})}</td>
        </tr>`;
    });

    document.getElementById('outDetailsBody').innerHTML = html;
    document.getElementById('outTotalQty').textContent = totalQty + ' ' + row.unit;
    document.getElementById('outTotalAmount').textContent = '฿' + totalAmt.toLocaleString('th-TH', {minimumFractionDigits:2});

    outModal.show();
}

$(document).ready(function() {
    $('#receiptTable').DataTable({
        language: { url: '{{ asset("asset/datatable/th.json") }}' },
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>
@endpush
