<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <span class="auth-card-kicker">Secure account recovery</span>
            <h1 class="auth-card-title">Choose a new password</h1>
            <p class="auth-card-subtitle">Set a strong password for your IRDCRP MIS account.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-800">
                <p class="font-semibold">We couldn’t reset your password.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-field">
                <label for="email" class="auth-label">{{ __('Email address') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884 10 9.882l7.997-3.998A2 2 0 0 0 16 4H4a2 2 0 0 0-1.997 1.884Z"/><path d="M18 8.118 10 12 2 8.118V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.118Z"/></svg></span>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="name@organization.lk" class="auth-input @error('email') auth-input-error @enderror">
                </div>
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label">{{ __('New password') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0 1 10 0v2a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2Zm8-2v2H7V7a3 3 0 0 1 6 0Z" clip-rule="evenodd"/></svg></span>
                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Enter a new password" class="auth-input auth-password-input @error('password') auth-input-error @enderror">
                    <button type="button" class="auth-password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><svg class="password-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.24A10.9 10.9 0 0 1 12 5.04c6.25 0 9.75 6.96 9.75 6.96a16.8 16.8 0 0 1-3.02 3.77M6.23 6.23C3.69 7.98 2.25 12 2.25 12s3.5 6.96 9.75 6.96c1.12 0 2.14-.25 3.05-.66"/></svg></button>
                </div>
                <p class="mt-1 text-xs text-slate-500">Use at least 8 characters.</p>
            </div>

            <div class="auth-field">
                <label for="password_confirmation" class="auth-label">{{ __('Confirm new password') }}</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0 1 10 0v2a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2Zm8-2v2H7V7a3 3 0 0 1 6 0Z" clip-rule="evenodd"/></svg></span>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter the password again" class="auth-input auth-password-input">
                    <button type="button" class="auth-password-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"><svg class="password-eye-show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="password-eye-hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" hidden><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.24A10.9 10.9 0 0 1 12 5.04c6.25 0 9.75 6.96 9.75 6.96a16.8 16.8 0 0 1-3.02 3.77M6.23 6.23C3.69 7.98 2.25 12 2.25 12s3.5 6.96 9.75 6.96c1.12 0 2.14-.25 3.05-.66"/></svg></button>
                </div>
            </div>

            <button type="submit" class="auth-btn-primary">Reset password</button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600"><a href="{{ route('login') }}" class="auth-link auth-link-bold">Back to sign in</a></p>
    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!visible));
                button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
                button.querySelector('.password-eye-show').hidden = !visible;
                button.querySelector('.password-eye-hide').hidden = visible;
            });
        });
    </script>
</x-guest-layout>
