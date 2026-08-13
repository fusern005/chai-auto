<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    protected GoogleSheetsService $sheets;

    public function __construct(GoogleSheetsService $sheets)
    {
        $this->sheets = $sheets;
    }

    // ============ CUSTOMERS ============
    public function customers()
    {
        $customers = $this->sheets->readAll('customers');
        return view('master-data.customers', compact('customers'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate(['name' => 'required']);
        $this->sheets->appendRow('customers', [
            'id'         => $this->sheets->nextId('customers'),
            'name'       => $request->name,
            'phone'      => $request->input('phone', ''),
            'email'      => $request->input('email', ''),
            'address'    => $request->input('address', ''),
            'is_active'  => '1',
            'created_at' => now()->toDateTimeString(),
        ]);
        return response()->json(['success' => true]);
    }

    public function updateCustomer(Request $request, string $id)
    {
        $customer = $this->sheets->findWhere('customers', 'id', $id);
        if (!$customer) return response()->json(['error' => 'Not found'], 404);
        $customer['name']    = $request->input('name', $customer['name']);
        $customer['phone']   = $request->input('phone', $customer['phone']);
        $customer['email']   = $request->input('email', $customer['email']);
        $customer['address'] = $request->input('address', $customer['address']);
        $this->sheets->updateRow('customers', $id, $customer);
        return response()->json(['success' => true]);
    }

    // ============ VEHICLES ============
    public function vehicles()
    {
        $vehicles  = $this->sheets->readAll('vehicles');
        $customers = $this->sheets->readAll('customers');
        $custMap   = [];
        foreach ($customers as $c) { $custMap[$c['id']] = $c['name']; }
        foreach ($vehicles as &$v) {
            $v['customer_name'] = $custMap[$v['customer_id']] ?? '-';
        }
        return view('master-data.vehicles', compact('vehicles', 'customers'));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate(['customer_id' => 'required', 'license_plate' => 'required']);
        $this->sheets->appendRow('vehicles', [
            'id'            => $this->sheets->nextId('vehicles'),
            'customer_id'   => $request->customer_id,
            'brand'         => $request->input('brand', ''),
            'model'         => $request->input('model', ''),
            'license_plate' => $request->license_plate,
            'year'          => $request->input('year', ''),
            'color'         => $request->input('color', ''),
            'is_active'     => '1',
            'created_at'    => now()->toDateTimeString(),
        ]);
        return response()->json(['success' => true]);
    }

    // ============ SUPPLIERS ============
    public function suppliers()
    {
        $suppliers = $this->sheets->readAll('suppliers');
        return view('master-data.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate(['name' => 'required']);
        $this->sheets->appendRow('suppliers', [
            'id'         => $this->sheets->nextId('suppliers'),
            'name'       => $request->name,
            'contact'    => $request->input('contact', ''),
            'phone'      => $request->input('phone', ''),
            'address'    => $request->input('address', ''),
            'is_active'  => '1',
            'created_at' => now()->toDateTimeString(),
        ]);
        return response()->json(['success' => true]);
    }

    public function updateSupplier(Request $request, string $id)
    {
        $supplier = $this->sheets->findWhere('suppliers', 'id', $id);
        if (!$supplier) return response()->json(['error' => 'Not found'], 404);
        $supplier['name']    = $request->input('name', $supplier['name']);
        $supplier['contact'] = $request->input('contact', $supplier['contact']);
        $supplier['phone']   = $request->input('phone', $supplier['phone']);
        $supplier['address'] = $request->input('address', $supplier['address']);
        $this->sheets->updateRow('suppliers', $id, $supplier);
        return response()->json(['success' => true]);
    }

    // ============ VEHICLES API ============
    public function getVehiclesByCustomer(string $customerId)
    {
        $vehicles = $this->sheets->filter('vehicles', ['customer_id' => $customerId, 'is_active' => '1']);
        return response()->json($vehicles);
    }
}
