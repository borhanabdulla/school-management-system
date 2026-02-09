@props(['title', 'description', 'variant' => 'default', 'size' => 'default'])

@php
    $isSoft = $variant === 'soft';
    $isCompact = $size === 'compact';
    $isTiny = $size === 'tiny';
    $wrapperClasses = $isSoft
        ? ($isTiny
            ? 'relative overflow-hidden bg-gradient-to-r from-background to-surface border border-border rounded-2xl shadow-sm p-2'
            : ($isCompact
                ? 'relative overflow-hidden bg-gradient-to-r from-background to-surface border border-border rounded-2xl shadow-md p-5'
                : 'relative overflow-hidden bg-gradient-to-r from-background to-surface border border-border rounded-2xl shadow-lg p-6'))
        : 'relative overflow-hidden bg-gradient-to-r from-primary to-accent border border-transparent dark:border-border rounded-2xl shadow-xl p-8 transition-all duration-500';
    $overlayClasses = $isSoft
        ? 'absolute inset-0 bg-gradient-to-r from-primary/5 to-accent/5'
        : 'absolute inset-0 bg-black opacity-10 dark:opacity-30';
    $bubbleRight = $isSoft ? 'bg-primary/10' : 'bg-white opacity-5';
    $bubbleLeft = $isSoft ? 'bg-accent/10' : 'bg-black opacity-5';
    $titleClasses = $isSoft
        ? ($isTiny
            ? 'text-base font-bold text-foreground mb-1 flex items-center gap-2'
            : ($isCompact
                ? 'text-xl font-bold text-foreground mb-1 flex items-center gap-3'
                : 'text-2xl font-bold text-foreground mb-1 flex items-center gap-3'))
        : 'text-3xl font-bold text-white mb-2 flex items-center gap-3';
    $descClasses = $isSoft
        ? ($isTiny
            ? 'text-muted-foreground text-xs font-medium'
            : ($isCompact ? 'text-muted-foreground text-xs font-medium' : 'text-muted-foreground text-sm font-medium'))
        : 'text-white/90 text-lg font-medium';
    $iconWrapClasses = $isSoft
        ? ($isTiny
            ? 'p-1 bg-surface/70 border border-border rounded-lg inline-flex items-center justify-center w-8 h-8'
            : ($isCompact
                ? 'p-1.5 bg-surface/70 border border-border rounded-lg inline-flex items-center justify-center w-10 h-10'
                : 'p-2 bg-surface/70 border border-border rounded-lg inline-flex items-center justify-center w-12 h-12'))
        : 'p-2 bg-white/20 rounded-lg backdrop-blur-sm inline-flex items-center justify-center w-12 h-12';
@endphp

<div class="{{ $wrapperClasses }}">
    <div class="{{ $overlayClasses }}"></div>
    <div class="absolute -right-10 -top-10 w-64 h-64 {{ $bubbleRight }} rounded-full blur-3xl"></div>
    <div class="absolute -left-10 -bottom-10 w-64 h-64 {{ $bubbleLeft }} rounded-full blur-3xl"></div>

    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="{{ $titleClasses }}">
                @if (isset($icon))
                    <span
                        class="{{ $iconWrapClasses }}">
                        {{ $icon }}
                    </span>
                @endif
                {{ $title }}
            </h2>
            <p class="{{ $descClasses }}">{{ $description }}</p>
        </div>
        @if (isset($actions))
            {{ $actions }}
        @endif
    </div>
</div>
