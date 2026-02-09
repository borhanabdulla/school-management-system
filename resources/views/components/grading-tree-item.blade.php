@props(['category', 'level' => 0])

<div class="relative" style="margin-right: {{ $level * 20 }}px">
    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow mb-2">
        <div class="flex items-center gap-3">
            <!-- Drag Handle (Visual only for now) -->
            <div class="text-gray-400 cursor-move">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
            </div>
            
            <div>
                <div class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    {{ $category->name }}
                    @if($category->is_locked)
                        <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200" title="مقفل من الإدارة">🔒 مقفل</span>
                    @endif
                    @if($category->is_dynamic_weight)
                        <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded border border-blue-200" title="وزن ديناميكي">⚡ ديناميكي</span>
                    @endif
                    @if($category->is_final_exam)
                        <span class="text-xs bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded border border-purple-200" title="اختبار نهائي">🎯 نهائي</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 flex gap-3 mt-1">
                    <span>الوزن: <strong>{{ $category->weight }}</strong></span>
                    <span>النوع: {{ $category->calculation_type }}</span>
                    <span>الربط: {{ $category->mapping_type ?? 'manual' }}</span>
                    @if($category->max_raw_score)
                        <span>(العظمى: {{ $category->max_raw_score }})</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="openCategoryForm({{ $category->id }})" class="p-1 text-green-600 hover:bg-green-50 rounded" title="إضافة فرع">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </button>
            <button wire:click="openCategoryForm({{ $category->parent_id ?? 'null' }}, {{ $category->id }})" class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="تعديل">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </button>
            <button wire:click="deleteCategory({{ $category->id }})" class="p-1 text-red-600 hover:bg-red-50 rounded" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذه الفئة وفروعها؟')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    </div>

    <!-- Recursive Children -->
    @if($category->children->count() > 0)
        <div class="border-r-2 border-gray-200 dark:border-gray-700 mr-4 pr-2">
            @foreach($category->children as $child)
                <x-grading-tree-item :category="$child" :level="$level + 1" />
            @endforeach
        </div>
    @endif
</div>
