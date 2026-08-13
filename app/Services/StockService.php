<?php

namespace App\Services;

use App\Services\GoogleSheetsService;

class StockService
{
    protected GoogleSheetsService $sheets;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
    }

    /**
     * ตรวจสอบว่า Stock เพียงพอหรือไม่
     */
    public function checkStock(string $partId, float $qty): bool
    {
        $part = $this->sheets->findWhere('parts', 'id', $partId);
        if (!$part) return false;
        return (float)($part['stock_qty'] ?? 0) >= $qty;
    }

    /**
     * ดึงจำนวน Stock ปัจจุบัน
     */
    public function getStock(string $partId): float
    {
        $part = $this->sheets->findWhere('parts', 'id', $partId);
        return (float)($part['stock_qty'] ?? 0);
    }

    /**
     * ตัด Stock เมื่อบันทึกใบซ่อม
     */
    public function deductStock(string $partId, float $qty, string $roId, string $roNumber): bool
    {
        $part = $this->sheets->findWhere('parts', 'id', $partId);
        if (!$part) return false;

        $currentStock = (float)($part['stock_qty'] ?? 0);
        if ($currentStock < $qty) return false;

        $newStock = $currentStock - $qty;

        // อัปเดต stock ในตาราง parts
        $part['stock_qty'] = $newStock;
        $this->sheets->updateRow('parts', $partId, $part);

        // บันทึก Stock Movement (ออก)
        $this->createMovement([
            'part_id'       => $partId,
            'movement_type' => 'out',
            'qty'           => $qty,
            'balance'       => $newStock,
            'ref_type'      => 'repair_order',
            'ref_id'        => $roId,
            'ref_number'    => $roNumber,
            'note'          => "ตัดจากใบซ่อม {$roNumber}",
        ]);

        return true;
    }

    /**
     * เพิ่ม Stock เมื่อรับอะไหล่เข้า
     */
    public function addStock(string $partId, float $qty, string $grId, string $grNumber): bool
    {
        $part = $this->sheets->findWhere('parts', 'id', $partId);
        if (!$part) return false;

        $currentStock = (float)($part['stock_qty'] ?? 0);
        $newStock = $currentStock + $qty;

        // อัปเดต stock ในตาราง parts
        $part['stock_qty'] = $newStock;
        $this->sheets->updateRow('parts', $partId, $part);

        // บันทึก Stock Movement (เข้า)
        $this->createMovement([
            'part_id'       => $partId,
            'movement_type' => 'in',
            'qty'           => $qty,
            'balance'       => $newStock,
            'ref_type'      => 'goods_receipt',
            'ref_id'        => $grId,
            'ref_number'    => $grNumber,
            'note'          => "รับเข้าจากใบรับสินค้า {$grNumber}",
        ]);

        return true;
    }

    /**
     * คืน Stock เมื่อยกเลิกใบซ่อม
     */
    public function returnStock(string $roId, string $roNumber): bool
    {
        // หารายการอะไหล่ทั้งหมดในใบซ่อมนั้น
        $items = $this->sheets->filter('repair_items', [
            'ro_id' => $roId,
            'type'  => 'part',
        ]);

        foreach ($items as $item) {
            $part = $this->sheets->findWhere('parts', 'id', $item['part_id']);
            if (!$part) continue;

            $currentStock = (float)($part['stock_qty'] ?? 0);
            $returnQty    = (float)($item['qty'] ?? 0);
            $newStock     = $currentStock + $returnQty;

            $part['stock_qty'] = $newStock;
            $this->sheets->updateRow('parts', $item['part_id'], $part);

            // บันทึก Movement คืน
            $this->createMovement([
                'part_id'       => $item['part_id'],
                'movement_type' => 'return',
                'qty'           => $returnQty,
                'balance'       => $newStock,
                'ref_type'      => 'repair_order',
                'ref_id'        => $roId,
                'ref_number'    => $roNumber,
                'note'          => "คืน Stock จากการยกเลิกใบซ่อม {$roNumber}",
            ]);
        }

        return true;
    }

    /**
     * บันทึก Stock Movement
     */
    private function createMovement(array $data): void
    {
        $this->sheets->appendRow('stock_movements', array_merge([
            'id'         => $this->sheets->nextId('stock_movements'),
            'created_at' => now()->toDateTimeString(),
        ], $data));
    }

    /**
     * ดึงรายการอะไหล่ใกล้หมด (stock <= min_stock)
     */
    public function getLowStockParts(): array
    {
        $parts = $this->sheets->readAll('parts');
        return array_values(array_filter($parts, function ($p) {
            return $p['is_active'] !== '0'
                && (float)($p['stock_qty'] ?? 0) <= (float)($p['min_stock'] ?? 0);
        }));
    }
}
