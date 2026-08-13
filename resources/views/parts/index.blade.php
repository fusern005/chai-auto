@extends('layouts.app')
@section('title', 'คลังอะไหล่')
@section('page-title', 'คลังอะไหล่')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">คลังอะไหล่</div>
        <div style="color:var(--text-muted); font-size:13px;">จัดการรายการอะไหล่และดูสต๊อกคงเหลือ</div>
    </div>
    <button onclick="openAddModal()" class="btn-primary-custom">
        <i class="fa-solid fa-plus"></i> เพิ่มอะไหล่ใหม่
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="partsTable" class="table mb-0">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่ออะไหล่</th>
                        <th>หน่วย</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">ราคาทุน</th>
                        <th class="text-end">ราคาขาย</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($parts as $p)
                @php
                $stock = (float)$p['stock_qty'];
                $minStock = (float)$p['min_stock'];
                $stockClass = $stock <= 0 ? 'badge-stock-empty' : ($stock <= $minStock ? 'badge-stock-low' : 'badge-stock-ok');
                @endphp
                <tr>
                    <td><code style="font-size:12px; color:var(--primary);">{{ $p['part_code'] }}</code></td>
                    <td>
                        <strong>{{ $p['name'] }}</strong>
                        @if($stock <= $minStock && $stock > 0)
                        <i class="fa-solid fa-triangle-exclamation text-warning ms-1" title="ใกล้หมด"></i>
                        @elseif($stock <= 0)
                        <i class="fa-solid fa-circle-xmark text-danger ms-1" title="หมด"></i>
                        @endif
                    </td>
                    <td>{{ $p['unit'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $stockClass }}">{{ $p['stock_qty'] }}</span>
                        <div style="font-size:10px; color:var(--text-muted);">ขั้นต่ำ: {{ $p['min_stock'] }}</div>
                    </td>
                    <td class="text-end">฿{{ number_format($p['cost_price'],2) }}</td>
                    <td class="text-end">฿{{ number_format($p['sell_price'],2) }}</td>
                    <td>
                        <span class="badge-status {{ $p['is_active']!=='0' ? 'done' : 'cancelled' }}">
                            {{ $p['is_active']!=='0' ? 'ใช้งาน' : 'ปิด' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button onclick='openEditModal(@json($p))' class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border);" title="แก้ไข">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button onclick="viewMovements('{{ $p['id'] }}','{{ $p['name'] }}')" class="btn btn-sm" style="background:#DBEAFE; border:none; color:#1E40AF;" title="ประวัติ Stock">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </button>
                            <button onclick="togglePart('{{ $p['id'] }}',{{ $p['is_active']!=='0' ? 'true' : 'false' }})" class="btn btn-sm"
                                    style="background:{{ $p['is_active']!=='0' ? '#FEE2E2' : '#D1FAE5' }}; border:none; color:{{ $p['is_active']!=='0' ? '#991B1B' : '#065F46' }};"
                                    title="{{ $p['is_active']!=='0' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                <i class="fa-solid fa-{{ $p['is_active']!=='0' ? 'ban' : 'check' }}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2 text-primary"></i>เพิ่มอะไหล่ใหม่</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">รหัสอะไหล่ *</label>
                        <div class="input-group">
                            <input type="text" id="add_code" class="form-control" placeholder="เช่น OIL-001">
                            <button type="button" class="btn btn-outline-primary" onclick="openQRScanner('add_code')" title="สแกน QR / Barcode">
                                <i class="fa-solid fa-qrcode"></i> สแกน
                            </button>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">หน่วย *</label>
                        <select id="add_unit" class="form-select">
                            <option>ชิ้น</option><option>ชุด</option><option>ลิตร</option><option>อัน</option><option>กล่อง</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ชื่ออะไหล่ *</label>
                        <input type="text" id="add_name" class="form-control" placeholder="เช่น น้ำมันเครื่อง 10W-40">
                    </div>
                    <div class="col-6">
                        <label class="form-label">ราคาทุน (฿)</label>
                        <input type="number" id="add_cost" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">ราคาขาย (฿) *</label>
                        <input type="number" id="add_sell" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Stock เริ่มต้น</label>
                        <input type="number" id="add_stock" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Stock ขั้นต่ำ</label>
                        <input type="number" id="add_min" class="form-control" value="5" min="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" onclick="savePart()" class="btn-primary-custom">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-pen me-2 text-primary"></i>แก้ไขอะไหล่</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">ชื่ออะไหล่</label>
                        <input type="text" id="edit_name" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">หน่วย</label>
                        <select id="edit_unit" class="form-select">
                            <option>ชิ้น</option><option>ชุด</option><option>ลิตร</option><option>อัน</option><option>กล่อง</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Stock ขั้นต่ำ</label>
                        <input type="number" id="edit_min" class="form-control" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">ราคาทุน (฿)</label>
                        <input type="number" id="edit_cost" class="form-control" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">ราคาขาย (฿)</label>
                        <input type="number" id="edit_sell" class="form-control" min="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" onclick="updatePart()" class="btn-navy">
                    <i class="fa-solid fa-floppy-disk"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Movement History Modal --}}
<div class="modal fade" id="movementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>ประวัติ Stock — <span id="movPartName"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:400px; overflow-y:auto;" id="movementBody">
                <div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> กำลังโหลด...</div>
            </div>
        </div>
    </div>
</div>

