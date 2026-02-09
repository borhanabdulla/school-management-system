@props(['title', 'message'])

<div class="col-span-full">
    <div
        class="flex flex-col items-center justify-center p-12 bg-surface rounded-2xl border border-border border-dashed">
        <div class="w-24 h-24 bg-primary/5 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-primary mb-2">{{ $title }}</h3>
        <p class="text-secondary mb-6 text-center max-w-md">{{ $message }}</p>
        @if (isset($action))
            {{ $action }}
        @endif
    </div>
</div>
