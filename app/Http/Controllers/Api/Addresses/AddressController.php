<?php

namespace App\Http\Controllers\Api\Addresses;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    // =========================================================
    // GET ALL ADDRESSES
    // =========================================================

    public function index(Request $request)
    {
        $query = Address::with('students');

        //search
        if($request->filled('search')){
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('municipality', 'like', "%{$search}%")
                  ->orWhere('ward', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('street', 'like', "%{$search}%");

            });
        }
        // PROVINCE FILTER
if (
    $request->filled('province_filter') &&
    $request->province_filter !== 'all'
) {

    $query->where(
        'province',
        $request->province_filter
    );
}
            
        $perPage = min(max((int) $request->input('per_page', 5), 1), 100);

        $addresses = $query
            ->latest()
            ->paginate($perPage);

        return response()->json($addresses);
    }


    // =========================================================
    // CREATE ADDRESS
    // =========================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'ward' => 'required|string|max:50',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
        ]);

        $address = Address::create($validated);

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => $address,
        ], 201);
    }


    // =========================================================
    // GET SINGLE ADDRESS
    // =========================================================

    public function show(Address $address)
    {
        $address->load('students');

        return response()->json($address);
    }


    // =========================================================
    // UPDATE ADDRESS
    // =========================================================

    public function update(Request $request, Address $address)
    {
        $validated = $request->validate([
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'ward' => 'required|string|max:50',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
        ]);

        $address->update($validated);

        return response()->json([
            'message' => 'Address updated successfully.',
            'address' => $address,
        ]);
    }


    // =========================================================
    // DELETE ADDRESS
    // =========================================================

    public function destroy(Address $address)
    {
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully.',
        ]);
    }
}