{{-- QR Scanner Modal --}}
<div class="modal fade" id="qrScannerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-qrcode me-2 text-primary"></i>สแกน QR Code / Barcode</h6>
                <button type="button" class="btn-close" onclick="closeQRScanner()"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width:100%; max-width:360px; margin:0 auto; border-radius:10px; overflow:hidden;"></div>
                <div id="qr-reader-results" class="mt-3 text-muted" style="font-size:13px;">
                    <i class="fa-solid fa-camera me-1"></i> เล็งกล้องไปที่ QR Code หรือ Barcode อะไหล่
                </div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button type="button" class="btn btn-secondary" onclick="closeQRScanner()">ยกเลิก/ปิดกล้อง</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('asset/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
$('#partsTable').DataTable({
    language: { url: '{{ asset("asset/datatable/th.json") }}' },
    order: [[0,'asc']], pageLength: 25
});

const addModal  = new bootstrap.Modal(document.getElementById('addPartModal'));
const editModal = new bootstrap.Modal(document.getElementById('editPartModal'));
const movModal  = new bootstrap.Modal(document.getElementById('movementModal'));

function openAddModal() { addModal.show(); }

function openEditModal(p) {
    $('#edit_id').val(p.id);
    $('#edit_name').val(p.name);
    $('#edit_unit').val(p.unit);
    $('#edit_min').val(p.min_stock);
    $('#edit_cost').val(p.cost_price);
    $('#edit_sell').val(p.sell_price);
    editModal.show();
}

function savePart() {
    $.post('{{ url("/parts") }}', {
        part_code: $('#add_code').val(), name: $('#add_name').val(),
        unit: $('#add_unit').val(), cost_price: $('#add_cost').val(),
        sell_price: $('#add_sell').val(), initial_stock: $('#add_stock').val(),
        min_stock: $('#add_min').val()
    }, function() {
        Swal.fire({ icon:'success', title:'เพิ่มสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    }).fail(xhr => Swal.fire({ icon:'error', text: xhr.responseJSON?.error || 'ผิดพลาด' }));
}

function updatePart() {
    const id = $('#edit_id').val();
    $.ajax({ url: `{{ url("/parts") }}/${id}`, method: 'PUT', data: {
        name: $('#edit_name').val(), unit: $('#edit_unit').val(),
        min_stock: $('#edit_min').val(), cost_price: $('#edit_cost').val(),
        sell_price: $('#edit_sell').val()
    }, success: () => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    }});
}

function togglePart(id, isActive) {
    const msg = isActive ? 'ปิดการใช้งานอะไหล่นี้?' : 'เปิดการใช้งานอะไหล่นี้?';
    Swal.fire({ icon:'question', title:msg, showCancelButton:true, confirmButtonText:'ยืนยัน', cancelButtonText:'ยกเลิก' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: `{{ url("/parts") }}/${id}/toggle`, method: 'PATCH',
            success: () => location.reload()
        });
    });
}

function viewMovements(partId, partName) {
    $('#movPartName').text(partName);
    $('#movementBody').html('<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin"></i> กำลังโหลด...</div>');
    movModal.show();

    $.get(`{{ url("/parts") }}/${partId}/movements`, function(data) {
        if (!data.movements.length) {
            $('#movementBody').html('<div class="empty-state"><p>ไม่มีประวัติ Stock</p></div>');
            return;
        }
        let html = '<table class="table"><thead><tr><th>วันที่</th><th>ประเภท</th><th class="text-center">จำนวน</th><th class="text-end">คงเหลือ</th><th>อ้างอิง</th></tr></thead><tbody>';
        data.movements.forEach(m => {
            const typeMap = { 'in': ['รับเข้า','badge-stock-ok'], 'out': ['ตัดออก','badge-stock-empty'], 'return': ['คืน Stock','badge-stock-low'] };
            const [tLabel, tCls] = typeMap[m.movement_type] || [m.movement_type, ''];
            html += `<tr>
                <td style="font-size:12px;">${m.created_at.substring(0,16)}</td>
                <td><span class="badge ${tCls}">${tLabel}</span></td>
                <td class="text-center">${m.qty}</td>
                <td class="text-end"><strong>${m.balance}</strong></td>
                <td style="font-size:12px; color:var(--text-muted);">${m.ref_number || m.ref_id}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        $('#movementBody').html(html);
    });
}

// ==================== QR SCANNER ====================
let html5QrCode = null;
let currentTargetInputId = null;

function openQRScanner(targetInputId) {
    currentTargetInputId = targetInputId;
    const qrModal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    qrModal.show();

    setTimeout(() => {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }
        
        const config = { fps: 10, qrbox: { width: 220, height: 220 } };
        
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText, decodedResult) => {
                $(`#${currentTargetInputId}`).val(decodedText);
                Swal.fire({
                    icon: 'success',
                    title: 'สแกนสำเร็จ!',
                    text: `รหัสอะไหล่: ${decodedText}`,
                    timer: 1500,
                    showConfirmButton: false
                });
                closeQRScanner();
            },
            (errorMessage) => {
                // Scanning...
            }
        ).catch(err => {
            console.error("Camera error:", err);
            $('#qr-reader-results').html('<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>ไม่สามารถเปิดกล้องได้ หรือโปรดอนุญาตสิทธิ์กล้องในเบราว์เซอร์</span>');
        });
    }, 400);
}

function closeQRScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            $('#qrScannerModal').modal('hide');
        }).catch(err => {
            console.error("Stop failed:", err);
            $('#qrScannerModal').modal('hide');
        });
    } else {
        $('#qrScannerModal').modal('hide');
    }
}
</script>
@endpush
