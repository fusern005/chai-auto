@extends('layouts.app')
@section('title', 'รายงานกำไร-ต้นทุนรายเดือน')
@section('page-title', 'รายงานกำไร-ต้นทุนรายเดือน')

@section('content')

{{-- Filter Box --}}
<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.profit') }}" class="d-flex align-items-end gap-3 flex-wrap">
            <div>
                <label class="form-label fw-bold">เลือกเดือน/ปี</label>
                <input type="month" name="month" class="form-control" value="{{ $month }}" style="width:180px;">
            </div>
            <button type="submit" class="btn-navy">
                <i class="fa-solid fa-calculator me-1"></i> คำนวณกำไร
            </button>
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary ms-auto">
                <i class="fa-solid fa-print me-1"></i> พิมพ์รายงาน
            </button>
        </form>
    </div>
</div>

{{-- Header Display for Print --}}
<div class="print-only mb-4 text-center">
    <h2 style="font-weight:800; font-size:24px;">อู่ช่างชัยไดนาโม</h2>
    <h4>รายงานสรุปกำไร - ต้นทุน ประจำเดือน {{ date('m/Y', strtotime($month . '-01')) }}</h4>
</div>

{{-- Overview Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">รายได้รวม</div>
                <div style="font-size:22px; font-weight:800; color:var(--primary);">
                    ฿{{ number_format($summary['total_revenue'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #EA580C, #F97316);">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">ต้นทุนอะไหล่ที่ใช้</div>
                <div style="font-size:22px; font-weight:800; color:#EA580C;">
                    ฿{{ number_format($summary['total_part_cost'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #059669, #10B981);">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">กำไรขั้นต้นสุทธิ</div>
                <div style="font-size:22px; font-weight:800; color:#059669;">
                    ฿{{ number_format($summary['net_gross_profit'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #7C3AED, #8B5CF6);">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div style="font-size:12px; color:var(--text-muted);">อัตรากำไร (Margin)</div>
                <div style="font-size:22px; font-weight:800; color:#7C3AED;">
                    {{ number_format($summary['overall_margin'], 1) }}%
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Summary Breakdown --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="fa-solid fa-list-check text-primary me-1"></i>
        สรุปรายได้และต้นทุน ประจำเดือน {{ date('m/Y', strtotime($month . '-01')) }}
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="p-3" style="background:var(--bg); border-radius:var(--radius); border:1px solid var(--border);">
                    <h6 class="fw-bold mb-3" style="color:var(--primary);"><i class="fa-solid fa-arrow-down-left-and-arrow-up-right-to-center me-1"></i> ด้านรายรับ</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>รายได้จากค่าแรง:</span>
                        <strong>฿{{ number_format($summary['total_labor'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>รายได้จากขายอะไหล่:</span>
                        <strong>฿{{ number_format($summary['total_parts_revenue'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>ส่วนลดรวม:</span>
                        <strong>-฿{{ number_format($summary['total_discount'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top fw-bold" style="font-size:16px;">
                        <span>รายได้รวมสุทธิ:</span>
                        <span class="text-primary">฿{{ number_format($summary['total_revenue'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3" style="background:var(--bg); border-radius:var(--radius); border:1px solid var(--border);">
                    <h6 class="fw-bold mb-3" style="color:#EA580C;"><i class="fa-solid fa-arrow-up-from-ground-water me-1"></i> ด้านต้นทุน & การสั่งซื้อ</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>ต้นทุนอะไหล่ (ที่ตัดไปซ่อมจริง):</span>
                        <strong class="text-danger">฿{{ number_format($summary['total_part_cost'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>ยอดสั่งซื้ออะไหล่เติมคลังเดือนนี้ (PO/GR):</span>
                        <strong>฿{{ number_format($summary['total_purchases'], 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted" style="font-size:13px;">
                        <span>ภาษีมูลค่าเพิ่ม (VAT 7% รวม):</span>
                        <span>฿{{ number_format($summary['total_vat'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top fw-bold" style="font-size:16px;">
                        <span>กำไรขั้นต้น (รายได้ - ต้นทุนอะไหล่):</span>
                        <span class="text-success">฿{{ number_format($summary['net_gross_profit'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Breakdown Table --}}
<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-table me-1 text-primary"></i>
        ตารางวิเคราะห์กำไรรายใบซ่อม ({{ count($orderBreakdown) }} รายการ)
    </div>
    <div class="card-body p-0">
        @if(empty($orderBreakdown))
        <div class="empty-state py-5">
            <i class="fa-solid fa-calculator"></i>
            <p>ไม่มีรายการใบซ่อมในเดือนที่เลือก</p>
        </div>
        @else
        <div class="table-responsive">
            <table id="profitTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่ใบซ่อม</th>
                        <th>วันที่</th>
                        <th>ลูกค้า</th>
                        <th class="text-end">รายได้ค่าแรง</th>
                        <th class="text-end">รายได้อะไหล่</th>
                        <th class="text-end">ต้นทุนอะไหล่</th>
                        <th class="text-end">ยอดรวมรับ</th>
                        <th class="text-end">กำไรสุทธิ</th>
                        <th class="text-center">% กำไร</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderBreakdown as $row)
                    <tr>
                        <td><strong style="color:var(--primary);">{{ $row['ro_number'] }}</strong></td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td class="text-end">฿{{ number_format($row['labor'], 2) }}</td>
                        <td class="text-end">฿{{ number_format($row['parts_revenue'], 2) }}</td>
                        <td class="text-end text-danger">฿{{ number_format($row['parts_cost'], 2) }}</td>
                        <td class="text-end">฿{{ number_format($row['grand_total'], 2) }}</td>
                        <td class="text-end fw-bold {{ $row['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            ฿{{ number_format($row['profit'], 2) }}
                        </td>
                        <td class="text-center fw-bold">
                            <span class="badge {{ $row['margin'] >= 30 ? 'bg-success' : ($row['margin'] >= 15 ? 'bg-primary' : 'bg-warning') }}">
                                {{ number_format($row['margin'], 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document.body).ready(function() {
    $('#profitTable').DataTable({
        language: { url: '{{ asset("asset/datatable/th.json") }}' },
        order: [[1, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush
