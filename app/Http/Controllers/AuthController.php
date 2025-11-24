<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $request->validate([
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                ]);

                $credentials = $request->only('email', 'password');

                if (!$token = JWTAuth::attempt($credentials)) {
                    throw ValidationException::withMessages([
                        'email' => ['The provided credentials are incorrect.'],
                    ]);
                }

                $user = Auth::user();
                $user->load('status');

                return response()->json([
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'token' => $token,
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error in Login",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for get login is just for JSON request";
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $request->validate([
                    'token' => 'required|string',
                ]);

                JWTAuth::setToken($request->input('token'))->invalidate();

                return response()->json(['message' => 'Sesión cerrada exitosamente.'], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error in Logout",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
        return "The access for logout is just for JSON request";
    }

    public function register(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                ]);

                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'status_id' => 1, // Activo por defecto
                ]);

                $token = JWTAuth::fromUser($user);
                $user->load('status');
                $user->assignRole('user'); // Asignar rol user por defecto

                return response()->json([
                    'user' => $user,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'token' => $token,
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    "severity" => "error",
                    "summary" => "Error",
                    "detail" => "Error in Login",
                    "errors" => $th->getMessage()
                ], 422);
            }
        }
    }
}
