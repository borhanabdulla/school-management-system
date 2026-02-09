<div class="min-h-screen bg-background" x-data="{ activeTab: @entangle('activeTab') }">
    {{-- Loading State --}}
    <div wire:loading.delay class="fixed inset-0 bg-background/60 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-surface border border-border rounded-lg p-6 shadow-xl">
            <div class="flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-foreground">جاري التحميل...</span>
            </div>
        </div>
    </div>

    {{-- Notification System handled by Layout --}}

    {{-- Header Section --}}
    <x-student.profile-header :student="$student" :canDelete="$canDelete" :deleteBlockers="$deleteBlockers" />

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <x-student.stats-grid :stats="$this->stats" />
        </div>

        <!-- Tabs Navigation -->
        <x-student.tabs :activeTab="$activeTab" />

        <!-- Tab Content -->
        <div class="pb-12">
            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" x-transition.opacity>
                <x-student.tab-content.profile :student="$student" />
            </div>

            <!-- Academic Tab -->
            <div x-show="activeTab === 'academic'" x-transition.opacity>
                <x-student.tab-content.academic :enrollments="$student->enrollments" />
            </div>

            <!-- Guardians Tab -->
            <div x-show="activeTab === 'guardians'" x-transition.opacity>
                <x-student.tab-content.guardians :guardians="$student->guardians" />
            </div>

            <!-- Grades Tab -->
            <div x-show="activeTab === 'grades'" x-transition.opacity>
                <x-student.tab-content.grades 
                    :performanceSummary="$performanceSummary"
                    :detailedCourses="$detailedCourses"
                    :recommendations="$recommendations"
                />
            </div>
        </div>

    {{-- Edit Student Modal --}}
    <x-ui.modal wire:model="showEditModal" maxWidth="2xl">
        <div class="bg-surface rounded-lg">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-border bg-surface/50">
            <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-foreground flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        تعديل بيانات الطالب
                    </h3>
                    <button wire:click="$set('showEditModal', false)"
                        class="text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <form wire:submit.prevent="update">
                <div class="px-6 py-6 space-y-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    {{-- Success/Error Messages --}}
                    {{-- Personal Information Section --}}
                    <div class="space-y-4">
                        <h4
                            class="text-sm font-semibold text-foreground flex items-center gap-2 pb-2 border-b border-border">
                            <span class="w-1 h-5 bg-primary rounded-full"></span>
                            المعلومات الشخصية
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- First Name --}}
                            <div>
                                <label for="first_name_ar"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    الاسم الأول <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="first_name_ar" wire:model="form.first_name_ar"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.first_name_ar') border-danger @enderror"
                                    placeholder="أدخل الاسم الأول">
                                @error('form.first_name_ar')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Family Name --}}
                            <div>
                                <label for="family_name_ar"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    اسم العائلة <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="family_name_ar" wire:model="form.family_name_ar"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.family_name_ar') border-danger @enderror"
                                    placeholder="أدخل اسم العائلة">
                                @error('form.family_name_ar')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date of Birth --}}
                            <div>
                                <label for="date_of_birth"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    تاريخ الميلاد <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="date_of_birth" wire:model="form.date_of_birth"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.date_of_birth') border-danger @enderror">
                                @error('form.date_of_birth')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Gender --}}
                            <div>
                                <label for="gender"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    الجنس <span class="text-danger">*</span>
                                </label>
                                <select id="gender" wire:model="form.gender"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.gender') border-danger @enderror">
                                    <option value="">اختر الجنس</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                                @error('form.gender')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nationality --}}
                            <div>
                                <label for="nationality_id"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    الجنسية
                                </label>
                                <select id="nationality_id" wire:model="form.nationality_id"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.nationality_id') border-danger @enderror">
                                    <option value="">اختر الجنسية</option>
                                    @foreach (app(\App\Domains\Shared\Services\SharedLookupService::class)->getCountries() as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.nationality_id')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Blood Type --}}
                            <div>
                                <label for="blood_type"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    فصيلة الدم
                                </label>
                                <select id="blood_type" wire:model="form.blood_type"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    <option value="">اختر فصيلة الدم</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                                @error('form.blood_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- National ID --}}
                            <div class="md:col-span-2">
                                <label for="national_id"
                                    class="block text-sm font-medium text-foreground mb-2">
                                    الرقم القومي
                                </label>
                                <input type="text" id="national_id" wire:model="form.national_id"
                                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('form.national_id') border-danger @enderror"
                                    placeholder="أدخل الرقم القومي">
                                @error('form.national_id')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div
                    class="px-6 py-4 bg-background/50 border-t border-border flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showEditModal', false)"
                        class="px-5 py-2.5 text-sm font-medium text-foreground bg-surface border border-border rounded-lg hover:bg-background focus:ring-4 focus:ring-border transition-all">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 focus:ring-4 focus:ring-primary/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>
