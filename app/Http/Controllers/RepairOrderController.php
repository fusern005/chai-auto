<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\StockService;
use Illuminate\Http\Request;

class RepairOrderController extends Controller
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
        $orders    = $this->sheets->readAll('repair_orders');
        $customers = $this->sheets->readAll('customers');

        // ผูกชื่อลูกค้ากับใบซ่อม
        $custMap = [];
        foreach ($customers as $c) { $custMap[$c['id']] = $c['name']; }

        foreach ($orders as &$o) {
            $o['customer_name'] = $custMap[$o['customer_id']] ?? '-';
        }

        $orders = array_reverse($orders);
        return view('repair-orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = array_filter($this->sheets->readAll('customers'), fn($c) => $c['is_active'] !== '0');
        $vehicles  = $this->sheets->readAll('vehicles');
        $parts     = array_filter($this->sheets->readAll('parts'), fn($p) => $p['is_active'] !== '0');

        return view('repair-orders.create', compact('customers', 'vehicles', 'parts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'vehicle_id'  => 'required',
        ]);

        // ตรวจสอบ stock ก่อนบันทึก
        $partItems = $request->input('part_items', []);
        foreach ($partItems as $item) {
            if (!$this->stock->checkStock($item['part_id'], $item['qty'])) {
                $part = $this->sheets->findWhere('parts', 'id', $item['part_id']);
                return back()->withErrors([
                    'stock' => "อะไหล่ '{$part['name']}' Stock ไม่เพียงพอ (คงเหลือ: {$part['stock_qty']} {$part['unit']})"
                ])->withInput();
            }
        }

        // สร้างเลขที่ใบซ่อม
        $roNumber = $this->sheets->generateDocNumber('RO', 'repair_orders');
        $roId     = $this->sheets->nextId('repair_orders');

        // คำนวณยอดเงิน
        $laborItems = $request->input('labor_items', []);
        $laborTotal = array_sum(array_column($laborItems, 'subtotal'));
        $partsTotal = array_sum(array_column($partItems, 'subtotal'));
        $subtotal   = $laborTotal + $partsTotal;
        $discount   = (float)$request->input('discount', 0);
        $vatRate    = $request->input('use_vat') ? 0.07 : 0;
        $afterDiscount = $subtotal - $discount;
        $vat        = round($afterDiscount * $vatRate, 2);
        $grandTotal = $afterDiscount + $vat;

        // บันทึกใบซ่อม
        $this->sheets->appendRow('repair_orders', [
            'id'          => $roId,
            'ro_number'   => $roNumber,
            'customer_id' => $request->customer_id,
            'vehicle_id'  => $request->vehicle_id,
            'status'      => 'in_progress',
            'labor_total' => $laborTotal,
            'parts_total' => $partsTotal,
            'discount'    => $discount,
            'vat'         => $vat,
            'grand_total' => $grandTotal,
            'note'        => $request->input('note', ''),
            'created_at'  => now()->toDateTimeString(),
            'updated_at'  => now()->toDateTimeString(),
        ]);

        // บันทึกรายการค่าแรง
        foreach ($laborItems as $item) {
            if (empty($item['description'])) continue;
            $this->sheets->appendRow('repair_items', [
                'id'         => $this->sheets->nextId('repair_items'),
                'ro_id'      => $roId,
                'type'       => 'labor',
                'part_id'    => '',
                'description'=> $item['description'],
                'qty'        => $item['qty'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'subtotal'   => $item['subtotal'] ?? 0,
                'created_at' => now()->toDateTimeString(),
            ]);
        }

        // บันทึกรายการอะไหล่ + ตัด Stock
        foreach ($partItems as $item) {
            if (empty($item['part_id'])) continue;
            $part = $this->sheets->findWhere('parts', 'id', $item['part_id']);
            $this->sheets->appendRow('repair_items', [
                'id'         => $this->sheets->nextId('repair_items'),
                'ro_id'      => $roId,
                'type'       => 'part',
                'part_id'    => $item['part_id'],
                'description'=> $part['name'] ?? $item['description'],
                'qty'        => $item['qty'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['subtotal'],
                'created_at' => now()->toDateTimeString(),
            ]);

            // ตัด Stock
            $this->stock->deductStock($item['part_id'], $item['qty'], $roId, $roNumber);
        }

        return redirect()->route('repair-orders.show', $roId)
            ->with('success', "สร้างใบซ่อม {$roNumber} สำเร็จ");
    }

    public function show(string $id)
    {
        $order = $this->sheets->findWhere('repair_orders', 'id', $id);
        if (!$order) abort(404);

        $items    = $this->sheets->filter('repair_items', ['ro_id' => $id]);
        $customer = $this->sheets->findWhere('customers', 'id', $order['customer_id']);
        $vehicle  = $this->sheets->findWhere('vehicles', 'id', $order['vehicle_id']);
        $parts    = $this->sheets->readAll('parts');

        $partCostMap = [];
        foreach ($parts as $p) {
            $partCostMap[$p['id']] = (float)($p['cost_price'] ?? 0);
        }

        $partsCost = 0;
        foreach ($items as &$item) {
            if (($item['type'] ?? '') === 'part') {
                $costUnit = $partCostMap[$item['part_id'] ?? ''] ?? (float)($item['unit_price'] ?? 0);
                $item['cost_price'] = $costUnit;
                $item['total_cost'] = $costUnit * (float)($item['qty'] ?? 0);
                $partsCost += $item['total_cost'];
            }
        }
        $order['parts_cost']       = $partsCost;
        $order['estimated_profit'] = (float)($order['grand_total'] ?? 0) - $partsCost;

        return view('repair-orders.show', compact('order', 'items', 'customer', 'vehicle'));
    }

    public function updateStatus(Request $request, string $id)
    {
        $order = $this->sheets->findWhere('repair_orders', 'id', $id);
        if (!$order) return response()->json(['error' => 'Not found'], 404);

        $newStatus = $request->input('status');
        $allowed = ['pending', 'in_progress', 'done', 'cancelled'];
        if (!in_array($newStatus, $allowed)) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        // ถ้ายกเลิก → คืน stock
        if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
            $this->stock->returnStock($id, $order['ro_number']);
        }

        $order['status']     = $newStatus;
        $order['updated_at'] = now()->toDateTimeString();
        $this->sheets->updateRow('repair_orders', $id, $order);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }
}
