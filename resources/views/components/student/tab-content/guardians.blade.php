@props(['guardians'])

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($guardians as $guardian)
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl ml-3">
                        {{ mb_substr($guardian->first_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $guardian->first_name }} {{ $guardian->last_name }}
                        </h3>
                        <span class="text-sm text-indigo-500 dark:text-indigo-400 font-medium bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-md">
                            {{ $guardian->pivot->relationship }}
                        </span>
                    </div>
                </div>
                @if ($guardian->pivot->is_financial_sponsor)
                    <span title="مسؤول مالي" class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center dark:bg-green-900/30 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                @endif
            </div>
            <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                @if ($guardian->phone)
                    <a href="tel:{{ $guardian->phone }}" class="flex items-center text-gray-600 dark:text-gray-300 hover:text-indigo-600 transition-colors p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg">
                        <svg class="w-5 h-5 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $guardian->phone }}
                    </a>
                @endif

                @if ($guardian->email)
                    <a href="mailto:{{ $guardian->email }}" class="flex items-center text-gray-600 dark:text-gray-300 hover:text-indigo-600 transition-colors p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg">
                        <svg class="w-5 h-5 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $guardian->email }}
                    </a>
                @endif

                @if ($guardian->job)
                    <div class="flex items-center text-gray-600 dark:text-gray-300 p-2">
                        <svg class="w-5 h-5 ml-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ $guardian->job }}
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="md:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center border border-gray-100 dark:border-gray-700">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.83.63-1.873 1-3 1a4.978 4.978 0 01-3-1m3-4a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">لا يوجد أولياء أمور</h3>
                <p class="text-gray-500 dark:text-gray-400">لم يتم إضافة أي ولي أمر لهذا الطالب</p>
            </div>
        </div>
    @endforelse
</div>
