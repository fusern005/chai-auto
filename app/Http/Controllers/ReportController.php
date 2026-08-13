<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected GoogleSheetsService $sheets;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
    }

    public function index(Request $request)
    {
        $type      = $request->input('type', 'repair_orders');
        $dateFrom  = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo    = $request->input('date_to', now()->toDateString());

        $data = $this->getReportData($type, $dateFrom, $dateTo);

        return view('reports.index', compact('type', 'dateFrom', 'dateTo', 'data'));
    }

    private function getReportData(string $type, string $from, string $to): array
    {
        switch ($type) {
            case 'repair_orders':
                $orders = $this->sheets->readAll('repair_orders');
                $filtered = array_filter($orders, fn($o) =>
                    substr($o['created_at'], 0, 10) >= $from &&
                    substr($o['created_at'], 0, 10) <= $to &&
                    $o['status'] !== 'cancelled'
                );
                $customers = $this->sheets->readAll('customers');
                $custMap = [];
                foreach ($customers as $c) { $custMap[$c['id']] = $c['name']; }
                foreach ($filtered as &$o) {
                    $o['customer_name'] = $custMap[$o['customer_id']] ?? '-';
                }
                return ['rows' => array_values($filtered), 'total' => array_sum(array_column($filtered, 'grand_total'))];

            case 'parts_usage':
                $items = $this->sheets->readAll('repair_items');
                $orders = $this->sheets->readAll('repair_orders');
                $orderMap = [];
                foreach ($orders as $o) { $orderMap[$o['id']] = $o; }

                $filtered = array_filter($items, function ($item) use ($orderMap, $from, $to) {
                    if ($item['type'] !== 'part') return false;
                    $order = $orderMap[$item['ro_id']] ?? null;
                    if (!$order || $order['status'] === 'cancelled') return false;
                    return substr($order['created_at'], 0, 10) >= $from &&
                           substr($order['created_at'], 0, 10) <= $to;
                });

                $parts = $this->sheets->readAll('parts');
                $partMap = [];
                $partCostMap = [];
                foreach ($parts as $p) {
                    $partMap[$p['id']] = $p['name'];
                    $partCostMap[$p['id']] = (float)($p['cost_price'] ?? 0);
                }
                foreach ($filtered as &$item) {
                    $item['part_name']  = $partMap[$item['part_id']] ?? '-';
                    $item['cost_price'] = $partCostMap[$item['part_id']] ?? 0;
                    $item['total_cost'] = (float)$item['qty'] * $item['cost_price'];
                    $item['profit']     = (float)($item['subtotal'] ?? 0) - $item['total_cost'];
                }

                return ['rows' => array_values($filtered), 'total' => array_sum(array_column($filtered, 'subtotal'))];

            case 'stock_balance':
                $parts = $this->sheets->readAll('parts');
                return ['rows' => $parts, 'total' => null];

            case 'low_stock':
                $parts = $this->sheets->readAll('parts');
                $low = array_filter($parts, fn($p) =>
                    $p['is_active'] !== '0' &&
                    (float)($p['stock_qty'] ?? 0) <= (float)($p['min_stock'] ?? 0)
                );
                return ['rows' => array_values($low), 'total' => null];

            case 'goods_receipts':
                $receipts = $this->sheets->readAll('goods_receipts');
                $filtered = array_filter($receipts, fn($r) =>
                    substr($r['created_at'], 0, 10) >= $from &&
                    substr($r['created_at'], 0, 10) <= $to
                );
                $suppliers = $this->sheets->readAll('suppliers');
                $supMap = [];
                foreach ($suppliers as $s) { $supMap[$s['id']] = $s['name']; }
                foreach ($filtered as &$r) {
                    $r['supplier_name'] = $supMap[$r['supplier_id']] ?? '-';
                }
                return ['rows' => array_values($filtered), 'total' => array_sum(array_column($filtered, 'total_cost'))];
            default:
                return ['rows' => [], 'total' => 0];
        }
    }

    public function profit(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $orders        = $this->sheets->readAll('repair_orders');
        $repairItems   = $this->sheets->readAll('repair_items');
        $parts         = $this->sheets->readAll('parts');
        $customers     = $this->sheets->readAll('customers');
        $goodsReceipts = $this->sheets->readAll('goods_receipts');

        $partCostMap = [];
        foreach ($parts as $p) {
            $partCostMap[$p['id']] = (float)($p['cost_price'] ?? 0);
        }

        $custMap = [];
        foreach ($customers as $c) {
            $custMap[$c['id']] = $c['name'];
        }

        $monthlyOrders = array_values(array_filter($orders, function ($o) use ($month) {
            return str_starts_with($o['created_at'] ?? '', $month) && ($o['status'] ?? '') !== 'cancelled';
        }));

        $itemsByRo = [];
        foreach ($repairItems as $item) {
            $itemsByRo[$item['ro_id']][] = $item;
        }

        $totalLabor        = 0;
        $totalPartsRevenue = 0;
        $totalDiscount     = 0;
        $totalVat          = 0;
        $totalRevenue      = 0;
        $totalPartCost     = 0;

        $orderBreakdown = [];

        foreach ($monthlyOrders as $o) {
            $roId    = $o['id'];
            $roItems = $itemsByRo[$roId] ?? [];
            
            $roPartCost = 0;
            foreach ($roItems as $item) {
                if (($item['type'] ?? '') === 'part') {
                    $partId   = $item['part_id'] ?? '';
                    $costUnit = $partCostMap[$partId] ?? (float)($item['unit_price'] ?? 0);
                    $roPartCost += (float)($item['qty'] ?? 0) * $costUnit;
                }
            }

            $labor    = (float)($o['labor_total'] ?? 0);
            $partsRev = (float)($o['parts_total'] ?? 0);
            $grand    = (float)($o['grand_total'] ?? 0);
            $profit   = $grand - $roPartCost;
            $margin   = $grand > 0 ? ($profit / $grand) * 100 : 0;

            $totalLabor        += $labor;
            $totalPartsRevenue += $partsRev;
            $totalDiscount     += (float)($o['discount'] ?? 0);
            $totalVat          += (float)($o['vat'] ?? 0);
            $totalRevenue      += $grand;
            $totalPartCost     += $roPartCost;

            $orderBreakdown[] = [
                'ro_number'     => $o['ro_number'] ?? '',
                'date'          => substr($o['created_at'] ?? '', 0, 10),
                'customer_name' => $custMap[$o['customer_id'] ?? ''] ?? '-',
                'labor'         => $labor,
                'parts_revenue' => $partsRev,
                'parts_cost'    => $roPartCost,
                'grand_total'   => $grand,
                'profit'        => $profit,
                'margin'        => $margin,
            ];
        }

        // สินค้าสั่งซื้อเข้าคลังในเดือนนี้
        $monthlyReceipts = array_filter($goodsReceipts, function ($r) use ($month) {
            return str_starts_with($r['created_at'] ?? '', $month);
        });
        $totalPurchases = array_sum(array_column($monthlyReceipts, 'total_cost'));

        $netGrossProfit = $totalRevenue - $totalPartCost;
        $overallMargin  = $totalRevenue > 0 ? ($netGrossProfit / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue'       => $totalRevenue,
            'total_labor'         => $totalLabor,
            'total_parts_revenue' => $totalPartsRevenue,
            'total_discount'      => $totalDiscount,
            'total_vat'           => $totalVat,
            'total_part_cost'     => $totalPartCost,
            'total_purchases'     => $totalPurchases,
            'net_gross_profit'    => $netGrossProfit,
            'overall_margin'      => $overallMargin,
        ];

        return view('reports.profit', compact('month', 'summary', 'orderBreakdown'));
    }

    public function receiptSummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $parts         = $this->sheets->readAll('parts');
        $suppliers     = $this->sheets->readAll('suppliers');
        $goodsReceipts = $this->sheets->readAll('goods_receipts');
        $grItems       = $this->sheets->readAll('goods_receipt_items');
        $movements     = $this->sheets->readAll('stock_movements');
        $repairOrders  = $this->sheets->readAll('repair_orders');

        $partMap = [];
        foreach ($parts as $p) {
            $partMap[$p['id']] = [
                'name'       => $p['name']       ?? '-',
                'code'       => $p['part_code']  ?? '-',
                'unit'       => $p['unit']        ?? 'ชิ้น',
                'cost_price' => (float)($p['cost_price'] ?? 0),
                'stock_qty'  => (float)($p['stock_qty']  ?? 0), // current stock fallback
            ];
        }

        $supMap = [];
        foreach ($suppliers as $s) { $supMap[$s['id']] = $s['name']; }

        $grMap = [];
        foreach ($goodsReceipts as $r) { $grMap[$r['id']] = $r; }

        $roMap = [];
        foreach ($repairOrders as $o) { $roMap[$o['id']] = $o; }

        // ─── Compute end-of-month balance per part from stock_movements ───
        // We want the LAST movement's `balance` field for each part UP TO end of the selected month.
        // Sort movements by date ASC so we can overwrite with latest per part.
        $endOfMonth = $month . '-31'; // generous upper bound

        $balanceByPart = []; // part_id => balance at end of selected month
        $allMovementsSorted = $movements;
        usort($allMovementsSorted, fn($a, $b) => strcmp($a['created_at'] ?? '', $b['created_at'] ?? ''));

        foreach ($allMovementsSorted as $m) {
            $mDate = substr($m['created_at'] ?? '', 0, 7); // YYYY-MM
            if ($mDate > $month) continue; // only up to end of selected month
            $pid = $m['part_id'] ?? '';
            if ($pid === '') continue;
            if (isset($m['balance']) && $m['balance'] !== '') {
                $balanceByPart[$pid] = (float)$m['balance'];
            }
        }

        // ─── Build per-part summary ────────────────────────────────
        $partSummary = []; // [part_id => [...]]

        // รับเข้าในเดือนนี้ (goods_receipt_items)
        foreach ($grItems as $item) {
            $grId = $item['gr_id'] ?? '';
            $gr   = $grMap[$grId] ?? null;
            $date = $gr['receipt_date'] ?? substr($item['created_at'] ?? '', 0, 10);
            if (!str_starts_with($date, $month)) continue;

            $pid   = $item['part_id'] ?? '';
            $qty   = (float)($item['qty']      ?? 0);
            $cost  = (float)($item['unit_cost'] ?? 0);
            $sub   = (float)($item['subtotal']  ?? ($qty * $cost));

            if (!isset($partSummary[$pid])) {
                $pi = $partMap[$pid] ?? ['name'=>'-','code'=>'-','unit'=>'ชิ้น','cost_price'=>0,'stock_qty'=>0];
                $partSummary[$pid] = [
                    'part_id'    => $pid,
                    'part_code'  => $pi['code'],
                    'part_name'  => $pi['name'],
                    'unit'       => $pi['unit'],
                    'cost_price' => $pi['cost_price'],
                    'stock_qty'  => $pi['stock_qty'],
                    'in_qty'     => 0,
                    'in_amount'  => 0,
                    'out_qty'    => 0,
                    'out_amount' => 0,
                    'out_details'=> [],
                    'in_receipts'=> [],
                ];
            }

            $partSummary[$pid]['in_qty']    += $qty;
            $partSummary[$pid]['in_amount'] += $sub;
            $partSummary[$pid]['in_receipts'][] = [
                'date'     => $date,
                'gr_num'   => $gr['gr_number'] ?? '-',
                'supplier' => $supMap[$gr['supplier_id'] ?? ''] ?? '-',
                'qty'      => $qty,
                'unit_cost'=> $cost,
                'subtotal' => $sub,
            ];
        }

        // เบิกออกในเดือนนี้ (stock_movements type=out)
        foreach ($movements as $m) {
            if (($m['movement_type'] ?? '') !== 'out') continue;
            $date = substr($m['created_at'] ?? '', 0, 10);
            if (!str_starts_with($date, $month)) continue;

            $pid  = $m['part_id'] ?? '';
            $qty  = (float)($m['qty'] ?? 0);
            $pi   = $partMap[$pid] ?? ['name'=>'-','code'=>'-','unit'=>'ชิ้น','cost_price'=>0,'stock_qty'=>0];
            $cost = $pi['cost_price'];
            $sub  = $qty * $cost;

            if (!isset($partSummary[$pid])) {
                $partSummary[$pid] = [
                    'part_id'    => $pid,
                    'part_code'  => $pi['code'],
                    'part_name'  => $pi['name'],
                    'unit'       => $pi['unit'],
                    'cost_price' => $cost,
                    'stock_qty'  => $pi['stock_qty'],
                    'in_qty'     => 0,
                    'in_amount'  => 0,
                    'out_qty'    => 0,
                    'out_amount' => 0,
                    'out_details'=> [],
                    'in_receipts'=> [],
                ];
            }

            $partSummary[$pid]['out_qty']    += $qty;
            $partSummary[$pid]['out_amount'] += $sub;

            $refId  = $m['ref_id']     ?? '';
            $refNum = $m['ref_number'] ?? '-';
            $partSummary[$pid]['out_details'][] = [
                'date'      => substr($m['created_at'] ?? '', 0, 16),
                'ro_number' => $refNum,
                'qty'       => $qty,
                'unit_cost' => $cost,
                'subtotal'  => $sub,
                'ro_id'     => $refId,
            ];
        }

        // ─── Balance = รับเข้า - แบกออก ─────────────────────────────
        foreach ($partSummary as $pid => &$row) {
            $row['balance_month'] = $row['in_qty'] - $row['out_qty'];
        }
        unset($row);

        $rows = array_values($partSummary);
        usort($rows, fn($a, $b) => strcmp($a['part_code'], $b['part_code']));

        $totalInQty     = array_sum(array_column($rows, 'in_qty'));
        $totalInAmount  = array_sum(array_column($rows, 'in_amount'));
        $totalOutQty    = array_sum(array_column($rows, 'out_qty'));
        $totalOutAmount = array_sum(array_column($rows, 'out_amount'));

        $stats = [
            'total_in_qty'     => $totalInQty,
            'total_in_amount'  => $totalInAmount,
            'total_out_qty'    => $totalOutQty,
            'total_out_amount' => $totalOutAmount,
            'total_parts'      => count($rows),
        ];

        return view('reports.receipt_summary', compact('month', 'rows', 'stats'));
    }
}
