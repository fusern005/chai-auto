<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    protected GoogleSheetsService $sheets;
    protected StockService $stock;

    public function __construct(GoogleSheetsService $sheets, StockService $stock)
    {
        $this->sheets = $sheets;
        $this->stock  = $stock;
    }

    public function index()
    {
        $repairOrders = $this->sheets->readAll('repair_orders');
        $today = now()->toDateString();

        $stats = [
            'total'       => count($repairOrders),
            'today'       => count(array_filter($repairOrders, fn($r) => str_starts_with($r['created_at'] ?? '', $today))),
            'in_progress' => count(array_filter($repairOrders, fn($r) => $r['status'] === 'in_progress')),
            'done'        => count(array_filter($repairOrders, fn($r) => $r['status'] === 'done')),
        ];

        // ยอดขายวันนี้
        $todayOrders = array_filter($repairOrders, fn($r) =>
            str_starts_with($r['created_at'] ?? '', $today) && $r['status'] !== 'cancelled'
        );
        $stats['revenue_today'] = array_sum(array_column($todayOrders, 'grand_total'));

        // ยอดขายเดือนนี้
        $month = now()->format('Y-m');
        $monthOrders = array_filter($repairOrders, fn($r) =>
            str_starts_with($r['created_at'] ?? '', $month) && $r['status'] !== 'cancelled'
        );
        $stats['revenue_month'] = array_sum(array_column($monthOrders, 'grand_total'));

        // อะไหล่ใกล้หมด
        $lowStock = $this->stock->getLowStockParts();

        // ใบซ่อมล่าสุด 5 รายการ
        $recentOrders = array_slice(array_reverse($repairOrders), 0, 5);

        // ดึงชื่อลูกค้ามาใส่ใน recentOrders
        $customers = $this->sheets->readAll('customers');
        $custMap = [];
        foreach ($customers as $c) { $custMap[$c['id']] = $c['name']; }
        foreach ($recentOrders as &$o) {
            $o['customer_name'] = $custMap[$o['customer_id']] ?? '-';
        }

        // Chart data - ยอดขาย 7 วันย้อนหลัง
        $chartData = $this->getChartData($repairOrders);

        return view('dashboard.index', compact('stats', 'lowStock', 'recentOrders', 'chartData'));
    }

    public function syncSheets()
    {
        $tables = ['parts', 'suppliers', 'customers', 'vehicles', 'repair_orders', 'repair_items', 'goods_receipts', 'goods_receipt_items', 'stock_movements'];
        foreach ($tables as $table) {
            $this->sheets->syncFromGoogleSheets($table);
        }
        return back()->with('success', 'ซิงค์ข้อมูลสดจาก Google Sheets เรียบร้อยแล้ว!');
    }

    private function getChartData(array $orders): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');

            $dayTotal = 0;
            foreach ($orders as $o) {
                if (str_starts_with($o['created_at'] ?? '', $date) && $o['status'] !== 'cancelled') {
                    $dayTotal += (float)($o['grand_total'] ?? 0);
                }
            }
            $values[] = $dayTotal;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
