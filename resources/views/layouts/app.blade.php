<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-[#f6f7fb] text-slate-800">
        <div class="min-h-screen lg:pl-72">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-10">
                {{ $slot }}
            </main>
        </div>

        <div class="confirm-dialog" data-confirm-dialog>
            <div class="confirm-dialog-backdrop" data-confirm-cancel></div>
            <div class="confirm-dialog-panel" role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title">
                <div class="confirm-dialog-mark" data-confirm-mark>!</div>
                <h2 id="confirm-dialog-title" class="confirm-dialog-title" data-confirm-title-text>Confirm action</h2>
                <p class="confirm-dialog-message" data-confirm-message-text>Are you sure you want to continue?</p>
                <div class="confirm-dialog-actions">
                    <button type="button" class="confirm-dialog-cancel" data-confirm-cancel>Cancel</button>
                    <button type="button" class="confirm-dialog-submit" data-confirm-submit>Confirm</button>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const dialog = document.querySelector('[data-confirm-dialog]');
                const title = dialog.querySelector('[data-confirm-title-text]');
                const message = dialog.querySelector('[data-confirm-message-text]');
                const submitButton = dialog.querySelector('[data-confirm-submit]');
                const mark = dialog.querySelector('[data-confirm-mark]');
                let pendingForm = null;

                const closeDialog = () => {
                    dialog.classList.remove('is-open');
                    document.body.classList.remove('overflow-hidden');
                    pendingForm = null;
                };

                const openDialog = (form) => {
                    pendingForm = form;
                    title.textContent = form.dataset.confirmTitle || 'Confirm action';
                    message.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue?';
                    submitButton.textContent = form.dataset.confirmAction || 'Confirm';
                    submitButton.dataset.tone = form.dataset.confirmTone || 'slate';
                    mark.dataset.tone = form.dataset.confirmTone || 'slate';
                    dialog.classList.add('is-open');
                    document.body.classList.add('overflow-hidden');
                };

                document.addEventListener('submit', (event) => {
                    const form = event.target;

                    if (!(form instanceof HTMLFormElement) || ! form.matches('form[data-confirm-title]')) {
                        return;
                    }

                    event.preventDefault();
                    openDialog(form);
                });

                submitButton.addEventListener('click', () => {
                    if (! pendingForm) {
                        closeDialog();
                        return;
                    }

                    const form = pendingForm;
                    closeDialog();
                    HTMLFormElement.prototype.submit.call(form);
                });

                dialog.querySelectorAll('[data-confirm-cancel]').forEach((button) => {
                    button.addEventListener('click', closeDialog);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && dialog.classList.contains('is-open')) {
                        closeDialog();
                    }
                });
            })();
        </script>
        @stack('scripts')
    </body>
</html>
