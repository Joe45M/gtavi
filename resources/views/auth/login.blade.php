@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-md rounded-md border border-slate-200 border-t-3 border-t-[#2b124c] bg-white p-6 shadow-xs sm:p-8">
        <div class="mb-6">
            <span class="inline-flex rounded border border-[#fbcfe8] bg-[#fdf2f8] px-2.5 py-0.5 text-xs font-semibold text-[#9d174d]">
                Protected Back-Office Access
            </span>
            <h1 id="main-heading" tabindex="-1" class="mt-2.5 text-2xl font-bold tracking-tight text-slate-900">
                Editorial Studio Sign In
            </h1>
            <p class="mt-1.5 text-sm text-slate-600">
                Sign in with an authorized editor or administrator account to access <code>/admin</code> and <code>/studio</code>.
            </p>
        </div>

        @if (session('status'))
            <div role="status" aria-live="polite" class="mb-6 rounded-md border border-emerald-200 border-l-3 border-l-emerald-600 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" aria-labelledby="main-heading" class="space-y-6">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-semibold text-slate-900">
                    Email Address <span aria-hidden="true" class="text-[#be185d]">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    autocorrect="off"
                    autocapitalize="none"
                    spellcheck="false"
                    enterkeyhint="next"
                    required
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @if ($errors->has('email')) aria-describedby="email-error" @endif
                    class="min-h-[48px] w-full rounded border {{ $errors->has('email') ? 'border-rose-600 bg-rose-50/40' : 'border-[#767676] bg-white' }} px-3.5 py-2.5 text-[max(1rem,1em)] text-slate-900 placeholder:text-slate-400"
                    placeholder="editor@gtavi.pro"
                >
                @error('email')
                    <p id="email-error" role="alert" class="text-sm font-medium text-rose-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-sm font-semibold text-slate-900">
                    Password <span aria-hidden="true" class="text-[#be185d]">*</span>
                </label>
                <div class="flex items-stretch gap-2">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        enterkeyhint="done"
                        required
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        @if ($errors->has('password')) aria-describedby="password-error" @endif
                        class="min-h-[48px] w-full rounded border {{ $errors->has('password') ? 'border-rose-600 bg-rose-50/40' : 'border-[#767676] bg-white' }} px-3.5 py-2.5 text-[max(1rem,1em)] text-slate-900"
                    >
                    <button
                        type="button"
                        id="toggle-password"
                        aria-pressed="false"
                        aria-label="Show password"
                        aria-describedby="toggle-password-warning"
                        class="inline-flex min-h-[48px] min-w-[72px] shrink-0 cursor-pointer items-center justify-center rounded border border-[#767676] bg-slate-50 px-3 text-sm font-semibold text-slate-800 transition hover:bg-slate-100"
                    >
                        Show
                    </button>
                </div>
                <span id="toggle-password-warning" class="visually-hidden">
                    Warning: this will display your password on the screen.
                </span>
                @error('password')
                    <p id="password-error" role="alert" class="text-sm font-medium text-rose-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="h-5 w-5 cursor-pointer rounded border-[#767676] accent-[#be185d]"
                >
                <label for="remember" class="cursor-pointer py-2 text-sm font-medium text-slate-700">
                    Keep me signed in on this browser
                </label>
            </div>

            <button
                type="submit"
                class="inline-flex min-h-[48px] w-full cursor-pointer items-center justify-center rounded bg-[#2b124c] px-5 py-3 text-base font-semibold text-white transition hover:bg-[#3b1a66]"
            >
                Sign In to Editorial Studio
            </button>
        </form>

        <div class="mt-6 border-t border-slate-200 pt-4 text-xs text-slate-500">
            <p>
                Access is restricted by role and permission checks (<code>admin</code> and <code>editor</code> roles via Spatie Permissions).
            </p>
        </div>
    </section>
@endsection
