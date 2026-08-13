<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    protected GoogleSheetsService $sheets;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
    }

    public function index(Request $request)
    {
        $movements = $this->sheets->readAll('stock_movements');
        $parts     = $this->sheets->readAll('parts');

        $partMap = [];
        foreach ($parts as $p) {
            $partMap[$p['id']] = ['name' => $p['name'], 'code' => $p['part_code']];
        }

        foreach ($movements as &$m) {
            $m['part_name'] = $partMap[$m['part_id']]['name'] ?? '-';
            $m['part_code'] = $partMap[$m['part_id']]['code'] ?? '-';
        }

        // Filter
        if ($request->filled('part_id')) {
            $movements = array_filter($movements, fn($m) => $m['part_id'] === $request->part_id);
        }
        if ($request->filled('type')) {
            $movements = array_filter($movements, fn($m) => $m['movement_type'] === $request->type);
        }
        if ($request->filled('date_from')) {
            $movements = array_filter($movements, fn($m) => substr($m['created_at'], 0, 10) >= $request->date_from);
        }
        if ($request->filled('date_to')) {
            $movements = array_filter($movements, fn($m) => substr($m['created_at'], 0, 10) <= $request->date_to);
        }

        $movements = array_reverse(array_values($movements));
        return view('stock-movements.index', compact('movements', 'parts'));
    }
}
