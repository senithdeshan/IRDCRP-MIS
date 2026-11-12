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

        <div class="auth-page auth-page-photos {{ $isRegister ? 'auth-page-register' : 'auth-page-login' }}">
            <main class="auth-stage">
                <aside class="auth-photo-timeline" aria-hidden="true">
                    <div class="auth-slide-frame">
                        @foreach ([1, 2, 3, 4, 5] as $slide)
                            <img src="{{ asset('images/auth/cover'.$slide.'.jpeg') }}" alt="" @class(['auth-slide', 'is-active' => $slide === 1])>
                        @endforeach
                        <div class="auth-slide-caption">
                            <span>{{ $isRegister ? 'IRDCRP staff access' : 'IRDCRP in the field' }}</span>
                            <strong>{{ $isRegister ? 'Join the team building resilient communities.' : 'Growing stronger communities together.' }}</strong>
                        </div>
                    </div>
                    <div class="auth-slide-dots">
                        @foreach ([1, 2, 3, 4, 5] as $dot)
                            <span @class(['is-active' => $dot === 1])></span>
                        @endforeach
                    </div>
                </aside>

                <section class="auth-panel" aria-label="{{ $isRegister ? 'Register' : 'Login' }}">
                    <div class="auth-brand-card">
                        <div class="auth-logo-strip" aria-label="Project partners">
                            <img src="{{ asset('images/logos/government-seal.png') }}" alt="Government of Sri Lanka" class="auth-logo auth-logo-seal">
                            <span class="auth-logo-divider"></span>
                            <x-application-logo class="auth-logo auth-logo-irdcrp" />
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
        <script>
            (() => {
                const carousel = document.querySelector('.auth-page-photos');
                if (!carousel) return;

                const slides = [...carousel.querySelectorAll('.auth-slide')];
                const dots = [...carousel.querySelectorAll('.auth-slide-dots span')];
                if (slides.length < 2) return;

                let activeIndex = 0;
                window.setInterval(() => {
                    activeIndex = (activeIndex + 1) % slides.length;
                    slides.forEach((slide, index) => slide.classList.toggle('is-active', index === activeIndex));
                    dots.forEach((dot, index) => dot.classList.toggle('is-active', index === activeIndex));
                }, 2000);
            })();
        </script>
    </body>
</html>
