<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8" dir="rtl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $isEditing ? 'تعديل الدور' : 'إنشاء دور جديد' }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $isEditing ? 'تعديل صلاحيات واسم الدور الحالي.' : 'قم بتحديد اسم الدور والصلاحيات الممنوحة له.' }}
            </p>
        </div>
        <a href="{{ route('admin.access.roles.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
            <i class="fas fa-arrow-right"></i> عودة للقائمة
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 space-y-8">
            
            <!-- Role Name Section -->
            <div class="max-w-xl">
                <label class="block text-base font-semibold text-gray-900 dark:text-white mb-2">اسم الدور الوظيفي</label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-id-badge text-gray-400"></i>
                    </div>
                    <input type="text" wire:model="name" 
                        class="block w-full pr-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-3"
                        placeholder="مثال: مدير الموارد البشرية"
                        {{ $isEditing && in_array($name, $staticRoles, true) ? 'disabled' : '' }}>
                </div>
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @if($isEditing && in_array($name, $staticRoles, true))
                    <p class="mt-2 text-xs text-yellow-600 bg-yellow-50 p-2 rounded border border-yellow-200">
                        <i class="fas fa-info-circle ml-1"></i> هذا دور أساسي في النظام لا يمكن تغيير اسمه.
                    </p>
                @endif
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            <!-- Permissions Matrix -->
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-key text-blue-500"></i>
                    مصفوفة الصلاحيات
                </h3>
                
                <div class="space-y-6">
                    @foreach($groupedPermissions as $group => $permissions)
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h4 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                                    وحدة {{ $permissionGroupMap[$group] ?? ucfirst($group) }}
                                </h4>
                                <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                                    {{ count($permissions) }} صلاحية
                                </span>
                            </div>
                            
                            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($permissions as $perm)
                                    <label class="relative flex items-start p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-white dark:hover:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-500 cursor-pointer transition-all duration-200 group">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm->name }}" 
                                                class="focus:ring-blue-500 h-5 w-5 text-blue-600 border-gray-300 rounded transition duration-150 ease-in-out">
                                        </div>
                                        <div class="mr-3 text-sm">
                                            <span class="font-medium text-gray-700 dark:text-gray-200 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
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

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.access.roles.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                    إلغاء
                </a>
                <button wire:click="save" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-blue-500/30 transition font-medium flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    {{ $isEditing ? 'حفظ التعديلات' : 'إنشاء الدور' }}
                </button>
            </div>

        </div>
    </div>
</div>
