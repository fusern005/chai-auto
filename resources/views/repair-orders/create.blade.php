@extends('layouts.app')
@section('title', 'สร้างใบซ่อม')
@section('page-title', 'สร้างใบซ่อมใหม่')

@section('content')

<form action="{{ route('repair-orders.store') }}" method="POST" id="roForm">
@csrf

<div class="row g-4">
    {{-- LEFT: ข้อมูลหลัก --}}
    <div class="col-lg-8">

        {{-- ข้อมูลลูกค้า/รถ --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa-solid fa-user text-primary"></i> ข้อมูลลูกค้าและรถ
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ลูกค้า <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select" required>
                            <option value="">— เลือกลูกค้า —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c['id'] }}" {{ old('customer_id')==$c['id'] ? 'selected' : '' }}>
                                {{ $c['name'] }} {{ $c['phone'] ? '('.$c['phone'].')' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">รถ <span class="text-danger">*</span></label>
                        <select name="vehicle_id" id="vehicleSelect" class="form-select" required disabled>
                            <option value="">— เลือกลูกค้าก่อน —</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม...">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- รายการค่าแรง --}}
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa-solid fa-screwdriver-wrench text-warning"></i> รายการค่าแรง
                <button type="button" onclick="addLaborRow()" class="btn-navy ms-auto" style="font-size:12px; padding:5px 12px;">
                    <i class="fa-solid fa-plus"></i> เพิ่ม
                </button>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:3fr 1fr 1fr 1fr 36px; gap:8px; padding:0 0 8px; font-size:12px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">
                    <span>รายการ</span><span class="text-center">จำนวน</span><span class="text-end">ราคา/หน่วย</span><span class="text-end">รวม</span><span></span>
                </div>
                <div id="laborRows"></div>
                <div id="noLabor" class="empty-state py-3" style="font-size:13px;">
                    <i class="fa-solid fa-screwdriver-wrench" style="font-size:24px;"></i>
                    <p>กดปุ่ม "เพิ่ม" เพื่อเพิ่มรายการค่าแรง</p>
                </div>
            </div>
        </div>

        {{-- รายการอะไหล่ --}}
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-boxes-stacked text-success"></i> รายการอะไหล่
                <button type="button" onclick="openPartModal()" class="btn-primary-custom ms-auto" style="font-size:12px; padding:5px 12px;">
                    <i class="fa-solid fa-plus"></i> เลือกอะไหล่
                </button>
            </div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 36px; gap:8px; padding:0 0 8px; font-size:12px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">
                    <span>อะไหล่</span><span class="text-center">Stock</span><span class="text-center">จำนวน</span><span class="text-end">ราคา</span><span class="text-end">รวม</span><span></span>
                </div>
                <div id="partRows"></div>
                <div id="noPart" class="empty-state py-3" style="font-size:13px;">
                    <i class="fa-solid fa-boxes-stacked" style="font-size:24px;"></i>
                    <p>กดปุ่ม "เลือกอะไหล่" เพื่อเพิ่ม</p>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: สรุปยอด --}}
    <div class="col-lg-4">
        <div class="card mb-3" style="position:sticky; top:80px;">
            <div class="card-header">
                <i class="fa-solid fa-calculator text-primary"></i> สรุปยอดเงิน
            </div>
            <div class="card-body">
                <div class="totals-box mb-3">
                    <div class="totals-row">
                        <span style="color:var(--text-muted);">ค่าแรงรวม</span>
                        <strong id="laborTotal">฿0.00</strong>
                    </div>
                    <div class="totals-row">
                        <span style="color:var(--text-muted);">ค่าอะไหล่รวม</span>
                        <strong id="partsTotal">฿0.00</strong>
                    </div>
                    <div class="totals-row">
                        <span style="color:var(--text-muted);">ส่วนลด</span>
                        <div class="input-group input-group-sm" style="width:130px;">
                            <span class="input-group-text" style="font-size:12px;">฿</span>
                            <input type="number" name="discount" id="discountInput" class="form-control text-end"
                                   value="0" min="0" step="0.01" style="font-size:13px;">
                        </div>
                    </div>
                    <div class="totals-row">
                        <label style="color:var(--text-muted);">
                            <input type="checkbox" name="use_vat" id="vatCheck" class="me-1">
                            VAT 7%
                        </label>
                        <strong id="vatAmount">฿0.00</strong>
                    </div>
                    <div class="totals-row grand">
                        <span>ยอดสุทธิ</span>
                        <span id="grandTotal">฿0.00</span>
                    </div>
                </div>

                <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="padding:14px; font-size:16px;">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึกใบซ่อม
                </button>
                <a href="{{ route('repair-orders.index') }}" class="btn w-100 mt-2"
                   style="border:1px solid var(--border); font-family:Sarabun,sans-serif; font-size:14px;">
                    ยกเลิก
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Part Selection Modal --}}
<div class="modal fade" id="partModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none; padding:16px 20px 0;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i>เลือกอะไหล่</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="partSearch" class="form-control mb-3" placeholder="ค้นหาด้วยรหัสหรือชื่ออะไหล่...">
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส</th><th>ชื่ออะไหล่</th><th class="text-center">Stock</th><th class="text-end">ราคาทุน</th><th class="text-end">ราคาขาย</th><th></th>
                            </tr>
                        </thead>
                        <tbody id="partModalBody">
                            @foreach($parts as $p)
                            <tr class="part-row"
                                data-id="{{ $p['id'] }}"
                                data-code="{{ $p['part_code'] }}"
                                data-name="{{ $p['name'] }}"
                                data-stock="{{ $p['stock_qty'] }}"
                                data-unit="{{ $p['unit'] }}"
                                data-price="{{ $p['sell_price'] }}">
                                <td><span style="font-family:monospace; font-size:12px;">{{ $p['part_code'] }}</span></td>
                                <td>{{ $p['name'] }}</td>
                                <td class="text-center">
                                    @php $s = (float)$p['stock_qty']; @endphp
                                    <span class="badge {{ $s <= 0 ? 'badge-stock-empty' : ($s <= (float)$p['min_stock'] ? 'badge-stock-low' : 'badge-stock-ok') }}">
                                        {{ $p['stock_qty'] }} {{ $p['unit'] }}
                                    </span>
                                </td>
                                <td class="text-end text-muted" style="font-size:13px;">฿{{ number_format($p['cost_price']??0,2) }}</td>
                                <td class="text-end fw-bold">฿{{ number_format($p['sell_price'],2) }}</td>
                                <td>
                                    <button type="button" onclick="selectPart(this)"
                                            class="btn btn-sm btn-primary-custom" style="font-size:11px; padding:4px 10px;"
                                            {{ (float)$p['stock_qty'] <= 0 ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-plus"></i> เลือก
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ==================== VEHICLES ====================
$('#customerSelect').on('change', function() {
    const custId = $(this).val();
    const $vsel = $('#vehicleSelect');
    $vsel.prop('disabled', true).html('<option>กำลังโหลด...</option>');

    if (!custId) {
        $vsel.prop('disabled',true).html('<option>— เลือกลูกค้าก่อน —</option>');
        return;
    }

    $.get(`{{ url('/master-data/vehicles-by-customer') }}/${custId}`, function(data) {
        let opts = '<option value="">— เลือกรถ —</option>';
        data.forEach(v => {
            opts += `<option value="${v.id}">${v.brand} ${v.model} — ${v.license_plate}</option>`;
        });
        $vsel.html(opts).prop('disabled', false);
    });
});

// ==================== LABOR ROWS ====================
let laborCount = 0;

function addLaborRow() {
    laborCount++;
    $('#noLabor').hide();
    const row = `
    <div class="item-row item-row-labor" id="laborRow${laborCount}">
        <input type="text" name="labor_items[${laborCount}][description]" class="form-control form-control-sm" placeholder="เช่น เปลี่ยนน้ำมันเครื่อง..." required>
        <input type="number" name="labor_items[${laborCount}][qty]" class="form-control form-control-sm text-center labor-qty" value="1" min="1" step="0.5">
        <input type="number" name="labor_items[${laborCount}][unit_price]" class="form-control form-control-sm text-end labor-price" value="0" min="0" step="0.01">
        <input type="number" name="labor_items[${laborCount}][subtotal]" class="form-control form-control-sm text-end labor-subtotal" readonly style="background:var(--bg);">
        <button type="button" class="btn-remove-row" onclick="removeLaborRow(${laborCount})">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>`;
    $('#laborRows').append(row);
    bindLaborCalc(laborCount);
    calcTotals();
}

function bindLaborCalc(n) {
    $(`#laborRow${n} .labor-qty, #laborRow${n} .labor-price`).on('input', function() {
        const qty   = parseFloat($(`#laborRow${n} .labor-qty`).val()) || 0;
        const price = parseFloat($(`#laborRow${n} .labor-price`).val()) || 0;
        $(`#laborRow${n} .labor-subtotal`).val((qty * price).toFixed(2));
        calcTotals();
    });
}

function removeLaborRow(n) {
    $(`#laborRow${n}`).remove();
    if ($('#laborRows .item-row').length === 0) $('#noLabor').show();
    calcTotals();
}

// ==================== PART ROWS ====================
let partCount = 0;
const partModal = new bootstrap.Modal(document.getElementById('partModal'));

function openPartModal() { partModal.show(); }

$('#partSearch').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('.part-row').each(function() {
        const txt = $(this).text().toLowerCase();
        $(this).toggle(txt.includes(q));
    });
});

