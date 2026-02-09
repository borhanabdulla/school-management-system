<div class="p-6" dir="rtl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">الصلاحيات والأدوار</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">إدارة أدوار النظام ومستويات الوصول.</p>
        </div>
        <a href="{{ route('admin.access.roles.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <i class="fas fa-plus"></i> إنشاء دور جديد
        </a>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-lg {{ in_array($role->name, ['Super Admin', 'super_admin', 'Admin', 'admin'], true) ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                            <i class="fas fa-user-shield text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white">{{ $roleMap[$role->name] ?? $role->name }}</h3>
                            <span class="text-xs text-gray-500">{{ $role->users_count ?? 0 }} مستخدم</span>
                        </div>
                    </div>
                    @if(in_array($role->name, $staticRoles, true))
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full border border-gray-200">
                            <i class="fas fa-lock ml-1"></i> نظام
                        </span>
                    @else
                        <div class="flex gap-2">
                            <a href="{{ route('admin.access.roles.edit', $role->id) }}" class="text-gray-400 hover:text-blue-600 transition">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button wire:click="delete({{ $role->id }})" class="text-gray-400 hover:text-red-600 transition" onclick="confirm('هل أنت متأكد من الحذف؟') || event.stopImmediatePropagation()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
                
                <div class="space-y-2">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">أهم الصلاحيات</div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($role->permissions->take(4) as $perm)
                            <span class="px-2 py-1 text-xs bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded border border-gray-200 dark:border-gray-600">
                                {{ $permissionMap[$perm->name] ?? $perm->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">لا توجد صلاحيات محددة</span>
                        @endforelse
                        @if($role->permissions->count() > 4)
                            <span class="px-2 py-1 text-xs bg-gray-50 text-gray-500 rounded border border-gray-200">
                                +{{ $role->permissions->count() - 4 }} المزيد
                            </span>
                        @endif
                    </div>
                </div>

                @if(!in_array($role->name, $staticRoles, true))
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <a href="{{ route('admin.access.roles.edit', $role->id) }}" class="text-sm text-blue-600 hover:underline">إدارة الصلاحيات</a>
                    </div>
                @elseif(!in_array($role->name, ['Super Admin', 'super_admin'], true))
                     <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                        <a href="{{ route('admin.access.roles.edit', $role->id) }}" class="text-sm text-blue-600 hover:underline">عرض الصلاحيات</a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
