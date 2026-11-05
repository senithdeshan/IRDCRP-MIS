<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Account recovery</span>
            <h1 class="auth-card-title">Forgot password?</h1>
            <p class="auth-card-subtitle">Enter your official email address and we’ll send a secure link to reset your password.</p>
        </div>

        @if (in_array(config('mail.default'), ['log', 'array'], true))
            <div role="note" class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                Password reset email delivery is not configured in this environment. Please contact your system administrator.
            </div>
        @endif

        @if (session('status'))
            <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="email" class="auth-label">{{ __('Email address') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0 0 16 4H4a2 2 0 0 0-1.997 1.884Z"/><path d="M18 8.118 10 12l-8-3.882V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.118Z"/></svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@organization.lk" class="auth-input @error('email') auth-input-error @enderror">
                </div>
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <button type="submit" class="auth-btn-primary">Send reset link</button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            Remembered your password?
            <a href="{{ route('login') }}" class="auth-link auth-link-bold">Back to sign in</a>
        </p>
    </div>
</x-guest-layout>
