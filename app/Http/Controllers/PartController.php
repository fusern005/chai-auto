<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use App\Services\StockService;
use Illuminate\Http\Request;

class PartController extends Controller
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
        $parts = $this->sheets->readAll('parts');
        return view('parts.index', compact('parts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_code'  => 'required',
            'name'       => 'required',
            'unit'       => 'required',
            'sell_price' => 'required|numeric',
        ]);

        // ตรวจ part_code ซ้ำ
        $existing = $this->sheets->findWhere('parts', 'part_code', $request->part_code);
        if ($existing) {
            return response()->json(['error' => 'รหัสอะไหล่ซ้ำ'], 422);
        }

        $this->sheets->appendRow('parts', [
            'id'         => $this->sheets->nextId('parts'),
            'part_code'  => $request->part_code,
            'name'       => $request->name,
            'unit'       => $request->unit,
            'cost_price' => $request->input('cost_price', 0),
            'sell_price' => $request->sell_price,
            'stock_qty'  => $request->input('initial_stock', 0),
            'min_stock'  => $request->input('min_stock', 5),
            'is_active'  => '1',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $part = $this->sheets->findWhere('parts', 'id', $id);
        if (!$part) return response()->json(['error' => 'Not found'], 404);

        $part['name']       = $request->input('name', $part['name']);
        $part['unit']       = $request->input('unit', $part['unit']);
        $part['cost_price'] = $request->input('cost_price', $part['cost_price']);
        $part['sell_price'] = $request->input('sell_price', $part['sell_price']);
        $part['min_stock']  = $request->input('min_stock', $part['min_stock']);
        $part['updated_at'] = now()->toDateTimeString();

        $this->sheets->updateRow('parts', $id, $part);

        return response()->json(['success' => true]);
    }

    public function toggleActive(string $id)
    {
        $part = $this->sheets->findWhere('parts', 'id', $id);
        if (!$part) return response()->json(['error' => 'Not found'], 404);

        $part['is_active'] = $part['is_active'] === '1' ? '0' : '1';
        $part['updated_at'] = now()->toDateTimeString();
        $this->sheets->updateRow('parts', $id, $part);

        return response()->json(['success' => true, 'is_active' => $part['is_active']]);
    }

    public function movements(string $id)
    {
        $part      = $this->sheets->findWhere('parts', 'id', $id);
        $movements = $this->sheets->filter('stock_movements', ['part_id' => $id]);
        $movements = array_reverse($movements);

        return response()->json(['part' => $part, 'movements' => $movements]);
    }
}
