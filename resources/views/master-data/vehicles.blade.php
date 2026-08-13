@extends('layouts.app')
@section('title', 'จัดการยานพาหนะ')
@section('page-title', 'จัดการยานพาหนะ')

@section('content')

<div class="page-header">
    <div><div class="page-title">รายการยานพาหนะ</div></div>
    <button onclick="openAdd()" class="btn-primary-custom">
        <i class="fa-solid fa-car"></i> เพิ่มรถ
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table id="vehTable" class="table mb-0">
            <thead><tr><th>เจ้าของ</th><th>ยี่ห้อ/รุ่น</th><th>ทะเบียน</th><th>ปี</th><th>สี</th></tr></thead>
            <tbody>
            @foreach($vehicles as $v)
            <tr>
                <td>{{ $v['customer_name'] }}</td>
                <td><strong>{{ $v['brand'] }} {{ $v['model'] }}</strong></td>
                <td><span style="background:var(--primary); color:#fff; padding:3px 10px; border-radius:4px; font-size:13px; font-weight:700;">{{ $v['license_plate'] }}</span></td>
                <td>{{ $v['year'] ?: '-' }}</td>
                <td>{{ $v['color'] ?: '-' }}</td>
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
            <div class="modal-header" style="border:none;"><h6 class="modal-title fw-bold">เพิ่มยานพาหนะ</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">เจ้าของ *</label>
                        <select id="add_cust" class="form-select">
                            <option value="">— เลือกลูกค้า —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6"><label class="form-label">ยี่ห้อ</label><input type="text" id="add_brand" class="form-control" placeholder="เช่น Toyota"></div>
                    <div class="col-6"><label class="form-label">รุ่น</label><input type="text" id="add_model" class="form-control" placeholder="เช่น Camry"></div>
                    <div class="col-6"><label class="form-label">ทะเบียน *</label><input type="text" id="add_plate" class="form-control" placeholder="กก 1234 กรุงเทพ"></div>
                    <div class="col-6"><label class="form-label">ปี</label><input type="text" id="add_year" class="form-control" placeholder="2565"></div>
                    <div class="col-6"><label class="form-label">สี</label><input type="text" id="add_color" class="form-control" placeholder="สีขาว"></div>
                </div>
            </div>
            <div class="modal-footer" style="border:none;">
                <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button onclick="saveVehicle()" class="btn-primary-custom"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$('#vehTable').DataTable({ language: { url: '{{ asset("asset/datatable/th.json") }}' }, pageLength:25 });
const addM = new bootstrap.Modal(document.getElementById('addModal'));
function openAdd() { addM.show(); }

function saveVehicle() {
    $.post('{{ url("/master-data/vehicles") }}', {
        customer_id: $('#add_cust').val(), brand: $('#add_brand').val(),
        model: $('#add_model').val(), license_plate: $('#add_plate').val(),
        year: $('#add_year').val(), color: $('#add_color').val()
    }, () => {
        Swal.fire({ icon:'success', title:'บันทึกสำเร็จ', timer:1200, showConfirmButton:false });
        setTimeout(() => location.reload(), 1300);
    }).fail(xhr => Swal.fire({ icon:'error', text: xhr.responseJSON?.message || 'ผิดพลาด' }));
}
</script>
@endpush
