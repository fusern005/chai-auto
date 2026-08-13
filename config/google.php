<?php

return [
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID'),
    'service_account_path' => env('GOOGLE_SERVICE_ACCOUNT_PATH'),

    'sheets' => [
        'users'               => 'users',
        'customers'           => 'customers',
        'vehicles'            => 'vehicles',
        'suppliers'           => 'suppliers',
        'parts'               => 'parts',
        'repair_orders'       => 'repair_orders',
        'repair_items'        => 'repair_items',
        'goods_receipts'      => 'goods_receipts',
        'goods_receipt_items' => 'goods_receipt_items',
        'stock_movements'     => 'stock_movements',
    ],
];
