@props(['student', 'canDelete' => false, 'deleteBlockers' => []])

<div class="bg-gradient-to-r from-blue-600 to-blue-800 pt-4 pb-20 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0"
            style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <!-- Navigation -->
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('students.index') }}"
                class="text-white/90 hover:text-white flex items-center gap-2 text-sm transition-colors group">
                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-medium">العودة للقائمة</span>
            </a>
            <div class="flex items-center gap-3">
                {{-- Delete Button --}}
                @if($canDelete)
                    <button wire:click="delete"
                        wire:confirm="هل أنت متأكد من حذف هذا الطالب؟ سيتم حذف جميع البيانات المرتبطة به."
                        class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-white text-sm rounded-lg flex items-center gap-2 transition-all hover:scale-105 border border-red-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        حذف الطالب
                    </button>
                @else
                    <div class="relative group">
                        <button disabled
                            class="px-4 py-2 bg-gray-500/20 text-gray-400 text-sm rounded-lg flex items-center gap-2 cursor-not-allowed border border-gray-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            حذف الطالب
                        </button>
                        {{-- Tooltip --}}
                        <div class="absolute top-full right-0 mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-xl border border-gray-700">
                            <div class="font-bold mb-1 text-red-400">لا يمكن الحذف:</div>
                            <ul class="list-disc list-inside space-y-1 text-gray-300">
                                @foreach($deleteBlockers as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <button wire:click="edit"
                    class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm rounded-lg flex items-center gap-2 transition-all hover:scale-105 border border-white/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    تعديل البيانات
                </button>
            </div>
        </div>

        <!-- Student Profile Header -->
        <div class="flex flex-col md:flex-row items-center gap-6 md:gap-8">
            <!-- Circular Profile Image -->
            <div class="relative group">
                <!-- Glow Effect -->
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-blue-400 to-purple-500 blur-lg opacity-30 group-hover:opacity-50 transition-opacity">
                </div>

                <!-- Image Container -->
                <div
                    class="relative w-28 h-28 md:w-32 md:h-32 rounded-full border-4 border-white/40 shadow-2xl overflow-hidden bg-gradient-to-br from-blue-400 to-indigo-500">
                    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->full_name_ar }}"
                        class="w-full h-full object-cover">
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>

                <!-- Status Badge -->
                <div class="absolute -bottom-2 -right-2 z-20">
                    <x-student.badge :status="$student->status" :label="$student->status->label()" size="sm" />
                </div>
            </div>

            <!-- Student Info -->
            <div class="flex-1 text-center md:text-right">
                <!-- Name and ID -->
                <div class="mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ $student->full_name_ar }}</h1>
                    <div
                        class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 border border-white/20">
                        <span class="text-white/80 text-sm font-medium">رقم القيد:</span>
                        <span class="text-white font-bold">{{ $student->admission_number }}</span>
                    </div>
                </div>

                <!-- Quick Info Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 w-full md:w-auto mt-4 md:mt-0">
                    @php
                        $quickInfo = [
                            [
                                'icon' => 'M12 14l9-5-9-5-9 5 9 5z',
                                'label' => 'المستوى الحالي',
                                'value' => $student->currentGrade?->name ?? 'غير محدد',
                                'variant' => 'blue',
                            ],
                            [
                                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                                'label' => 'أولياء الأمور',
                                'value' => $student->guardians->count(),
                                'variant' => 'purple',
                            ],
                            [
                                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                'label' => 'سنوات الدراسة',
                                'value' => ($student->created_at->diffInYears(now()) + 1) . ' سنوات',
                                'variant' => 'pink',
                            ],
                            [
                                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                'label' => 'حالة الحساب',
                                'value' => 'غير مفعل',
                                'variant' => 'green',
                            ],
                        ];

                        $variants = [
                            'blue' => 'from-blue-400 to-blue-500',
                            'purple' => 'from-purple-400 to-purple-500',
                            'pink' => 'from-pink-400 to-pink-500',
                            'green' => 'from-green-400 to-green-500',
                        ];
                    @endphp

                    @foreach ($quickInfo as $info)
                        <div class="bg-white/10 rounded-lg p-3 border border-white/10 hover:bg-white/20 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-md bg-gradient-to-br {{ $variants[$info['variant']] }} shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $info['icon'] }}" />
                                    </svg>
                                </div>
                                <div class="text-right overflow-hidden">
                                    <p class="text-xs text-white/70 mb-0.5 truncate">{{ $info['label'] }}</p>
                                    <p class="text-sm font-bold text-white truncate">{{ $info['value'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
