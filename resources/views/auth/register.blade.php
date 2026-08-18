<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Staff registration</span>
            <h2 class="auth-card-title">Create Account</h2>
            <p class="auth-card-subtitle">Register your staff account for IRDCRP MIS access.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="name" class="auth-label">{{ __('Full name') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    </span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Enter your full name"
                        class="auth-input @error('name') auth-input-error @enderror"
                    />
                </div>
                <x-input-error :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="auth-field">
                <label for="email" class="auth-label">{{ __('Work email') }}</label>
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
                        autocomplete="username"
                        placeholder="name@organization.lk"
                        class="auth-input @error('email') auth-input-error @enderror"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-field-grid">
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
                            autocomplete="new-password"
                            placeholder="Min. 8 characters"
                            class="auth-input @error('password') auth-input-error @enderror"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="auth-error" />
                </div>

                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">{{ __('Confirm password') }}</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </span>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Repeat password"
                            class="auth-input"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                </div>
            </div>

            <div class="auth-info-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p>Your account will be linked to IRDCRP staff records. Contact your administrator for role assignment after registration.</p>
            </div>

            <button type="submit" class="auth-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/></svg>
                {{ __('Create account') }}
            </button>
        </form>

        <div class="auth-divider">
            <span>Already registered?</span>
        </div>

        <p class="auth-switch">
            {{ __('Have an account?') }}
            <a href="{{ route('login') }}" class="auth-link auth-link-bold">{{ __('Sign in instead') }}</a>
        </p>
    </div>
</x-guest-layout>
