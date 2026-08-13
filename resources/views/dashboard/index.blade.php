@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-file-invoice"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['total']) }}</div>
                <div class="stat-label">ใบซ่อมทั้งหมด</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-calendar-day"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['today']) }}</div>
                <div class="stat-label">วันนี้</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-wrench"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['in_progress']) }}</div>
                <div class="stat-label">กำลังดำเนินการ</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['done']) }}</div>
                <div class="stat-label">ปิดงานแล้ว</div>
            </div>
        </div>
    </div>
</div>

{{-- REVENUE --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fa-solid fa-money-bill-wave"></i></div>
            <div class="stat-info">
                <div class="stat-value">฿{{ number_format($stats['revenue_today'], 2) }}</div>
                <div class="stat-label">ยอดขายวันนี้</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-chart-bar"></i></div>
            <div class="stat-info">
                <div class="stat-value">฿{{ number_format($stats['revenue_month'], 2) }}</div>
                <div class="stat-label">ยอดขายเดือนนี้</div>
            </div>
        </div>
    </div>
</div>

{{-- CHART + LOW STOCK --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-chart-area text-primary me-1"></i>
                ยอดขาย 7 วันย้อนหลัง
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                อะไหล่ใกล้หมด
                @if(count($lowStock) > 0)
                <span class="badge ms-auto" style="background:#FEF3C7; color:#92400E;">{{ count($lowStock) }}</span>
                @endif
            </div>
            <div class="card-body p-0" style="overflow-y:auto; max-height:260px;">
                @forelse($lowStock as $part)
                <div style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px;">
                    <div style="flex:1;">
                        <div style="font-weight:600; font-size:13px;">{{ $part['name'] }}</div>
                        <div style="font-size:11px; color:var(--text-muted);">{{ $part['part_code'] }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge {{ (float)$part['stock_qty'] <= 0 ? 'badge-stock-empty' : 'badge-stock-low' }}">
                            {{ $part['stock_qty'] }} {{ $part['unit'] }}
                        </span>
                        <div style="font-size:10px; color:var(--text-muted);">ขั้นต่ำ: {{ $part['min_stock'] }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-state py-4">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <p style="font-size:13px;">Stock ปกติทั้งหมด</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- RECENT ORDERS --}}
<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-clock-rotate-left text-primary me-1"></i>
        ใบซ่อมล่าสุด
        <a href="{{ route('repair-orders.create') }}" class="btn-primary-custom ms-auto" style="font-size:13px; padding:6px 14px;">
            <i class="fa-solid fa-plus"></i> สร้างใบซ่อม
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>เลขที่ใบซ่อม</th>
                        <th>ลูกค้า</th>
                        <th>สถานะ</th>
                        <th class="text-end">ยอดรวม</th>
                        <th>วันที่</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td><strong>{{ $order['ro_number'] }}</strong></td>
                        <td>{{ $order['customer_name'] }}</td>
                        <td>
                            @php
                            $statusMap = [
                                'pending'     => ['label'=>'รอซ่อม',     'class'=>'pending'],
                                'in_progress' => ['label'=>'กำลังซ่อม',  'class'=>'in_progress'],
                                'done'        => ['label'=>'เสร็จแล้ว',  'class'=>'done'],
                                'cancelled'   => ['label'=>'ยกเลิก',     'class'=>'cancelled'],
                            ];
                            $s = $statusMap[$order['status']] ?? ['label'=>$order['status'],'class'=>''];
                            @endphp
                            <span class="badge-status {{ $s['class'] }}">
                                <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                                {{ $s['label'] }}
                            </span>
                        </td>
                        <td class="text-end">฿{{ number_format($order['grand_total'], 2) }}</td>
                        <td style="color:var(--text-muted); font-size:13px;">{{ substr($order['created_at'],0,10) }}</td>
                        <td>
                            <a href="{{ route('repair-orders.show', $order['id']) }}"
                               class="btn btn-sm" style="background:var(--bg); border:1px solid var(--border); font-size:12px;">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีใบซ่อม</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [{
            label: 'ยอดขาย (฿)',
            data: {!! json_encode($chartData['values']) !!},
            backgroundColor: 'rgba(249,115,22,.85)',
            borderColor: '#F97316',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '฿' + Number(ctx.raw).toLocaleString('th-TH', {minimumFractionDigits:2})
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,.05)' },
                ticks: { callback: v => '฿' + v.toLocaleString() }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
