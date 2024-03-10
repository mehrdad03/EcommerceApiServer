<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'address' => 'required|string',
            'cellphone' => 'required',
            'postal_cod' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',

        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }
        /*  dd($request->all());*/

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'cellphone' => $request->cellphone,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'postal_cod' => $request->postal_cod,
        ]);
        $token = $user->createToken('myApp')->plainTextToken;
        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse('user not found', 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('password is incorrect', 401);
        }

        $token = $user->createToken('myApp')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth()->user()->tokens()->delete();
        return $this->successResponse('log out');

    }
}
