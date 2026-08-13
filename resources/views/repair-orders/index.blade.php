@extends('layouts.app')
@section('title', 'ใบซ่อม')
@section('page-title', 'ใบซ่อม / ใบเสร็จ')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">ใบซ่อมทั้งหมด</div>
        <div style="color:var(--text-muted); font-size:13px;">จัดการใบซ่อมรถและใบเสร็จรับเงิน</div>
    </div>
    <a href="{{ route('repair-orders.create') }}" class="btn-primary-custom">
        <i class="fa-solid fa-plus"></i> สร้างใบซ่อมใหม่
    </a>
</div>

{{-- STATUS TABS --}}
<div class="mb-3 d-flex gap-2 flex-wrap">
    <button class="filter-btn active" data-status="all"
            style="padding:6px 16px; border-radius:20px; border:1px solid var(--border);
                   background:#fff; cursor:pointer; font-family:Sarabun,sans-serif; font-size:13px; font-weight:600;">
        ทั้งหมด <span class="count-badge"></span>
    </button>
    <button class="filter-btn" data-status="pending"
            style="padding:6px 16px; border-radius:20px; border:1px solid #FEF3C7;
                   background:#FEF3C7; color:#92400E; cursor:pointer; font-family:Sarabun,sans-serif; font-size:13px; font-weight:600;">
        รอซ่อม
    </button>
    <button class="filter-btn" data-status="in_progress"
            style="padding:6px 16px; border-radius:20px; border:1px solid #DBEAFE;
                   background:#DBEAFE; color:#1E40AF; cursor:pointer; font-family:Sarabun,sans-serif; font-size:13px; font-weight:600;">
        กำลังซ่อม
    </button>
    <button class="filter-btn" data-status="done"
            style="padding:6px 16px; border-radius:20px; border:1px solid #D1FAE5;
                   background:#D1FAE5; color:#065F46; cursor:pointer; font-family:Sarabun,sans-serif; font-size:13px; font-weight:600;">
        เสร็จแล้ว
    </button>
    <button class="filter-btn" data-status="cancelled"
            style="padding:6px 16px; border-radius:20px; border:1px solid #FEE2E2;
                   background:#FEE2E2; color:#991B1B; cursor:pointer; font-family:Sarabun,sans-serif; font-size:13px; font-weight:600;">
        ยกเลิก
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="roTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่ใบซ่อม</th>
                        <th>ลูกค้า</th>
                        <th>สถานะ</th>
                        <th class="text-end">ค่าแรง</th>
                        <th class="text-end">ค่าอะไหล่</th>
                        <th class="text-end">ยอดรวม</th>
                        <th>วันที่</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                <tr data-status="{{ $order['status'] }}">
                    <td><strong style="color:var(--primary);">{{ $order['ro_number'] }}</strong></td>
                    <td>{{ $order['customer_name'] }}</td>
                    <td>
                        @php
                        $sMap = ['pending'=>['รอซ่อม','pending'],'in_progress'=>['กำลังซ่อม','in_progress'],
                                 'done'=>['เสร็จแล้ว','done'],'cancelled'=>['ยกเลิก','cancelled']];
                        [$label, $cls] = $sMap[$order['status']] ?? [$order['status'],''];
                        @endphp
                        <span class="badge-status {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td class="text-end">฿{{ number_format($order['labor_total'] ?? 0, 2) }}</td>
                    <td class="text-end">฿{{ number_format($order['parts_total'] ?? 0, 2) }}</td>
                    <td class="text-end"><strong>฿{{ number_format($order['grand_total'] ?? 0, 2) }}</strong></td>
                    <td style="color:var(--text-muted); font-size:13px;">{{ substr($order['created_at'],0,10) }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('repair-orders.show', $order['id']) }}"
                               class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border);">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if($order['status'] !== 'cancelled' && $order['status'] !== 'done')
                            <button onclick="changeStatus('{{ $order['id'] }}', '{{ $order['ro_number'] }}')"
                                    class="btn btn-sm" style="background:#DBEAFE; border:none; color:#1E40AF;">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none; padding:16px 20px 0;">
                <h6 class="modal-title fw-bold">เปลี่ยนสถานะใบซ่อม</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="statusModalRo" style="color:var(--text-muted); font-size:13px;"></p>
                <div class="d-grid gap-2">
                    <button onclick="doStatus('in_progress')" class="btn" style="background:#DBEAFE; color:#1E40AF; border:none; font-family:Sarabun,sans-serif;">กำลังซ่อม</button>
                    <button onclick="doStatus('done')" class="btn" style="background:#D1FAE5; color:#065F46; border:none; font-family:Sarabun,sans-serif;">เสร็จแล้ว ✓</button>
                    <button onclick="doStatus('cancelled')" class="btn" style="background:#FEE2E2; color:#991B1B; border:none; font-family:Sarabun,sans-serif;">ยกเลิก (คืน Stock)</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentRoId = null;

$('#roTable').DataTable({
    language: { url: '{{ asset("asset/datatable/th.json") }}' },
    order: [[6,'desc']], pageLength: 25,
});

// Filter tabs
$('.filter-btn').on('click', function() {
    const status = $(this).data('status');
    $('.filter-btn').css('outline','none');
    $(this).css('outline', '2px solid var(--primary)');

    if (status === 'all') {
        $('#roTable').DataTable().search('').draw();
    } else {
        $('#roTable').DataTable().column(2).search(
            status === 'pending'     ? 'รอซ่อม'     :
            status === 'in_progress' ? 'กำลังซ่อม'  :
            status === 'done'        ? 'เสร็จแล้ว'  : 'ยกเลิก'
        ).draw();
    }
});

function changeStatus(id, roNum) {
    currentRoId = id;
    document.getElementById('statusModalRo').textContent = 'ใบซ่อม: ' + roNum;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function doStatus(status) {
    $.ajax({
        url: '{{ url("/repair-orders") }}/' + currentRoId + '/status',
        method: 'PATCH',
        data: { status },
        success: () => {
            Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
            setTimeout(() => location.reload(), 1300);
        },
        error: (xhr) => Swal.fire({ icon:'error', title:'ผิดพลาด', text: xhr.responseJSON?.error || 'เกิดข้อผิดพลาด' })
    });
}
</script>
@endpush
