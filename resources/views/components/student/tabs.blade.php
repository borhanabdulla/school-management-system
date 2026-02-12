@props(['activeTab'])

<div class="mb-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <nav class="flex space-x-1 rtl:space-x-reverse overflow-x-auto no-scrollbar">
            <!-- Profile Tab -->
            <button wire:click="$set('activeTab', 'profile')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'profile' 
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 shadow-sm ring-1 ring-purple-200 dark:ring-purple-700/50' 
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200' }}">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 {{ $activeTab === 'profile' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span>الملف الشخصي</span>
                </div>
            </button>

            <!-- Academic Tab -->
            <button wire:click="$set('activeTab', 'academic')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'academic' 
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 shadow-sm ring-1 ring-purple-200 dark:ring-purple-700/50' 
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200' }}">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 {{ $activeTab === 'academic' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.499 5.516 50.552 50.552 0 0 0-2.658.813m-15.482 0A50.55 50.55 0 0 1 12 13.489a50.55 50.55 0 0 1 1.518-2.528M20.25 10.5V18" />
                    </svg>
                    <span>السجل الأكاديمي</span>
                </div>
            </button>

            <!-- Guardians Tab -->
            <button wire:click="$set('activeTab', 'guardians')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'guardians' 
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 shadow-sm ring-1 ring-purple-200 dark:ring-purple-700/50' 
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200' }}">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 {{ $activeTab === 'guardians' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>أولياء الأمور</span>
                </div>
            </button>

            <!-- Grades Tab -->
            <button wire:click="$set('activeTab', 'grades')"
                class="flex-1 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $activeTab === 'grades' 
                    ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 shadow-sm ring-1 ring-purple-200 dark:ring-purple-700/50' 
                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200' }}">
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 {{ $activeTab === 'grades' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span>الدرجات والتحليل</span>
                </div>
            </button>
        </nav>
    </div>
</div>
