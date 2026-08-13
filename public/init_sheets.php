<?php
/**
 * ======================================================
 * Suchai Auto — Google Sheets Initializer
 * รัน 1 ครั้งเพื่อสร้าง Sheet tabs และ Admin user แรก
 * URL: /api-test-450802-1a3c7ce3f924.json (don't expose)
 * รันที่: http://localhost/Suchai_auto/public/init_sheets.php
 * ======================================================
 */

require_once __DIR__ . '/../vendor/autoload.php';

$keyPath       = __DIR__ . '/../API/api-test-450802-1a3c7ce3f924.json';
$spreadsheetId = '171-5miq9QCluZWh1fvLbpgIUKKGp2D2RLJomKS5jTh8';

$client = new Google\Client();
$client->setAuthConfig($keyPath);
$client->addScope(Google\Service\Sheets::SPREADSHEETS);
$service = new Google\Service\Sheets($client);

// ============================================================
// ตาราง → headers
// ============================================================
$tables = [
    'users'               => ['id','name','username','password_hash','role','is_active','created_at'],
    'customers'           => ['id','name','phone','email','address','is_active','created_at'],
    'vehicles'            => ['id','customer_id','brand','model','license_plate','year','color','is_active','created_at'],
    'suppliers'           => ['id','name','contact','phone','address','is_active','created_at'],
    'parts'               => ['id','part_code','name','unit','cost_price','sell_price','stock_qty','min_stock','is_active','created_at','updated_at'],
    'repair_orders'       => ['id','ro_number','customer_id','vehicle_id','status','labor_total','parts_total','discount','vat','grand_total','note','created_at','updated_at'],
    'repair_items'        => ['id','ro_id','type','part_id','description','qty','unit_price','subtotal','created_at'],
    'goods_receipts'      => ['id','gr_number','supplier_id','receipt_date','total_cost','note','created_at'],
    'goods_receipt_items' => ['id','gr_id','part_id','qty','unit_cost','subtotal','created_at'],
    'stock_movements'     => ['id','part_id','movement_type','qty','balance','ref_type','ref_id','ref_number','note','created_at'],
];

// ============================================================
// ดึง Sheet tabs ที่มีอยู่
// ============================================================
$spreadsheet = $service->spreadsheets->get($spreadsheetId);
$existingSheets = [];
foreach ($spreadsheet->getSheets() as $sheet) {
    $existingSheets[] = $sheet->getProperties()->getTitle();
}

$requests = [];

// ============================================================
// สร้าง Sheet tab ที่ยังไม่มี
// ============================================================
foreach ($tables as $sheetName => $headers) {
    if (!in_array($sheetName, $existingSheets)) {
        $requests[] = new Google\Service\Sheets\Request([
            'addSheet' => ['properties' => ['title' => $sheetName]]
        ]);
        echo "📋 จะสร้าง Sheet: {$sheetName}<br>";
    } else {
        echo "✅ มีอยู่แล้ว: {$sheetName}<br>";
    }
}

if (!empty($requests)) {
    $batchRequest = new Google\Service\Sheets\BatchUpdateSpreadsheetRequest(['requests' => $requests]);
    $service->spreadsheets->batchUpdate($spreadsheetId, $batchRequest);
    echo "<br>✅ สร้าง Sheets สำเร็จ<br><br>";
}

// ============================================================
// เขียน Headers ให้แต่ละ Sheet (Row 1)
// ============================================================
foreach ($tables as $sheetName => $headers) {
    $range = "{$sheetName}!A1:" . chr(64 + count($headers)) . "1";
    $body  = new Google\Service\Sheets\ValueRange(['values' => [$headers]]);
    $params = ['valueInputOption' => 'RAW'];
    $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    echo "📝 เขียน Headers: {$sheetName}<br>";
}

// ============================================================
// สร้าง Admin user แรก
// ============================================================
$adminRange = 'users!A2:G2';
$existing   = $service->spreadsheets_values->get($spreadsheetId, $adminRange);

if (empty($existing->getValues())) {
    $passwordHash = password_hash('admin1234', PASSWORD_BCRYPT);
    $body = new Google\Service\Sheets\ValueRange([
        'values' => [['1', 'Administrator', 'admin', $passwordHash, 'admin', '1', date('Y-m-d H:i:s')]]
    ]);
    $service->spreadsheets_values->update($spreadsheetId, $adminRange, $body, ['valueInputOption' => 'RAW']);
    echo "<br>👤 สร้าง Admin User สำเร็จ<br>Username: admin<br>Password: admin1234<br>";
} else {
    echo "<br>👤 Admin User มีอยู่แล้ว<br>";
}

echo "<br><strong style='color:green;'>🎉 ระบบพร้อมใช้งาน!</strong><br>";
echo "<a href='/Suchai_auto/public/login'>→ ไปหน้า Login</a>";
