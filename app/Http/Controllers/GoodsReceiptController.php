<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\StockService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
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
        $receipts  = array_reverse($this->sheets->readAll('goods_receipts'));
        $suppliers = $this->sheets->readAll('suppliers');
        $supMap    = [];
        foreach ($suppliers as $s) { $supMap[$s['id']] = $s['name']; }
        foreach ($receipts as &$r) {
            $r['supplier_name'] = $supMap[$r['supplier_id']] ?? '-';
        }

        return view('goods-receipts.index', compact('receipts'));
    }

    public function create()
    {
        $suppliers = $this->sheets->readAll('suppliers');
        $parts     = array_filter($this->sheets->readAll('parts'), fn($p) => $p['is_active'] !== '0');
        return view('goods-receipts.create', compact('suppliers', 'parts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'  => 'required',
            'receipt_date' => 'required|date',
            'items'        => 'required|array|min:1',
        ]);

        $grNumber  = $this->sheets->generateDocNumber('GR', 'goods_receipts');
        $grId      = $this->sheets->nextId('goods_receipts');

        $items     = $request->input('items', []);
        $totalCost = array_sum(array_column($items, 'subtotal'));

        // บันทึกเอกสารรับเข้า
        $this->sheets->appendRow('goods_receipts', [
            'id'           => $grId,
            'gr_number'    => $grNumber,
            'supplier_id'  => $request->supplier_id,
            'receipt_date' => $request->receipt_date,
            'total_cost'   => $totalCost,
            'note'         => $request->input('note', ''),
            'created_at'   => now()->toDateTimeString(),
        ]);

        // บันทึกรายการ + เพิ่ม Stock
        foreach ($items as $item) {
            if (empty($item['part_id'])) continue;

            $this->sheets->appendRow('goods_receipt_items', [
                'id'         => $this->sheets->nextId('goods_receipt_items'),
                'gr_id'      => $grId,
                'part_id'    => $item['part_id'],
                'qty'        => $item['qty'],
                'unit_cost'  => $item['unit_cost'],
                'subtotal'   => $item['subtotal'],
                'created_at' => now()->toDateTimeString(),
            ]);

            // เพิ่ม Stock
            $this->stock->addStock($item['part_id'], $item['qty'], $grId, $grNumber);
        }

        return redirect()->route('goods-receipts.index')
            ->with('success', "บันทึกใบรับสินค้า {$grNumber} สำเร็จ");
    }

    public function show(string $id)
    {
        $receipt  = $this->sheets->findWhere('goods_receipts', 'id', $id);
        if (!$receipt) abort(404);

        $items    = $this->sheets->filter('goods_receipt_items', ['gr_id' => $id]);
        $supplier = $this->sheets->findWhere('suppliers', 'id', $receipt['supplier_id']);

        // ผูกชื่ออะไหล่
        foreach ($items as &$item) {
            $part = $this->sheets->findWhere('parts', 'id', $item['part_id']);
            $item['part_name'] = $part['name'] ?? '-';
            $item['part_code'] = $part['part_code'] ?? '-';
        }

        return response()->json(compact('receipt', 'items', 'supplier'));
    }
}
