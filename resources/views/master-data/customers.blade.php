@extends('layouts.app')
@section('title', 'จัดการลูกค้า')
@section('page-title', 'จัดการลูกค้า')

@section('content')

<div class="page-header">
    <div><div class="page-title">รายการลูกค้า</div></div>
    <button onclick="openAdd()" class="btn-primary-custom">
        <i class="fa-solid fa-user-plus"></i> เพิ่มลูกค้า
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table id="custTable" class="table mb-0">
            <thead><tr><th>ชื่อ</th><th>โทรศัพท์</th><th>อีเมล</th><th>ที่อยู่</th><th></th></tr></thead>
            <tbody>
            @foreach($customers as $c)
            <tr>
                <td><strong>{{ $c['name'] }}</strong></td>
                <td>{{ $c['phone'] ?: '-' }}</td>
                <td>{{ $c['email'] ?: '-' }}</td>
                <td>{{ $c['address'] ?: '-' }}</td>
                <td><button onclick='openEdit(@json($c))' class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border);"><i class="fa-solid fa-pen"></i></button></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;"><h6 class="modal-title fw-bold">เพิ่มลูกค้า</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">ชื่อ *</label><input type="text" id="add_name" class="form-control"></div>
                <div class="mb-3"><label class="form-label">โทรศัพท์</label><input type="text" id="add_phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">อีเมล</label><input type="email" id="add_email" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ที่อยู่</label><textarea id="add_address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button onclick="saveCustomer()" class="btn-primary-custom"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:var(--radius);">
            <div class="modal-header" style="border:none;"><h6 class="modal-title fw-bold">แก้ไขลูกค้า</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="mb-3"><label class="form-label">ชื่อ</label><input type="text" id="edit_name" class="form-control"></div>
                <div class="mb-3"><label class="form-label">โทรศัพท์</label><input type="text" id="edit_phone" class="form-control"></div>
                <div class="mb-3"><label class="form-label">อีเมล</label><input type="email" id="edit_email" class="form-control"></div>
                <div class="mb-3"><label class="form-label">ที่อยู่</label><textarea id="edit_address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button onclick="updateCustomer()" class="btn-navy"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#custTable').DataTable({ language: { url: '{{ asset("asset/datatable/th.json") }}' }, pageLength:25 });

const addM = new bootstrap.Modal(document.getElementById('addModal'));
const editM = new bootstrap.Modal(document.getElementById('editModal'));

function openAdd() { addM.show(); }
function openEdit(c) {
    $('#edit_id').val(c.id); $('#edit_name').val(c.name); $('#edit_phone').val(c.phone);
    $('#edit_email').val(c.email); $('#edit_address').val(c.address);
    editM.show();
}

function saveCustomer() {
    $.post('{{ url("/master-data/customers") }}', { name:$('#add_name').val(), phone:$('#add_phone').val(), email:$('#add_email').val(), address:$('#add_address').val() }, () => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    });
}

function updateCustomer() {
    const id = $('#edit_id').val();
    $.ajax({ url:`{{ url("/master-data/customers") }}/${id}`, method:'PUT', data:{ name:$('#edit_name').val(), phone:$('#edit_phone').val(), email:$('#edit_email').val(), address:$('#edit_address').val() }, success: () => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    }});
}
</script>
@endpush
