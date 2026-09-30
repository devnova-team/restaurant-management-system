<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        // Call the AuthService to handle the login logic
        $result = $this->authService->login($validatedData['email'], $validatedData['password']);

        return ApiResponse::success($result, 'تم تسجيل الدخول بنجاح');
    }

    public function logout(Request $request)
    {
        // Call the AuthService to handle the logout logic
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'تم تسجيل الخروج بنجاح');
    }
}
