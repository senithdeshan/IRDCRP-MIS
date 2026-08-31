<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>IRDCRP MIS - Secure Access</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $isRegister = request()->routeIs('register');
        @endphp

        <div class="auth-page {{ $isRegister ? 'auth-page-register' : 'auth-page-login' }}">
            <main class="auth-stage">
                <aside class="auth-photo-timeline" aria-hidden="true">
                    <div class="auth-slide-frame">
                        <img src="{{ asset($isRegister ? 'images/auth/register-background.jpg' : 'images/auth/timeline-1.jpg') }}" alt="" class="auth-slide auth-slide-one">
                        <div class="auth-slide-caption">
                            <span>{{ $isRegister ? 'IRDCRP staff access' : 'IRDCRP field stories' }}</span>
                            <strong>{{ $isRegister ? 'Register for secure MIS access' : 'Farmers, fields and resilience' }}</strong>
                        </div>
                    </div>
                    <div class="auth-slide-dots">
                        <span class="is-active"></span>
                        <span></span>
                        <span></span>
                    </div>
                </aside>

                <section class="auth-panel" aria-label="{{ $isRegister ? 'Register' : 'Login' }}">
                    <div class="auth-brand-card">
                        <div class="auth-logo-strip" aria-label="Project partners">
                            <img src="{{ asset('images/logos/government-seal.png') }}" alt="Government of Sri Lanka" class="auth-logo auth-logo-seal">
                            <span class="auth-logo-divider"></span>
                            <img src="{{ asset('images/logos/irdcrp-logo.png') }}" alt="IRDCRP" class="auth-logo auth-logo-irdcrp">
                            <span class="auth-logo-divider"></span>
                            <img src="{{ asset('images/logos/world-bank.png') }}" alt="The World Bank" class="auth-logo auth-logo-world-bank">
                        </div>

                        <div class="auth-system-name">
                            <p>IRDCRP MIS</p>
                            <span>Management Information System</span>
                        </div>

                        {{ $slot }}

                        <p class="auth-footer-note">
                            &copy; {{ date('Y') }} IRDCRP MIS. Authorized personnel only.
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
