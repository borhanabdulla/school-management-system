<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700" dir="rtl">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">تعيين الأدوار</h3>
    
    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($availableRoles as $role)
                <label class="relative flex items-start p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition">
                    <div class="flex items-center h-5">
                        <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}" 
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="mr-3 text-sm">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $roleMap[$role->name] ?? $role->name }}</span>
                        <p class="text-gray-500 dark:text-gray-400 text-xs">
                            {{ $role->permissions_count }} صلاحية
                        </p>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex justify-end pt-4">
            <button wire:click="updateRoles" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                تحديث الأدوار
            </button>
        </div>
    </div>
</div>
