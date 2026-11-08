<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Secure access</span>
            <h2 class="auth-card-title">Staff Login</h2>
            <p class="auth-card-subtitle">Sign in with your official MIS credentials.</p>
        </div>

        <x-auth-session-status class="auth-alert auth-alert-success" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="email" class="auth-label">{{ __('Email address') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@organization.lk"
                        class="auth-input @error('email') auth-input-error @enderror"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label">{{ __('Password') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    </span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="auth-input auth-password-input @error('password') auth-input-error @enderror"
                    />
                    <button type="button" class="auth-password-toggle" id="toggle-password" aria-label="Show password" aria-pressed="false" aria-controls="password">
                        <svg class="password-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="password-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.24A10.9 10.9 0 0 1 12 5.04c6.25 0 9.75 6.96 9.75 6.96a16.8 16.8 0 0 1-3.02 3.77M6.23 6.23C3.69 7.98 2.25 12 2.25 12s3.5 6.96 9.75 6.96c1.12 0 2.14-.25 3.05-.66"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-form-row">
                <label for="remember_me" class="auth-checkbox">
                    <input id="remember_me" type="checkbox" name="remember" class="auth-checkbox-input">
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="auth-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                {{ __('Sign in to MIS') }}
            </button>
        </form>

        <div class="auth-divider">
            <span>Need access?</span>
        </div>

        <p class="auth-switch">
            {{ __('Staff profile approved?') }}
            <a href="{{ route('register') }}" class="auth-link auth-link-bold">{{ __('Register staff account') }}</a>
        </p>
        <p class="mt-3 text-center text-xs text-slate-500">Ask the Super Admin to create your staff profile and enable registration for your official email.</p>
    </div>
    <script>
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const password = document.getElementById('password');
            const isVisible = password.type === 'text';

            password.type = isVisible ? 'password' : 'text';
            this.setAttribute('aria-pressed', String(!isVisible));
            this.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            this.querySelector('.password-eye-show').hidden = !isVisible;
            this.querySelector('.password-eye-hide').hidden = isVisible;
        });
    </script>
</x-guest-layout>
