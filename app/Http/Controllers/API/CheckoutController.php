<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required_without:guest_address|exists:addresses,id',
            'guest_address' => 'required_without:address_id|array',
            'guest_address.firstName' => 'required_with:guest_address|string|max:255',
            'guest_address.lastName' => 'required_with:guest_address|string|max:255',
            'total_price' => 'required|numeric|min:0', // إضافة التحقق من السعر
            'payment_method' => 'required|in:cash,click',
            'notes' => 'nullable|string',
            'guest_address.cityId' => 'nullable',
            'save_address' => 'boolean',
            'delivery_fee' => 'required|numeric|min:0',

        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $addressData = $this->handleAddress($request);
            $order = $this->createOrder($request, $addressData);
            $this->processCartItems($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => $this->getSuccessMessage($request->payment_method)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()
            ], 500);
        }
    }
    private function handleAddress($request)
    {
        if (Auth::check()) {
            return $this->handleUserAddress($request);
        }

        // إنشاء عنوان مؤقت للضيف
        $guestAddress = $request->guest_address;
        $address = Address::create([
            'firstName' => $guestAddress['firstName'],
            'lastName' => $guestAddress['lastName'],
            'phone' => $guestAddress['phone'],
            'address' => $guestAddress['address'],
            'district' => $guestAddress['district'] ?? null,
            'cityId' => $guestAddress['cityId'] ?? null,
            'userId' => null // السماح بقيم null للمستخدمين الزوار
        ]);

        return $address;
    }

    private function handleUserAddress($request)
    {
        // إذا تم اختيار عنوان موجود
        if ($request->filled('address_id')) {
            return Address::where('id', $request->address_id)
                ->where('userId', Auth::id())
                ->firstOrFail();
        }

        // إذا تم إضافة عنوان جديد وطلب حفظه
        if ($request->save_address) {
            return Address::create([
                'userId' => Auth::id(),
                'firstName' => $request->guest_address['firstName'],
                'lastName' => $request->guest_address['lastName'],
                'phone' => $request->guest_address['phone'],
                'address' => $request->guest_address['address'],
                'district' => $request->guest_address['district'] ?? null,
                'cityId' => $request->guest_address['cityId'] ?? null,
                'default' => !Auth::user()->addresses()->exists()
            ]);
        }

        // إنشاء عنوان مؤقت دون حفظه
        return Address::create([
            'userId' => Auth::id(),
            'firstName' => $request->guest_address['firstName'],
            'lastName' => $request->guest_address['lastName'],
            'phone' => $request->guest_address['phone'],
            'address' => $request->guest_address['address'],
            'district' => $request->guest_address['district'] ?? null,
            'cityId' => $request->guest_address['cityId'] ?? null,
            'temp' => true // حقل جديد للعناوين المؤقتة
        ]);
    }

    private function createOrder($request, $addressData)
    {
        return Order::create([
            'user_id' => Auth::id(),
            'address_id' => $addressData instanceof Address ? $addressData->id : null,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method,
            'click_id' => $request->payment_method === 'click' ? $request->click_id : null,
            'status' => 'pending',
            'total_price' => $request->total_price, // استخدام القيمة المرسلة
            'delivery_fee' => $request->delivery_fee,
        ]);
    }

    private function processCartItems($order)
    {
        $cartQuery = Auth::check()
            ? Cart::where('user_id', Auth::id())
            : Cart::where('session_id', $this->getSessionId());

        $cartItems = $cartQuery->with('product')->get();

        if ($cartItems->isEmpty()) {
            throw new \Exception('السلة فارغة');
        }
        $totalAmount = 0;

        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price
            ]);

            $totalAmount += ($cartItem->product->price * $cartItem->quantity);
        }




        //   $order->update([
        //   'total_amount' => $totalAmount + $order->delivery_fee
        // ]);

        $cartItems->each->delete();
    }

    private function getSessionId()
    {
        $sessionId = request()->header('X-Session-ID');
        if (!$sessionId) {
            throw new \Exception('مطلوب معرف جلسة صالح');
        }
        return $sessionId;
    }

    private function getSuccessMessage($paymentMethod)
    {
        return $paymentMethod === 'click'
            ? 'تم إنشاء الطلب بنجاح، سيتم التواصل معك للتأكيد والدفع عبر كليك'
            : 'تم إنشاء الطلب بنجاح، سيتم الدفع عند الاستلام';
    }
       public function getOrderUser($id)
    {
        $order = Order::with(['address.city', 'items.product.images'])
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