function selectPart(btn) {
    const row = $(btn).closest('tr');
    const id    = row.data('id');
    const name  = row.data('name');
    const code  = row.data('code');
    const stock = row.data('stock');
    const unit  = row.data('unit');
    const price = row.data('price');

    // ไม่ให้เลือกซ้ำ
    if ($(`#partRows [data-part-id="${id}"]`).length) {
        Swal.fire({ icon:'warning', title:'มีอยู่แล้ว', text:`${name} มีในรายการแล้ว`, timer:1500, showConfirmButton:false });
        return;
    }

    partCount++;
    $('#noPart').hide();

    const htmlRow = `
    <div class="item-row item-row-part" id="partRow${partCount}" data-part-id="${id}">
        <input type="hidden" name="part_items[${partCount}][part_id]" value="${id}">
        <div style="align-self:center;">
            <div style="font-weight:600; font-size:13px;">${name}</div>
            <div style="font-size:11px; color:var(--text-muted);">${code}</div>
        </div>
        <div class="text-center" style="align-self:center;">
            <span class="badge ${parseFloat(stock)<=0 ? 'badge-stock-empty' : 'badge-stock-ok'}" id="stockBadge${partCount}">
                ${stock} ${unit}
            </span>
        </div>
        <input type="number" name="part_items[${partCount}][qty]" class="form-control form-control-sm text-center part-qty"
               value="1" min="1" max="${stock}" step="1" data-max="${stock}">
        <input type="number" name="part_items[${partCount}][unit_price]" class="form-control form-control-sm text-end part-price"
               value="${price}" min="0" step="0.01">
        <input type="number" name="part_items[${partCount}][subtotal]" class="form-control form-control-sm text-end part-subtotal"
               readonly style="background:var(--bg);">
        <button type="button" class="btn-remove-row" onclick="removePartRow(${partCount})">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>`;
    $('#partRows').append(htmlRow);
    bindPartCalc(partCount, price);
    $(`#partRow${partCount} .part-qty`).trigger('input');
    partModal.hide();
}

