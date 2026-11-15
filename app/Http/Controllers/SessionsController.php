<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final readonly class SessionsController
{
    public function create(SeoService $seo): View
    {
        $siteUrl = rtrim((string) config('app.url', 'https://gtavi.pro'), '/');

        return view('auth.login', [
            'seoMeta' => $seo->buildPageMeta(
                title: 'Admin Sign In — Vice Editorial Studio | GTAVI.PRO',
                description: 'Sign in to access the GTAVI.PRO Editorial Studio and SEO Command Center.',
                canonicalUrl: $siteUrl.'/login',
                robots: 'noindex, nofollow',
            ),
        ]);
    }

    public function store(LoginRequest $request, AuthService $auth): RedirectResponse
    {
        $authenticated = $auth->attemptLogin(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->boolean('remember'),
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('studio.index'));
    }

    public function destroy(Request $request, AuthService $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect()
            ->route('login')
            ->with('status', 'Signed out of Editorial Studio.');
    }
}
