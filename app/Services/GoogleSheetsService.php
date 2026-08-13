<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    /**
     * In-Memory Runtime Cache สำหรับลด HTTP request ซ้ำซ้อนภายใน Request เดียวกัน (0ms response)
     */
    private static array $runtimeCache = [];

    /**
     * Cache TTL ในหน่วยวินาที (ตั้งค่าผ่าน .env ได้ เช่น GOOGLE_SHEETS_CACHE_TTL=60)
     */
    protected int $cacheTtl;

    public function __construct()
    {
        $this->spreadsheetId = config('google.spreadsheet_id');
        $this->client = new Client();

        // 1. ตรวจสอบว่ามีการใส่ JSON ใน Environment Variable ตรงๆ หรือไม่ (เหมาะสำหรับ Render/Vercel/Cloud Deployment)
        $jsonEnv = env('GOOGLE_SERVICE_ACCOUNT_JSON');
        if (!empty($jsonEnv)) {
            $authData = json_decode($jsonEnv, true) ?? json_decode(base64_decode($jsonEnv), true);
            if ($authData) {
                $this->client->setAuthConfig($authData);
            } else {
                $keyPath = base_path(config('google.service_account_path'));
                $this->client->setAuthConfig($keyPath);
            }
        } else {
            // 2. ถ้าไม่มีใน Env Var ให้ดึงจากไฟล์ตาม Path ที่กำหนด
            $keyPath = base_path(config('google.service_account_path'));
            if (file_exists($keyPath)) {
                $this->client->setAuthConfig($keyPath);
            } else {
                Log::warning("Google Service Account Key File not found at {$keyPath}");
            }
        }

        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->client->addScope('https://www.googleapis.com/auth/drive');

        $this->service  = new Sheets($this->client);
        $this->cacheTtl = (int) env('GOOGLE_SHEETS_CACHE_TTL', 60);
    }


    /**
     * อ่านข้อมูลจาก Sheet (รองรับ Cloud Deployment บน Render/Vercel/Git)
     * ใช้ In-Memory Static Cache (0ms ใน request เดียวกัน) + Standard Laravel Cache
     */
    public function readAll(string $sheetName, bool $forceRefresh = false): array
    {
        // 1. ถ้ามีใน Runtime Memory ของ Request นี้ คืนค่าทันที (0ms)
        if (!$forceRefresh && isset(self::$runtimeCache[$sheetName])) {
            return self::$runtimeCache[$sheetName];
        }

        $cacheKey = "google_sheet_{$sheetName}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // 2. ดึงจาก Standard Laravel Cache หรือดึงจาก Google Sheets API
        $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($sheetName) {
            return $this->fetchDirectFromGoogleSheets($sheetName);
        });

        // 3. บันทึกลง Runtime Memory
        self::$runtimeCache[$sheetName] = $data;

        return $data;
    }

    /**
     * ดึงข้อมูลสดตรงจาก Google Sheets API
     */
    protected function fetchDirectFromGoogleSheets(string $sheetName): array
    {
        try {
            $range = "{$sheetName}!A:ZZ";
            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            $values = $response->getValues() ?? [];

            if (empty($values)) {
                return [];
            }

            $headers = array_shift($values);
            $result  = [];

            foreach ($values as $row) {
                if (empty(array_filter($row))) continue;
                $record = [];
                foreach ($headers as $i => $header) {
                    $record[$header] = $row[$i] ?? '';
                }
                $result[] = $record;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error fetching Google Sheet {$sheetName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ค้นหาแถวตาม column = value
     */
    public function findWhere(string $sheetName, string $column, $value): ?array
    {
        $all = $this->readAll($sheetName);
        foreach ($all as $row) {
            if (isset($row[$column]) && (string)$row[$column] === (string)$value) {
                return $row;
            }
        }
        return null;
    }

    /**
     * กรองหลาย condition
     */
    public function filter(string $sheetName, array $conditions): array
    {
        $all = $this->readAll($sheetName);
        return array_values(array_filter($all, function ($row) use ($conditions) {
            foreach ($conditions as $col => $val) {
                if (!isset($row[$col]) || (string)$row[$col] !== (string)$val) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * เพิ่มแถวใหม่ (ใช้ 1 API Call และอัปเดต In-Memory/Laravel Cache ทันที)
     */
    public function appendRow(string $sheetName, array $data): bool
    {
        $currentRows = $this->readAll($sheetName);
        
        // หา Headers จากข้อมูลที่มีอยู่แล้วใน Memory (ไม่ต้องยิง API หา header เพิ่ม)
        $headers = [];
        if (!empty($currentRows)) {
            $headers = array_keys($currentRows[0]);
        } else {
            $headers = array_keys($data);
            $this->setHeaders($sheetName, $headers);
        }

        $row = [];
        foreach ($headers as $header) {
            $row[] = $data[$header] ?? '';
        }

        // ยิง API บันทึกเพียง 1 ครั้งเท่านั้น
        try {
            $body   = new ValueRange(['values' => [$row]]);
            $params = ['valueInputOption' => 'RAW'];

            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                $sheetName,
                $body,
                $params
            );

            // อัปเดต Cache ทันทีเพื่อให้แสดงผลทันทีโดยไม่ต้องยิงดึงใหม่
            $currentRows[] = $data;
            $this->updateMemoryAndCache($sheetName, $currentRows);

            return true;
        } catch (\Exception $e) {
            Log::error("Error appending row to Google Sheets {$sheetName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * อัปเดตแถว โดยค้นหาจาก id (ใช้ 1 API Call และอัปเดต Cache ทันที)
     */
    public function updateRow(string $sheetName, string $id, array $data): bool
    {
        $currentRows = $this->readAll($sheetName);
        $foundIndex  = null;

        foreach ($currentRows as $i => $row) {
            if (isset($row['id']) && (string)$row['id'] === (string)$id) {
                $foundIndex = $i;
                break;
            }
        }

        if ($foundIndex === null) {
            return false;
        }

        // อัปเดตข้อมูลใน array
        $updatedRow = array_merge($currentRows[$foundIndex], $data);
        $currentRows[$foundIndex] = $updatedRow;

        // คำนวณตำแหน่งแถวใน Google Sheet (Row 1 = Headers, Index 0 = Row 2)
        $sheetRowIndex = $foundIndex + 2;

        $headers = array_keys($currentRows[0] ?? $data);
        $rowValues = [];
        foreach ($headers as $header) {
            $rowValues[] = $updatedRow[$header] ?? '';
        }

        try {
            $updateRange = "{$sheetName}!A{$sheetRowIndex}";
            $body   = new ValueRange(['values' => [$rowValues]]);
            $params = ['valueInputOption' => 'RAW'];

            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $updateRange,
                $body,
                $params
            );

            // อัปเดต Cache ทันที
            $this->updateMemoryAndCache($sheetName, $currentRows);

            return true;
        } catch (\Exception $e) {
            Log::error("Error updating row in Google Sheets {$sheetName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ตั้ง headers ของ Sheet (Row 1)
     */
    public function setHeaders(string $sheetName, array $headers): void
    {
        try {
            $body   = new ValueRange(['values' => [$headers]]);
            $params = ['valueInputOption' => 'RAW'];
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                "{$sheetName}!A1",
                $body,
                $params
            );
        } catch (\Exception $e) {
            Log::error("Error setting headers {$sheetName}: " . $e->getMessage());
        }
    }

    /**
     * อัปเดตทั้ง Runtime Memory และ Laravel Cache
     */
    protected function updateMemoryAndCache(string $sheetName, array $data): void
    {
        self::$runtimeCache[$sheetName] = $data;
        $cacheKey = "google_sheet_{$sheetName}";
        Cache::put($cacheKey, $data, $this->cacheTtl);
    }

    /**
     * ล้าง Cache ของ Sheet (ทั้ง Memory และ Laravel Cache)
     */
    public function clearCache(?string $sheetName = null): void
    {
        if ($sheetName) {
            unset(self::$runtimeCache[$sheetName]);
            Cache::forget("google_sheet_{$sheetName}");
        } else {
            self::$runtimeCache = [];
            Cache::flush();
        }
    }

    /**
     * บังคับดึงข้อมูลสดจาก Google Sheets และอัปเดต Cache ทุกตาราง
     */
    public function syncFromGoogleSheets(string $sheetName): array
    {
        return $this->readAll($sheetName, true);
    }

    /**
     * สร้าง ID ใหม่ (auto increment - รวดเร็ว 0ms จาก memory)
     */
    public function nextId(string $sheetName): int
    {
        $all = $this->readAll($sheetName);
        if (empty($all)) return 1;

        $ids = array_column($all, 'id');
        $ids = array_filter($ids, 'is_numeric');
        return empty($ids) ? 1 : (int)max($ids) + 1;
    }

    /**
     * สร้างเลขที่เอกสาร เช่น RO-20240101-0001
     */
    public function generateDocNumber(string $prefix, string $sheetName): string
    {
        $date = now()->format('Ymd');
        $all  = $this->readAll($sheetName);
        $prefix_date = "{$prefix}-{$date}-";

        $maxNum = 0;
        foreach ($all as $row) {
            $field = $prefix === 'RO' ? 'ro_number' : 'gr_number';
            if (isset($row[$field]) && str_starts_with($row[$field], $prefix_date)) {
                $num = (int)substr($row[$field], -4);
                if ($num > $maxNum) $maxNum = $num;
            }
        }

        return $prefix_date . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);
    }
}
