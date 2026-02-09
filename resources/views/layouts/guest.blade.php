<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-indigo-900 via-purple-800 to-pink-800 relative overflow-hidden">

        <!-- Decorative blobs -->
        <div
            class="absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob">
        </div>
        <div
            class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute -bottom-32 left-20 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000">
        </div>

        <div class="relative z-10 mb-6">
            <a href="/" class="flex flex-col items-center gap-2">
                <div
                    class="w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center shadow-xl border border-white/20">
                    <x-application-logo class="w-12 h-12 fill-current text-white" />
                </div>
                <span class="text-2xl font-bold text-white tracking-wider">SCHOOL ADMIN</span>
            </a>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white/90 backdrop-blur-xl shadow-2xl overflow-hidden sm:rounded-3xl border border-white/50 relative z-10">
            {{ $slot }}
        </div>

        <div class="mt-8 text-white/50 text-sm relative z-10">
            &copy; {{ date('Y') }} School Management System. All rights reserved.
        </div>
    </div>
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
                 class="flex items-center w-full max-w-xs p-4 rounded-lg shadow text-gray-500 bg-white"
                 :class="notification.type === 'error' ? 'bg-red-50 text-red-800' : 'bg-white text-gray-500'"
                 role="alert">
                
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"
                     :class="notification.type === 'error' ? 'bg-red-100 text-red-500' : 'bg-green-100 text-green-500'">
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
                <button type="button" @click="remove(notification.id)" class="mr-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @if (session()->has('success') || session()->has('message') || session()->has('notify') || session()->has('error') || session('status'))
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
                @if (session('status'))
                    messages.push({ type: 'success', message: @json(session('status')) });
                @endif

                messages.forEach((item) => {
                    const eventName = item.type === 'error' ? 'error' : 'notify';
                    window.dispatchEvent(new CustomEvent(eventName, { detail: item }));
                });
            });
        </script>
    @endif
</body>

</html>
