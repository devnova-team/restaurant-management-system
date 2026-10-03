<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct() {}

    public function login(string $email, string $password): array
    {
        // Find the staff member by email
        $staff = Staff::where('email', $email)->first();

        // Check if the staff member exists, compare the password, and ensure the staff is active
        if (! $staff || ! Hash::check($password, $staff->password_hash) || ! $staff->is_active) {
            throw new AuthenticationException('بيانات الدخول غير صحيحة أو الحساب غير نشط.');
        }

        return [
            'staff' => $staff,
            'token' => $staff->createToken('auth_token')->plainTextToken,
        ];
    }

    public function logout(Staff $staff): void
    {
        // Revoke the current access token not all tokens to log out the staff member
        $staff->currentAccessToken()->delete();
    }

    // Refresh the token for the staff member
    public function refresh(Staff $staff): array
    {
        $staff->currentAccessToken()->delete();

        return [
            'staff' => $staff,
            'token' => $staff->createToken('auth_token')->plainTextToken,
        ];
    }


}
