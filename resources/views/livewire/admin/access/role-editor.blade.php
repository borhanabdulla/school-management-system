<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" dir="rtl">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-user-shield text-white text-lg"></i>
                </span>
                {{ $isEditing ? 'تعديل الدور: ' . $name : 'إنشاء دور جديد' }}
            </h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2 mr-14">
                {{ $isEditing ? 'إدارة الصلاحيات والأعضاء لهذا الدور.' : 'قم بتحديد اسم الدور والصلاحيات الممنوحة له.' }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.access.roles.index') }}" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-right"></i> عودة للقائمة
            </a>
            @if($isEditing)
                <button wire:click="save" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg transition font-medium flex items-center gap-2">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden min-h-[600px]">
        
        {{-- Tabs Navigation --}}
        <div class="flex border-b border-gray-100 dark:border-gray-700 overflow-x-auto">
            <button wire:click="setTab('permissions')" 
                class="px-8 py-4 font-bold text-sm transition focus:outline-none flex items-center gap-2 border-b-2 {{ $activeTab === 'permissions' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                <i class="fas fa-key"></i> الصلاحيات والإعدادات
            </button>
            @if($isEditing)
                <button wire:click="setTab('members')" 
                    class="px-8 py-4 font-bold text-sm transition focus:outline-none flex items-center gap-2 border-b-2 {{ $activeTab === 'members' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                    <i class="fas fa-users"></i> الأعضاء <span class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full text-xs ml-1">{{ $role->users()->count() }}</span>
                </button>
            @else
                <button disabled class="px-8 py-4 font-bold text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed flex items-center gap-2 border-b-2 border-transparent">
                    <i class="fas fa-users"></i> الأعضاء (بعد الحفظ)
                </button>
            @endif
        </div>

        <div class="p-8">
            {{-- Permissions Tab --}}
            <div x-show="$wire.activeTab === 'permissions'" class="space-y-8 animate-fade-in">
                <!-- Role Name Section -->
                <div class="max-w-2xl">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">اسم الدور الوظيفي <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-badge text-gray-400"></i>
                        </div>
                        <input type="text" wire:model="name" 
                            class="block w-full pr-10 rounded-xl border-gray-200 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white py-3 transition"
                            placeholder="مثال: مدير الموارد البشرية"
                            {{ $isEditing && in_array($name, $staticRoles, true) ? 'disabled' : '' }}>
                    </div>
                    @error('name') <p class="mt-1 text-sm text-red-600 font-medium">{{ $message }}</p> @enderror
                    @if($isEditing && in_array($name, $staticRoles, true))
                        <div class="mt-3 flex gap-2 p-3 bg-amber-50 dark:bg-amber-900/10 text-amber-800 dark:text-amber-400 rounded-lg border border-amber-100 dark:border-amber-800 text-sm">
                            <i class="fas fa-lock mt-0.5"></i>
                            <p>هذا دور أساسي في النظام لا يمكن تغيير اسمه، ولكن يمكنك تعديل صلاحياته.</p>
                        </div>
                    @endif
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <!-- Permissions Matrix -->
                <div>
                    <div class="mb-6 flex justify-between items-end">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-shield-alt text-indigo-500"></i>
                            مصفوفة الصلاحيات
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">حدد الصلاحيات التي يمنحها هذا الدور</span>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:border-indigo-300 dark:hover:border-indigo-700 transition duration-300">
                                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                        وحدة {{ $permissionGroupMap[$group] ?? ucfirst($group) }}
                                    </h4>
                                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">
                                        {{ count($permissions) }} PERMISSIONS
                                    </span>
                                </div>
                                
                                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach($permissions as $perm)
                                        <label class="relative flex items-start p-3 rounded-lg border border-gray-100 dark:border-gray-700/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 hover:border-indigo-200 dark:hover:border-indigo-800 cursor-pointer transition-all duration-200 group">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->name }}" 
                                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition duration-150 ease-in-out">
                                            </div>
                                            <div class="mr-3 text-sm">
                                                <span class="font-medium text-gray-600 dark:text-gray-300 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                                    {{ $permissionMap[$perm->name] ?? $perm->name }}
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Members Tab --}}
            <div x-show="$wire.activeTab === 'members'" class="space-y-6 animate-fade-in" x-cloak>
                @if($isEditing)
                    <!-- Add Member Search -->
                    <div class="max-w-xl mx-auto mb-10">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 text-center">إضافة عضو جديد للدور</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 group-focus-within:text-indigo-500 transition"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchMember"
                                class="block w-full pr-11 pl-4 py-3 rounded-xl border-gray-200 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-center transition"
                                placeholder="ابحث بالاسم أو البريد الإلكتروني...">
                        </div>
                        
                        <!-- Search Results -->
                        @if(strlen($searchMember) > 2 && $foundUsers && $foundUsers->count() > 0)
                            <div class="mt-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-lg overflow-hidden absolute z-50 w-full max-w-xl">
                                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-500">
                                    نتائج البحث ({{ $foundUsers->count() }})
                                </div>
                                @foreach($foundUsers as $user)
                                    <div class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                                                {{ substr($user->first_name ?? $user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="addMember({{ $user->id }})" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition">
                                            إضافة
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(strlen($searchMember) > 2)
                             <div class="mt-2 text-center text-gray-400 text-sm">لا توجد نتائج مطابقة للإضافة</div>
                        @endif
                    </div>

                    <!-- Current Members List -->
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <i class="fas fa-users-cog text-indigo-500"></i>
                            الأعضاء الحاليين
                        </h3>

                        @if($members && $members->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($members as $member)
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold">
                                                {{ substr($member->first_name ?? $member->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $member->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="removeMember({{ $member->id }})" 
                                                wire:confirm="هل أنت متأكد من إزالة الصلاحية من هذا العضو؟"
                                                class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="إزالة">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                {{ $members->links() }}
                            </div>
                        @else
                             <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-users-slash text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">لا يوجد أعضاء في هذا الدور حالياً</p>
                                <p class="text-xs text-gray-400 mt-1">استخدم البحث أعلاه لإضافة أعضاء</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-20">
                        <p class="text-gray-500 dark:text-gray-400 text-lg">يرجى حفظ الدور أولاً للتمكن من إدارة الأعضاء.</p>
                    </div>
                @endif
            </div>

            @if(!$isEditing)
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button wire:click="save" class="px-8 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow-lg hover:shadow-indigo-500/30 transition font-bold text-lg flex items-center gap-2">
                        <i class="fas fa-check"></i> إنشاء الدور
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
