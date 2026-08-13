@extends('layouts.app')
@section('title', 'สร้างเอกสารรับเข้า')
@section('page-title', 'สร้างเอกสารรับเข้า')

@section('content')

<form action="{{ route('goods-receipts.store') }}" method="POST" id="grForm">
@csrf

<div class="row g-4">
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-header">
                <i class="fa-solid fa-truck text-primary"></i> ข้อมูลการรับเข้า
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">— เลือก Supplier —</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วันที่รับเข้า <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">หมายเหตุ</label>
                        <input type="text" name="note" class="form-control" placeholder="หมายเหตุ...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-list text-success"></i> รายการอะไหล่
                <div class="ms-auto d-flex gap-2">
                    <button type="button" onclick="openScanModal()" class="btn-navy" style="font-size:12px; padding:5px 12px;">
                        <i class="fa-solid fa-qrcode"></i> Scan QR/Barcode
                    </button>
                    <button type="button" onclick="addItemRow()" class="btn-primary-custom" style="font-size:12px; padding:5px 12px;">
                        <i class="fa-solid fa-plus"></i> เพิ่มรายการ
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 36px; gap:8px; padding:0 0 8px; font-size:12px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">
                    <span>อะไหล่</span><span class="text-center">จำนวน</span><span class="text-end">ราคาทุน/หน่วย</span><span class="text-end">รวม</span><span></span>
                </div>
                <div id="itemRows"></div>
                <div id="noItem" class="empty-state py-3" style="font-size:13px;">
                    <i class="fa-solid fa-truck-ramp-box" style="font-size:24px;"></i>
                    <p>เพิ่มรายการอะไหล่ที่รับเข้า</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card" style="position:sticky; top:80px;">
            <div class="card-header">
                <i class="fa-solid fa-calculator text-primary"></i> สรุปมูลค่า
            </div>
            <div class="card-body">
                <div class="totals-box mb-3">
                    <div class="totals-row grand">
                        <span>มูลค่ารวม</span>
                        <span id="grandTotalGR">฿0.00</span>
                    </div>
                </div>
                <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px; font-size:16px;">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึก + เพิ่ม Stock
                </button>
                <a href="{{ route('goods-receipts.index') }}" class="btn w-100 mt-2"
                   style="border:1px solid var(--border); font-family:Sarabun,sans-serif; font-size:14px;">
                    ยกเลิก
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Scan Modal --}}
<div class="modal fade" id="scanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-qrcode me-2"></i>Scan QR / Barcode</h6>
                <button type="button" class="btn-close" onclick="stopScan()" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <video id="video" style="width:100%; border-radius:var(--radius-sm); background:#000;" autoplay></video>
                <div id="scanResult" class="mt-3 p-3" style="display:none; background:var(--bg); border-radius:var(--radius-sm);">
                    <strong>สแกนได้:</strong> <span id="scanCode"></span>
                </div>
                <p style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                    ระบบจะเพิ่มอะไหล่โดยอัตโนมัติเมื่อสแกน QR/Barcode ที่ตรงกับรหัสอะไหล่ในคลัง
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Parts data for select
const partsData = @json(array_values($parts));

let itemCount = 0;

function addItemRow(partId = '', partName = '', price = 0) {
    itemCount++;
    $('#noItem').hide();
    const opts = partsData.map(p =>
        `<option value="${p.id}" data-price="${p.cost_price}" ${p.id==partId?'selected':''}>${p.part_code} — ${p.name}</option>`
    ).join('');

    const row = `
    <div class="item-row" id="grRow${itemCount}" style="grid-template-columns:2fr 1fr 1fr 1fr 36px;">
        <select name="items[${itemCount}][part_id]" class="form-select form-select-sm gr-part" onchange="onPartChange(${itemCount})">
            <option value="">— เลือกอะไหล่ —</option>
            ${opts}
        </select>
        <input type="number" name="items[${itemCount}][qty]" class="form-control form-control-sm text-center gr-qty" value="1" min="1">
        <input type="number" name="items[${itemCount}][unit_cost]" class="form-control form-control-sm text-end gr-cost" value="${price}" min="0" step="0.01">
        <input type="number" name="items[${itemCount}][subtotal]" class="form-control form-control-sm text-end gr-sub" readonly style="background:var(--bg);">
        <button type="button" class="btn-remove-row" onclick="removeRow(${itemCount})">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>`;
    $('#itemRows').append(row);
    bindCalc(itemCount);
    $(`#grRow${itemCount} .gr-qty`).trigger('input');
}

function onPartChange(n) {
    const sel   = $(`#grRow${n} .gr-part`);
    const price = sel.find(':selected').data('price') || 0;
    $(`#grRow${n} .gr-cost`).val(price);
    $(`#grRow${n} .gr-qty`).trigger('input');
}

function bindCalc(n) {
    $(`#grRow${n} .gr-qty, #grRow${n} .gr-cost`).on('input', function() {
        const qty  = parseFloat($(`#grRow${n} .gr-qty`).val()) || 0;
        const cost = parseFloat($(`#grRow${n} .gr-cost`).val()) || 0;
        $(`#grRow${n} .gr-sub`).val((qty * cost).toFixed(2));
        calcGRTotal();
    });
}

function removeRow(n) {
    $(`#grRow${n}`).remove();
    if ($('#itemRows .item-row').length === 0) $('#noItem').show();
    calcGRTotal();
}

function calcGRTotal() {
    let total = 0;
    $('.gr-sub').each(function() { total += parseFloat($(this).val()) || 0; });
    $('#grandTotalGR').text('฿' + total.toLocaleString('th-TH',{minimumFractionDigits:2}));
}

// Scan
let stream = null;

function openScanModal() {
    new bootstrap.Modal(document.getElementById('scanModal')).show();
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(s => { stream = s; document.getElementById('video').srcObject = s; })
        .catch(() => {
            Swal.fire({ icon:'info', title:'ไม่พบกล้อง', text:'พิมพ์รหัสอะไหล่แทนการสแกน' });
        });
}

function stopScan() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
}

$('#scanModal').on('hidden.bs.modal', stopScan);

$('#grForm').on('submit', function(e) {
    const hasItems = $('#itemRows .item-row').length > 0;
    if (!hasItems) {
        e.preventDefault();
        Swal.fire({ icon:'warning', title:'กรุณาเพิ่มรายการอะไหล่', });
    }
});
</script>
@endpush
