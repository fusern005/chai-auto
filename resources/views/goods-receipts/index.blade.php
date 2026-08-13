@extends('layouts.app')
@section('title', 'รับอะไหล่เข้า')
@section('page-title', 'รับอะไหล่เข้า')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">รับอะไหล่เข้า</div>
        <div style="color:var(--text-muted); font-size:13px;">ประวัติเอกสารรับสินค้าเข้าคลัง</div>
    </div>
    <a href="{{ route('goods-receipts.create') }}" class="btn-primary-custom">
        <i class="fa-solid fa-plus"></i> สร้างเอกสารรับเข้า
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="grTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่เอกสาร</th>
                        <th>Supplier</th>
                        <th>วันที่รับ</th>
                        <th class="text-end">มูลค่ารวม</th>
                        <th>วันที่สร้าง</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($receipts as $r)
                <tr>
                    <td><strong style="color:var(--primary);">{{ $r['gr_number'] }}</strong></td>
                    <td>{{ $r['supplier_name'] }}</td>
                    <td>{{ $r['receipt_date'] }}</td>
                    <td class="text-end"><strong>฿{{ number_format($r['total_cost'],2) }}</strong></td>
                    <td style="color:var(--text-muted); font-size:13px;">{{ substr($r['created_at'],0,10) }}</td>
                    <td>
                        <button onclick="viewGR('{{ $r['id'] }}')" class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border);">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="grDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-truck-ramp-box me-2"></i>รายละเอียดรับเข้า</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="grDetailBody">
                <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> กำลังโหลด...</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#grTable').DataTable({
    language: { url: '{{ asset("asset/datatable/th.json") }}' },
    order: [[4,'desc']], pageLength: 25
});

const grModal = new bootstrap.Modal(document.getElementById('grDetailModal'));

function viewGR(id) {
    $('#grDetailBody').html('<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> กำลังโหลด...</div>');
    grModal.show();

    $.get(`{{ url("/goods-receipts") }}/${id}`, function(data) {
        let html = `
        <div class="row mb-3">
            <div class="col-6">
                <div style="font-size:12px; color:var(--text-muted);">เลขที่เอกสาร</div>
                <div style="font-weight:800; font-size:18px; color:var(--primary);">${data.receipt.gr_number}</div>
            </div>
            <div class="col-6 text-end">
                <div style="font-size:12px; color:var(--text-muted);">Supplier</div>
                <div style="font-weight:600;">${data.supplier?.name || '-'}</div>
                <div style="font-size:12px; color:var(--text-muted);">วันที่รับ: ${data.receipt.receipt_date}</div>
            </div>
        </div>
        <table class="table">
            <thead><tr><th>รหัส</th><th>อะไหล่</th><th class="text-center">จำนวน</th><th class="text-end">ราคาทุน</th><th class="text-end">รวม</th></tr></thead>
            <tbody>`;
        data.items.forEach(item => {
            html += `<tr>
                <td><code style="font-size:12px;">${item.part_code}</code></td>
                <td>${item.part_name}</td>
                <td class="text-center">${item.qty}</td>
                <td class="text-end">฿${parseFloat(item.unit_cost).toLocaleString('th-TH',{minimumFractionDigits:2})}</td>
                <td class="text-end"><strong>฿${parseFloat(item.subtotal).toLocaleString('th-TH',{minimumFractionDigits:2})}</strong></td>
            </tr>`;
        });
        html += `</tbody></table>
        <div class="text-end" style="border-top:2px solid var(--primary); padding-top:10px;">
            <strong style="font-size:18px; color:var(--accent);">มูลค่ารวม: ฿${parseFloat(data.receipt.total_cost).toLocaleString('th-TH',{minimumFractionDigits:2})}</strong>
        </div>`;
        $('#grDetailBody').html(html);
    });
}
</script>
@endpush
