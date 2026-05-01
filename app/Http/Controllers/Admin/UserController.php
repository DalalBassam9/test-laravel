<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;


class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return UserResource::collection($users);
    }
}
