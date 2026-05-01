<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Get all Addresses.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAddresses()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول للوصول إلى العناوين المحفوظة'
            ], 401);
        }

        $addresses = Address::where('userId', Auth::id())
            ->orderBy('default', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    /**
     * Store a new Address record.
     * @param AddressRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(AddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['userId'] = Auth::id();
        
        // If this is the first address or default is true, set all other addresses to non-default
        if (Address::where('userId', Auth::id())->count() === 0 || $data['default']) {
            Address::where('userId', Auth::id())->update(['default' => false]);
        }

        $address = Address::create($data);
        return response()->json([
            'message' => 'Address created successfully',
            'data' => new AddressResource($address)
        ], 201);
    }

    /**
     * Update Address record.
     * @param AddressRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        $data = $request->validated();
        
        // If setting as default, update other addresses
        if (isset($data['default']) && $data['default']) {
            Address::where('userId', Auth::id())->update(['default' => false]);
        }

        $address->update($data);
        return response()->json([
            'message' => 'Address updated successfully',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Set default Address.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefault(Address $address): JsonResponse
    {
        Address::where('userId', Auth::id())->update(['default' => false]);
        $address->update(['default' => true]);
        
        return response()->json([
            'message' => 'Address set as default successfully',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Delete Address record.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Address $address): JsonResponse
    {
        $address->delete();
        return response()->json([
            'message' => 'Address deleted successfully'
        ]);
    }

    public function index(): AnonymousResourceCollection
    {
        $addresses = Address::where('userId', Auth::id())->get();
        return AddressResource::collection($addresses);
    }

    public function show(Address $address): AddressResource
    {
        return new AddressResource($address);
    }
}
