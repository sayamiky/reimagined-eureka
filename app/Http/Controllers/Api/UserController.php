<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function index(Request $request)
    {
        $users = User::paginate(
            $perPage = $request->perPage
        )->withQueryString();

        return UserResource::collection($users)->additional(['message' => 'Users retrieved successfully']);
    }

    function show($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'data' => $user,
            'message' => 'User retrieved successfully'
        ]);
    }
}