function bindPartCalc(n, price) {
    $(`#partRow${n} .part-qty, #partRow${n} .part-price`).on('input', function() {
        const qty   = parseFloat($(`#partRow${n} .part-qty`).val()) || 0;
        const price = parseFloat($(`#partRow${n} .part-price`).val()) || 0;
        const max   = parseFloat($(`#partRow${n} .part-qty`).data('max'));
        if (qty > max) {
            $(`#partRow${n} .part-qty`).val(max);
            Swal.fire({ icon:'warning', title:'Stock ไม่เพียงพอ', text:`Stock คงเหลือ ${max} ชิ้น`, timer:1500, showConfirmButton:false });
        }
        $(`#partRow${n} .part-subtotal`).val((Math.min(qty,max) * price).toFixed(2));
        calcTotals();
    });
}

function removePartRow(n) {
    $(`#partRow${n}`).remove();
    if ($('#partRows .item-row').length === 0) $('#noPart').show();
    calcTotals();
}

// ==================== TOTALS ====================
function calcTotals() {
    let labor = 0, parts = 0;
    $('.labor-subtotal').each(function() { labor += parseFloat($(this).val()) || 0; });
    $('.part-subtotal').each(function()  { parts += parseFloat($(this).val()) || 0; });

    const discount = parseFloat($('#discountInput').val()) || 0;
    const useVat   = $('#vatCheck').is(':checked');
    const sub      = labor + parts - discount;
    const vat      = useVat ? sub * 0.07 : 0;
    const grand    = sub + vat;

    $('#laborTotal').text('฿' + labor.toLocaleString('th-TH', {minimumFractionDigits:2}));
    $('#partsTotal').text('฿' + parts.toLocaleString('th-TH', {minimumFractionDigits:2}));
    $('#vatAmount').text('฿' + vat.toLocaleString('th-TH', {minimumFractionDigits:2}));
    $('#grandTotal').text('฿' + grand.toLocaleString('th-TH', {minimumFractionDigits:2}));
}

$('#discountInput, #vatCheck').on('input change', calcTotals);

// Form validation
$('#roForm').on('submit', function(e) {
    const hasItems = ($('#laborRows .item-row').length + $('#partRows .item-row').length) > 0;
    if (!hasItems) {
        e.preventDefault();
        Swal.fire({ icon:'warning', title:'กรุณาเพิ่มรายการ', text:'ต้องมีรายการค่าแรงหรืออะไหล่อย่างน้อย 1 รายการ' });
    }
});
</script>
@endpush
