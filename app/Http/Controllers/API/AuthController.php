<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\Cart;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([

            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required'
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'] ? $validatedData['email'] : null,
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),

        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function login(Request $request)
    {
        // ✅ التحقق من البيانات المدخلة (فقط رقم + كلمة مرور)
        $request->validate([
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        // ✅ محاولة تسجيل الدخول باستخدام رقم الهاتف
        if (!Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'phone' => ['رقم الهاتف أو كلمة المرور غير صحيحة.'],
            ]);
        }

        $user = Auth::user();

        // ✅ دمج سلة الزائر مع سلة المستخدم عند تسجيل الدخول
        $sessionId = $request->header('X-Session-ID');
        if ($sessionId) {
            Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);
        }

        // ✅ توليد توكن الوصول للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user, // ممكن ترجع بيانات المستخدم كمان
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
