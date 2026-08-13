@extends('layouts.app')
@section('title', 'ใบซ่อมเสร็จ อู่ช่างชัยไดนาโมๅ')
@section('page-title', 'ใบซ่อม '.$order['ro_number'])

@section('content')

@php
$statusMap = ['pending'=>['รอซ่อม','pending'],'in_progress'=>['กำลังซ่อม','in_progress'],
              'done'=>['เสร็จแล้ว','done'],'cancelled'=>['ยกเลิก','cancelled']];
[$sLabel, $sCls] = $statusMap[$order['status']] ?? [$order['status'],''];
$laborItems = array_filter($items, fn($i) => $i['type'] === 'labor');
$partItems  = array_filter($items, fn($i) => $i['type'] === 'part');
@endphp

<div class="page-header d-print-none">
    <div>
        <a href="{{ route('repair-orders.index') }}" style="color:var(--text-muted); font-size:13px; text-decoration:none;">
            <i class="fa-solid fa-arrow-left me-1"></i>กลับรายการ
        </a>
        <div class="page-title mt-1">{{ $order['ro_number'] }}</div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn-navy no-print">
            <i class="fa-solid fa-print"></i> พิมพ์ใบเสร็จ
        </button>
        @if($order['status'] !== 'cancelled')
        <button onclick="changeStatus()" class="btn-primary-custom no-print">
            <i class="fa-solid fa-arrows-rotate"></i> เปลี่ยนสถานะ
        </button>
        @endif
    </div>
</div>

