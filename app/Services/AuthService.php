<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final readonly class AuthService
{
    public function attemptLogin(string $email, string $password, bool $remember = false): bool
    {
        return Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
