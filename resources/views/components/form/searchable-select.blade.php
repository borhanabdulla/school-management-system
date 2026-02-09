@props([
    'name',
    'label',
    'options' => [],
    'selected' => null,
    'placeholder' => 'ابحث...',
    'optionLabel' => 'name',
    'optionValue' => 'id',
    'required' => false,
])

@php
    $wireModel = $attributes->wire('model')->value();
@endphp

<div x-data="searchableSelect({
    options: @json($options),
    selected: @js($selected),
    placeholder: '{{ $placeholder }}',
    optionLabel: '{{ $optionLabel }}',
    optionValue: '{{ $optionValue }}',
    wireModel: '{{ $wireModel }}'
})" class="relative">
    <label class="block text-sm font-medium text-slate-600 dark:text-slate-200/80 mb-2">
        {{ $label }}
        @if ($required)
            <span class="text-red-400">*</span>
        @endif
    </label>
    <div class="relative">
        <input type="text" x-model="search" @click="open = true" @click.away="open = false" @keydown.escape="open = false"
            @keydown.arrow-down.prevent="highlightNext()" @keydown.arrow-up.prevent="highlightPrev()"
            @keydown.enter.prevent="selectHighlighted()" :placeholder="placeholder"
            class="w-full px-5 py-4 bg-white/80 dark:bg-surface/80 border border-gray-200 dark:border-border rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60 transition-all backdrop-blur-sm" />

        <div x-show="open" x-transition
            class="absolute z-50 w-full max-h-60 overflow-auto bg-white dark:bg-surface/95 backdrop-blur-md border border-gray-200 dark:border-border rounded-xl mt-1 shadow-2xl">
            <template x-if="filteredOptions.length > 0">
                <ul>
                    <template x-for="(option, index) in filteredOptions" :key="option[optionValue]">
                        <li @click="select(option)" @mouseenter="highlight = index"
                            :class="{ 'bg-primary/15': highlight === index }"
                            class="px-4 py-3 cursor-pointer text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            x-text="option[optionLabel]">
                        </li>
                    </template>
                </ul>
            </template>
            <template x-if="filteredOptions.length === 0">
                <div class="px-4 py-3 text-gray-400 text-center">لا توجد نتائج</div>
            </template>
        </div>
    </div>
    @error($name)
        <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
    @enderror
</div>

<script>
    function searchableSelect({
        options,
        selected,
        placeholder,
        optionLabel,
        optionValue,
        wireModel
    }) {
        return {
            open: false,
            search: selected ? selected[optionLabel] : '',
            selected: selected,
            options: options,
            placeholder: placeholder,
            optionLabel: optionLabel,
            optionValue: optionValue,
            wireModel: wireModel,
            highlight: -1,

            init() {
                // Watch for changes and update Livewire
                this.$watch('selected', (value) => {
                    if (value && this.wireModel) {
                        this.$wire.set(this.wireModel, value[this.optionValue]);
                    }
                });
            },

            get filteredOptions() {
                if (!this.search) return this.options;
                return this.options.filter(o =>
                    String(o[this.optionLabel]).toLowerCase().includes(this.search.toLowerCase())
                );
            },

            select(option) {
                this.selected = option;
                this.search = option[this.optionLabel];
                this.open = false;
            },

            highlightNext() {
                if (this.highlight < this.filteredOptions.length - 1) {
                    this.highlight++;
                }
            },

            highlightPrev() {
                if (this.highlight > 0) {
                    this.highlight--;
                }
            },

            selectHighlighted() {
                if (this.highlight >= 0 && this.filteredOptions[this.highlight]) {
                    this.select(this.filteredOptions[this.highlight]);
                }
            }
        };
    }
</script>