<div class="print-receipt no-print">
    {{-- RECEIPT HEADER --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h2 style="font-size:22px; font-weight:800; color:var(--primary); margin-bottom:16px;">ใบเสร็จรับเงิน</h2>
                    <div>
                        <div style="margin-bottom:6px;">
                            <span style="color:var(--text-muted); font-size:12px;">สถานะ:</span>
                            <span class="badge-status {{ $sCls }} ms-2">{{ $sLabel }}</span>
                        </div>
                        <div style="color:var(--text-muted); font-size:13px;">
                            วันที่สร้าง: {{ substr($order['created_at'],0,10) }}
                        </div>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div style="font-size:20px; font-weight:800; color:var(--primary); letter-spacing:1px; margin-bottom:8px;">
                        {{ $order['ro_number'] }}
                    </div>

                    <div style="font-size:14px; line-height:2;">
                        <strong>ลูกค้า:</strong> {{ $customer['name'] ?? '-' }}<br>
                        @if($customer['phone'] ?? false)
                        <strong>โทร:</strong> {{ $customer['phone'] }}<br>
                        @endif
                        @if($vehicle ?? false)
                        <strong>รถ:</strong> {{ $vehicle['brand'] }} {{ $vehicle['model'] }}
                        ({{ $vehicle['license_plate'] }})<br>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- รายการค่าแรง --}}
    @if(count($laborItems))
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-screwdriver-wrench text-warning me-1"></i> รายการค่าแรง
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>รายการ</th><th class="text-center">จำนวน</th><th class="text-end">ราคา/หน่วย</th><th class="text-end">รวม</th></tr></thead>
                <tbody>
                @foreach($laborItems as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-end">฿{{ number_format($item['unit_price'],2) }}</td>
                    <td class="text-end"><strong>฿{{ number_format($item['subtotal'],2) }}</strong></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- รายการอะไหล่ --}}
    @if(count($partItems))
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-boxes-stacked text-success me-1"></i> รายการอะไหล่
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>รายการ</th><th class="text-center">จำนวน</th><th class="text-end">ราคา/หน่วย</th><th class="text-end">รวม</th></tr></thead>
                <tbody>
                @foreach($partItems as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-end">฿{{ number_format($item['unit_price'],2) }}</td>
                    <td class="text-end"><strong>฿{{ number_format($item['subtotal'],2) }}</strong></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TOTALS --}}
    <div class="row justify-content-end">
        <div class="col-md-5">
            <div class="totals-box">
                <div class="totals-row">
                    <span style="color:var(--text-muted);">ค่าแรงรวม</span>
                    <strong>฿{{ number_format($order['labor_total'] ?? 0, 2) }}</strong>
                </div>
                <div class="totals-row">
                    <span style="color:var(--text-muted);">ค่าอะไหล่รวม</span>
                    <strong>฿{{ number_format($order['parts_total'] ?? 0, 2) }}</strong>
                </div>
                @if(($order['discount'] ?? 0) > 0)
                <div class="totals-row">
                    <span style="color:var(--text-muted);">ส่วนลด</span>
                    <strong style="color:var(--danger);">-฿{{ number_format($order['discount'], 2) }}</strong>
                </div>
                @endif
                @if(($order['vat'] ?? 0) > 0)
                <div class="totals-row">
                    <span style="color:var(--text-muted);">VAT 7%</span>
                    <strong>฿{{ number_format($order['vat'], 2) }}</strong>
                </div>
                @endif
                <div class="totals-row grand">
                    <span>ยอดสุทธิ</span>
                    <span>฿{{ number_format($order['grand_total'] ?? 0, 2) }}</span>
                </div>
                <div class="totals-row no-print" style="margin-top:12px; padding-top:10px; border-top:1px dashed var(--border);">
                    <span style="color:var(--text-muted); font-size:13px;"><i class="fa-solid fa-boxes-packing me-1 text-warning"></i> ต้นทุนอะไหล่รวม</span>
                    <strong style="color:var(--danger); font-size:13px;">฿{{ number_format($order['parts_cost'] ?? 0, 2) }}</strong>
                </div>
                <div class="totals-row no-print">
                    <span style="color:var(--text-muted); font-size:13px;"><i class="fa-solid fa-sack-dollar me-1 text-success"></i> กำไรขั้นต้นใบซ่อมนี้</span>
                    <strong style="color:var(--success); font-size:14px;">฿{{ number_format($order['estimated_profit'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- FORMAL PRINT LAYOUT (Only visible when printing) --}}
<div class="print-only" style="background:#fff; color:#000; font-family:'Sarabun', sans-serif;">
    <div class="text-center mb-4">
        <h2 style="font-size:26px; font-weight:bold; margin-bottom:5px;">อู่ช่างชัยไดนาโม</h2>
        <div style="font-size:14px; color:#333;">(แก้ไขที่อยู่และเบอร์โทรได้ในระบบ)</div>
    </div>
    
    <div class="row mb-4">
        <div class="col-6">
            <h3 style="font-size:20px; font-weight:bold; text-decoration:underline;">ใบเสร็จรับเงิน</h3>
            <div style="margin-top:12px; font-size:15px; line-height:1.6;">
                <strong>ลูกค้า:</strong> {{ $customer['name'] ?? '-' }}<br>
                @if($customer['phone'] ?? false)
                <strong>โทร:</strong> {{ $customer['phone'] }}<br>
                @endif
                @if($vehicle ?? false)
                <strong>รถ:</strong> {{ $vehicle['brand'] }} {{ $vehicle['model'] }} ({{ $vehicle['license_plate'] }})
                @endif
            </div>
        </div>
        <div class="col-6 text-end">
            <div style="font-size:15px; line-height:1.6;">
                <strong>เลขที่ใบเสร็จ:</strong> {{ $order['ro_number'] }}<br>
                <strong>วันที่:</strong> {{ date('d/m/Y', strtotime($order['created_at'])) }}<br>
                <strong>สถานะ:</strong> {{ $sLabel }}
            </div>
        </div>
    </div>

    <table class="table table-bordered border-dark" style="font-size:15px;">
        <thead class="table-light">
            <tr class="text-center">
                <th style="width:60px;">ลำดับ</th>
                <th>รายการ</th>
                <th style="width:100px;">จำนวน</th>
                <th style="width:140px;">ราคา/หน่วย</th>
                <th style="width:140px;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @php $n = 1; @endphp
            @foreach($laborItems as $item)
            <tr>
                <td class="text-center">{{ $n++ }}</td>
                <td>{{ $item['description'] }} (ค่าแรง)</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-end">{{ number_format($item['unit_price'],2) }}</td>
                <td class="text-end">{{ number_format($item['subtotal'],2) }}</td>
            </tr>
            @endforeach
            @foreach($partItems as $item)
            <tr>
                <td class="text-center">{{ $n++ }}</td>
                <td>{{ $item['description'] }} (อะไหล่)</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-end">{{ number_format($item['unit_price'],2) }}</td>
                <td class="text-end">{{ number_format($item['subtotal'],2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row justify-content-end" style="margin-top:20px; font-size:15px;">
        <div class="col-5">
            <div class="d-flex justify-content-between mb-2">
                <span>รวมเป็นเงิน:</span>
                <span>฿{{ number_format(($order['labor_total'] ?? 0) + ($order['parts_total'] ?? 0), 2) }}</span>
            </div>
            @if(($order['discount'] ?? 0) > 0)
            <div class="d-flex justify-content-between mb-2">
                <span>ส่วนลด:</span>
                <span>-฿{{ number_format($order['discount'], 2) }}</span>
            </div>
            @endif
            @if(($order['vat'] ?? 0) > 0)
            <div class="d-flex justify-content-between mb-2">
                <span>ภาษีมูลค่าเพิ่ม (VAT 7%):</span>
                <span>฿{{ number_format($order['vat'], 2) }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between mt-2 pt-2" style="border-top:2px solid #000; font-weight:bold; font-size:18px;">
                <span>ยอดสุทธิ:</span>
                <span>฿{{ number_format($order['grand_total'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="row text-center" style="margin-top:100px; font-size:15px;">
        <div class="col-6">
            <div>_________________________________</div>
            <div class="mt-2">ลายมือชื่อลูกค้า</div>
            <div class="mt-1">วันที่ _______/_______/_______</div>
        </div>
        <div class="col-6">
            <div>_________________________________</div>
            <div class="mt-2">ผู้รับเงิน / ผู้รับมอบอำนาจ</div>
            <div class="mt-1">วันที่ _______/_______/_______</div>
        </div>
    </div>


    @if($order['note'])
    <div class="mt-3 p-3" style="background:var(--bg); border-radius:var(--radius-sm); font-size:13px; color:var(--text-muted);">
        <strong>หมายเหตุ:</strong> {{ $order['note'] }}
    </div>
    @endif
</div>

{{-- Status Modal --}}
<div class="modal fade no-print" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none; padding:16px 20px 0;">
                <h6 class="modal-title fw-bold">เปลี่ยนสถานะ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button onclick="doStatus('pending')"     class="btn" style="background:#FEF3C7; color:#92400E; border:none; font-family:Sarabun,sans-serif;">รอซ่อม</button>
                    <button onclick="doStatus('in_progress')" class="btn" style="background:#DBEAFE; color:#1E40AF; border:none; font-family:Sarabun,sans-serif;">กำลังซ่อม</button>
                    <button onclick="doStatus('done')"        class="btn" style="background:#D1FAE5; color:#065F46; border:none; font-family:Sarabun,sans-serif;">เสร็จแล้ว ✓</button>
                    <button onclick="doStatus('cancelled')"   class="btn" style="background:#FEE2E2; color:#991B1B; border:none; font-family:Sarabun,sans-serif;">ยกเลิก (คืน Stock)</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
function changeStatus() { statusModal.show(); }

function doStatus(status) {
    if (status === 'cancelled') {
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการยกเลิก?',
            text: 'ระบบจะคืน Stock อะไหล่ทั้งหมดที่ตัดไปโดยอัตโนมัติ',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน ยกเลิก',
            cancelButtonText: 'ไม่ยกเลิก',
            confirmButtonColor: '#EF4444',
        }).then(r => { if (r.isConfirmed) sendStatus(status); });
    } else { sendStatus(status); }
}

function sendStatus(status) {
    $.ajax({
        url: '{{ url("/repair-orders/" . $order["id"] . "/status") }}',
        method: 'PATCH',
        data: { status },
        success: () => location.reload(),
        error: xhr => Swal.fire({ icon:'error', text: xhr.responseJSON?.error || 'ผิดพลาด' })
    });
}
</script>
@endpush
