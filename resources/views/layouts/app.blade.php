<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl" class="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Theme Initialization Script (must be in head to prevent flash) -->
    <script>
        (() => {
            const root = document.documentElement;
            root.classList.add('theme-preload');
            try {
                const stored = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme =
                    stored === 'dark' || stored === 'light'
                        ? stored
                        : prefersDark
                          ? 'dark'
                          : 'light';
                root.classList.toggle('dark', theme === 'dark');
            } catch (error) {
                root.classList.toggle(
                    'dark',
                    window.matchMedia('(prefers-color-scheme: dark)').matches
                );
            }
            window.addEventListener('load', () => {
                requestAnimationFrame(() => root.classList.remove('theme-preload'));
            });
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @livewireStyles
</head>

<body class="font-sans antialiased text-foreground bg-background">
    <div class="min-h-screen bg-background transition-colors duration-200">
        @include('layouts.navigation')
        @include('layouts.sidebar')

        <div class="main-content">
            <!-- Page Heading -->
            @hasSection('header')
                <header class="bg-surface shadow border-b border-border">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('header')
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
                @isset($slot)
                    {{ $slot }}
                @endisset
            </main>
        </div>
    </div>
    <script>
        (() => {
            const root = document.documentElement;

            function getTheme() {
                try {
                    const stored = localStorage.getItem('theme');
                    if (stored === 'dark' || stored === 'light') {
                        return stored;
                    }
                } catch (error) {
                    // ignore storage errors
                }
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function updateThemeIcons(theme) {
                const isDark = theme === 'dark';
                const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
                const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
                const themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');
                const themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
                const themeToggleTextMobile = document.getElementById('theme-toggle-text-mobile');

                if (themeToggleLightIcon && themeToggleDarkIcon) {
                    themeToggleLightIcon.classList.toggle('hidden', !isDark);
                    themeToggleDarkIcon.classList.toggle('hidden', isDark);
                }

                if (themeToggleLightIconMobile && themeToggleDarkIconMobile) {
                    themeToggleLightIconMobile.classList.toggle('hidden', !isDark);
                    themeToggleDarkIconMobile.classList.toggle('hidden', isDark);
                    if (themeToggleTextMobile) {
                        themeToggleTextMobile.textContent = isDark ? 'الوضع النهاري' : 'الوضع الليلي';
                    }
                }
            }

            function applyTheme(theme) {
                root.classList.toggle('dark', theme === 'dark');
                updateThemeIcons(theme);
            }

            function toggleTheme() {
                root.classList.add('theme-switching');
                const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
                try {
                    localStorage.setItem('theme', nextTheme);
                } catch (error) {
                    // ignore storage errors
                }
                applyTheme(nextTheme);
                requestAnimationFrame(() => {
                    setTimeout(() => root.classList.remove('theme-switching'), 150);
                });
            }

            function bindThemeToggleButtons() {
                [document.getElementById('theme-toggle'), document.getElementById('theme-toggle-mobile')]
                    .filter(Boolean)
                    .forEach((button) => {
                        if (button.dataset.themeBound === 'true') {
                            return;
                        }
                        button.addEventListener('click', toggleTheme);
                        button.dataset.themeBound = 'true';
                    });
            }

            function initThemeControls() {
                const desiredTheme = getTheme();
                const currentTheme = root.classList.contains('dark') ? 'dark' : 'light';
                if (currentTheme !== desiredTheme) {
                    applyTheme(desiredTheme);
                } else {
                    updateThemeIcons(desiredTheme);
                }
                bindThemeToggleButtons();
            }

            initThemeControls();
            document.addEventListener('livewire:load', initThemeControls);
            document.addEventListener('livewire:navigated', initThemeControls);
        })();
    </script>
    {{-- Notification Component --}}
    <div x-data="{ 
            notifications: [],
            add(message, type = 'success') {
                const id = Date.now();
                this.notifications.push({ id, message, type });
                setTimeout(() => this.remove(id), 5000);
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
        }"
        x-on:notify.window="add(typeof $event.detail === 'string' ? $event.detail : (Array.isArray($event.detail) ? $event.detail[0] : $event.detail.message), $event.detail.type ?? 'success')"
        x-on:toast.window="add(typeof $event.detail === 'string' ? $event.detail : (Array.isArray($event.detail) ? $event.detail[0] : $event.detail.message), $event.detail.type ?? 'success')"
        x-on:error.window="add(typeof $event.detail === 'string' ? $event.detail : (Array.isArray($event.detail) ? $event.detail[0] : $event.detail.message), 'error')"
        class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="flex items-center w-full max-w-xs p-4 rounded-lg shadow border border-border"
                 :class="notification.type === 'error' ? 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800' : 'bg-surface text-foreground'"
                 role="alert">
                
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"
                     :class="notification.type === 'error' ? 'bg-red-100 text-red-500 dark:bg-red-800 dark:text-red-200' : 'bg-green-100 text-green-500 dark:bg-green-800 dark:text-green-200'">
                    <template x-if="notification.type === 'error'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z"/>
                        </svg>
                    </template>
                    <template x-if="notification.type !== 'error'">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
                        </svg>
                    </template>
                </div>
                <div class="mr-3 text-sm font-normal" x-text="notification.message"></div>
                <button type="button" @click="remove(notification.id)" class="mr-auto -mx-1.5 -my-1.5 bg-surface text-muted-foreground hover:text-foreground rounded-lg focus:ring-2 focus:ring-border p-1.5 hover:bg-background inline-flex items-center justify-center h-8 w-8" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @if (session()->has('success') || session()->has('message') || session()->has('notify') || session()->has('error'))
        <script>
            window.addEventListener('load', () => {
                const messages = [];
                @if (session()->has('success'))
                    messages.push({ type: 'success', message: @json(session('success')) });
                @endif
                @if (session()->has('notify'))
                    messages.push({ type: 'success', message: @json(session('notify')) });
                @endif
                @if (session()->has('message'))
                    messages.push({ type: 'success', message: @json(session('message')) });
                @endif
                @if (session()->has('error'))
                    messages.push({ type: 'error', message: @json(session('error')) });
                @endif

                messages.forEach((item) => {
                    const eventName = item.type === 'error' ? 'error' : 'notify';
                    window.dispatchEvent(new CustomEvent(eventName, { detail: item }));
                });
            });
        </script>
    @endif

    @livewireScripts
    @livewireChartsScripts
</body>

</html>
