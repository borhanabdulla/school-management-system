<?php
/**
 * YearClosingWizard View - معالج إغلاق السنة الدراسية
 *
 * خطوة 1: تقرير الجاهزية
 * خطوة 2: الفحوصات النهائية
 * خطوة 3: تأكيد الإغلاق
 */
?>

<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">
            معالج إغلاق السنة الدراسية
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ $year->name }}
        </p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($step = 1; $step <= $totalSteps; $step++)
                <div class="flex items-center {{ $step < $totalSteps ? 'flex-1' : '' }}">
                    <!-- Step Circle -->
                    <div class="flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold
                            @if ($currentStep >= $step)
                                bg-blue-600 text-white
                            @else
                                bg-gray-200 text-gray-500
                            @endif
                        ">
                            {{ $step }}
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-500">
                            @if ($step === 1)
                                الجاهزية
                            @elseif ($step === 2)
                                الفحوصات
                            @else
                                التأكيد
                            @endif
                        </span>
                    </div>

                    <!-- Step Line -->
                    @if ($step < $totalSteps)
                        <div class="flex-1 h-1 mx-4
                            @if ($currentStep > $step)
                                bg-blue-600
                            @else
                                bg-gray-200
                            @endif
                        "></div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- Step Content -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        @switch($currentStep)
            <!-- Step 1: Readiness Report -->
            @case(1)
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        تقرير جاهزية السنة الدراسية للإغلاق
                    </h2>

                    <!-- Blocking Issues -->
                    @if (count($blockingItems) > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-red-600 mb-3 flex items-center">
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                مشاكل تمنع الإغلاق ({{ count($blockingItems) }})
                            </h3>
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <ul class="list-disc list-inside space-y-2">
                                    @foreach ($blockingItems as $item)
                                        <li class="text-sm text-red-700">
                                            <strong>{{ $item['label'] }}:</strong> {{ $item['message'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm font-medium text-green-700">
                                        لا توجد مشاكل تمنع إغلاق السنة الدراسية
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Warning Issues -->
                    @if (count($warningItems) > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-yellow-600 mb-3 flex items-center">
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                تحذيرات ({{ count($warningItems) }})
                            </h3>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <ul class="list-disc list-inside space-y-2">
                                    @foreach ($warningItems as $item)
                                        <li class="text-sm text-yellow-700">
                                            <strong>{{ $item['label'] }}:</strong> {{ $item['message'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        @if (count($blockingItems) === 0)
                            <button
                                wire:click="nextStep"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                التالي
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @else
                            <div class="text-sm text-gray-500">
                                يجب حل جميع المشاكل أولاً قبل المتابعة
                            </div>
                        @endif
                    </div>
                </div>
                @break

            <!-- Step 2: Final Checks -->
            @case(2)
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        الفحوصات النهائية قبل الإغلاق
                    </h2>

                    <div class="space-y-4">
                        <!-- Check 1 -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="confirmNoBlockingIssues"
                                    type="checkbox"
                                    wire:model="confirmNoBlockingIssues"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="confirmNoBlockingIssues" class="font-medium text-gray-700">
                                    لا توجد مشاكل تمنع الإغلاق
                                </label>
                                <p class="text-gray-500">
                                    تأكدت من مراجعة تقرير الجاهزية ولا توجد مشاكل.
                                </p>
                            </div>
                        </div>

                        <!-- Check 2 -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="confirmBackupTaken"
                                    type="checkbox"
                                    wire:model="confirmBackupTaken"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="confirmBackupTaken" class="font-medium text-gray-700">
                                    تم أخذ نسخة احتياطية
                                </label>
                                <p class="text-gray-500">
                                    تم أخذ نسخة احتياطية من قاعدة البيانات قبل الإغلاق.
                                </p>
                            </div>
                        </div>

                        <!-- Check 3 -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="confirmResponsibility"
                                    type="checkbox"
                                    wire:model="confirmResponsibility"
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="confirmResponsibility" class="font-medium text-gray-700">
                                    أتحمل المسؤولية
                                </label>
                                <p class="text-gray-500">
                                    أدرك أن هذا الإجراء نهائي ولا يمكن التراجع عنه إلا عبر نظام التعديلات.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Error -->
                    @error('step2')
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Action Buttons -->
                    <div class="flex justify-between pt-4 border-t border-gray-200">
                        <button
                            wire:click="previousStep"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            السابق
                        </button>

                        <button
                            wire:click="nextStep"
                            @if (!$canProceedFromStep2()) disabled @endif
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            التالي
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                @break

            <!-- Step 3: Confirm Closing -->
            @case(3)
                <div class="p-6">
                    @if ($closingSuccess)
                        <!-- Success Message -->
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                تم إغلاق السنة الدراسية بنجاح
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                {{ $closingSuccess }}
                            </p>
                            <a
                                href="{{ route('academic-years.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-white hover:bg-blue-700"
                            >
                                العودة للسنين الدراسية
                            </a>
                        </div>
                    @else
                        <h2 class="text-lg font-medium text-gray-900 mb-4">
                            تأكيد إغلاق السنة الدراسية
                        </h2>

                        <!-- Warning -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        تحذير: هذا الإجراء نهائي
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>
                                            بعد إغلاق السنة الدراسية، لن يمكن إجراء أي تعديلات إلا عبر نظام التعديلات (Amendments).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Year Details -->
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 mb-6">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">السنة الدراسية</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $year->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">الحالة الحالية</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        نشطة
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        <!-- Error Message -->
                        @if ($closingError)
                            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                                <p class="text-sm text-red-600">{{ $closingError }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex justify-between pt-4 border-t border-gray-200">
                            @if (!$isClosing)
                                <button
                                    wire:click="previousStep"
                                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    السابق
                                </button>

                                <button
                                    wire:click="closeYear"
                                    wire:confirm="هل أنت متأكد من إغلاق السنة الدراسية؟ لا يمكن التراجع عن هذا الإجراء."
                                    class="inline-flex items-center px-6 py-2 bg-red-600 border border-transparent rounded-md font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    تأكيد إغلاق السنة
                                </button>
                            @else
                                <!-- Loading State -->
                                <div class="flex items-center justify-center w-full">
                                    <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="ml-3 text-gray-600">جاري إغلاق السنة الدراسية...</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                @break
        @endswitch
    </div>
</div>
