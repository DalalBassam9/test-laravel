<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/api/get-session-id', function (Request $request) {
    $sessionId = $request->session()->get('cart_session_id');

    if (!$sessionId) {
        $sessionId = Str::uuid()->toString();
        $request->session()->put('cart_session_id', $sessionId);
    }

    return response()->json(['session_id' => $sessionId]);
});