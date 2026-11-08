<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Staff registration</span>
            <h2 class="auth-card-title">{{ $approvedStaff ? 'Create your account' : 'Check your staff email' }}</h2>
            <p class="auth-card-subtitle">{{ $approvedStaff ? 'Your email is approved. Set a password to complete registration.' : 'The Super Admin must create your staff profile and enable registration first.' }}</p>
        </div>

        <x-auth-session-status class="auth-alert auth-alert-success" :status="session('status')" />

        @if (! $approvedStaff)
            <form method="POST" action="{{ route('register.check-email') }}" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="email" class="auth-label">Official email address</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@organization.lk" aria-describedby="email-help" @class(['auth-input', 'auth-input-error' => $errors->has('email')]) />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="auth-error" />
                    <p id="email-help" class="mt-2 text-xs text-slate-500">Use the official email recorded in your staff profile.</p>
                </div>
                <button type="submit" class="auth-btn-primary">Check email and continue</button>
            </form>
        @else
            <div class="auth-info-box" role="status">
                <div>
                    <p class="font-semibold">{{ $approvedStaff->name_with_initials }}</p>
                    <p>{{ $approvedStaff->official_email }}</p>
                    <p class="mt-1">{{ $approvedStaff->designation }}</p>
                    <a href="{{ route('register', ['change_email' => 1]) }}" class="auth-link">Use another email</a>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf
                <input type="hidden" name="email" value="{{ session('staff_registration.email') }}">
                <x-input-error :messages="$errors->get('email')" class="auth-error" />

                <div class="auth-field">
                    <label for="password" class="auth-label">Password</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <input id="password" type="password" name="password" required minlength="8" autofocus autocomplete="new-password" placeholder="At least 8 characters" @class(['auth-input', 'auth-input-error' => $errors->has('password')]) />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="auth-error" />
                </div>
                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">Confirm password</label>
                    <div class="auth-input-wrap">
                        <span class="auth-input-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repeat your password" class="auth-input" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                </div>

                <div class="auth-info-box"><p>Your account uses your approved staff profile. The Super Admin manages your role and module permissions.</p></div>
                <button type="submit" class="auth-btn-primary">Create staff account</button>
            </form>
        @endif

        <div class="auth-divider"><span>Already registered?</span></div>
        <p class="auth-switch">Have an account? <a href="{{ route('login') }}" class="auth-link auth-link-bold">Sign in</a></p>
    </div>
</x-guest-layout>
