<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountUserController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }
    
    public function updateUserInformation(AccountRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $user->update([
            'phone' => $request->phone ?? $user->phone,
            'email' => $request->email ?? $user->email,
            'firstName' => $request->firstName ?? $user->firstName,
            'lastName' => $request->lastName ?? $user->lastName,
        ]);
    
        return new UserResource($user);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = User::findOrFail(Auth::id());

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }
}
