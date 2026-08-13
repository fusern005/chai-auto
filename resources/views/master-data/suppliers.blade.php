@extends('layouts.app')
@section('title', 'จัดการ Supplier')
@section('page-title', 'จัดการ Supplier')

@section('content')

<div class="page-header">
    <div><div class="page-title">รายการ Supplier</div></div>
    <button onclick="openAdd()" class="btn-primary-custom">
        <i class="fa-solid fa-building-circle-arrow-right"></i> เพิ่ม Supplier
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table id="supTable" class="table mb-0">
            <thead><tr><th>ชื่อ Supplier</th><th>ผู้ติดต่อ</th><th>โทรศัพท์</th><th>ที่อยู่</th><th></th></tr></thead>
            <tbody>
            @foreach($suppliers as $s)
            <tr>
                <td><strong>{{ $s['name'] }}</strong></td>
                <td>{{ $s['contact'] ?: '-' }}</td>
                <td>{{ $s['phone'] ?: '-' }}</td>
                <td>{{ $s['address'] ?: '-' }}</td>
                <td><button onclick='openEdit(@json($s))' class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border);"><i class="fa-solid fa-pen"></i></button></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;"><h6 class="modal-title fw-bold">เพิ่ม Supplier</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">ชื่อ Supplier *</label><input type="text" id="add_name" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ผู้ติดต่อ</label><input type="text" id="add_contact" class="form-control"></div>
                <div class="mb-3"><label class="form-label">โทรศัพท์</label><input type="text" id="add_phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ที่อยู่</label><textarea id="add_address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button onclick="saveSupplier()" class="btn-primary-custom"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;"><h6 class="modal-title fw-bold">แก้ไข Supplier</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="mb-3"><label class="form-label">ชื่อ Supplier</label><input type="text" id="edit_name" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ผู้ติดต่อ</label><input type="text" id="edit_contact" class="form-control"></div>
                <div class="mb-3"><label class="form-label">โทรศัพท์</label><input type="text" id="edit_phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ที่อยู่</label><textarea id="edit_address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button onclick="updateSupplier()" class="btn-navy"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#supTable').DataTable({ language: { url: '{{ asset("asset/datatable/th.json") }}' }, pageLength:25 });
const addM = new bootstrap.Modal(document.getElementById('addModal'));
const editM = new bootstrap.Modal(document.getElementById('editModal'));

function openAdd() { addM.show(); }
function openEdit(s) {
    $('#edit_id').val(s.id); $('#edit_name').val(s.name);
    $('#edit_contact').val(s.contact); $('#edit_phone').val(s.phone); $('#edit_address').val(s.address);
    editM.show();
}

function saveSupplier() {
    $.post('{{ url("/master-data/suppliers") }}', { name:$('#add_name').val(), contact:$('#add_contact').val(), phone:$('#add_phone').val(), address:$('#add_address').val() }, () => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    });
}

function updateSupplier() {
    $.ajax({ url:`{{ url("/master-data/suppliers") }}/${$('#edit_id').val()}`, method:'PUT', data:{ name:$('#edit_name').val(), contact:$('#edit_contact').val(), phone:$('#edit_phone').val(), address:$('#edit_address').val() }, success:() => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    }});
}
</script>
@endpush
