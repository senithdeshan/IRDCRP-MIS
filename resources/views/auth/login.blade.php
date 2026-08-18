<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Secure access</span>
            <h2 class="auth-card-title">Admin Login</h2>
            <p class="auth-card-subtitle">Sign in with your official credentials.</p>
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
                        class="auth-input @error('password') auth-input-error @enderror"
                    />
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
            {{ __("Don't have an account?") }}
            <a href="{{ route('register') }}" class="auth-link auth-link-bold">{{ __('Create staff account') }}</a>
        </p>
    </div>
</x-guest-layout>
