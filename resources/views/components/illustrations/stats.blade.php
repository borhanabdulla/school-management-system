@props(['size' => 'md'])

@php
$sizeClasses = [
    'sm' => 'w-16 h-16',
    'md' => 'w-24 h-24',
    'lg' => 'w-32 h-32',
    'xl' => 'w-48 h-48',
];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<svg class="illustration-stats {{ $sizeClass }}" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-bar-1" x1="0%" y1="100%" x2="0%" y2="0%">
            <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="grad-bar-2" x1="0%" y1="100%" x2="0%" y2="0%">
            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#34d399;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="grad-bar-3" x1="0%" y1="100%" x2="0%" y2="0%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#fbbf24;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <rect x="50" y="90" width="25" height="60" rx="3" fill="url(#grad-bar-1)" class="bar-grow-up"/>
    <rect x="87.5" y="70" width="25" height="80" rx="3" fill="url(#grad-bar-2)" class="bar-grow-up" style="animation-delay: 0.2s"/>
    <rect x="125" y="100" width="25" height="50" rx="3" fill="url(#grad-bar-3)" class="bar-grow-up" style="animation-delay: 0.4s"/>
    
    <polyline points="62.5,80 100,60 137.5,90" 
              stroke="#ec4899" stroke-width="3" fill="none" 
              stroke-linecap="round" stroke-linejoin="round"
              stroke-dasharray="150" stroke-dashoffset="150" class="trend-line"/>
    
    <circle cx="62.5" cy="80" r="4" fill="#ec4899" class="data-point" style="animation-delay: 0.6s"/>
    <circle cx="100" cy="60" r="4" fill="#ec4899" class="data-point" style="animation-delay: 0.8s"/>
    <circle cx="137.5" cy="90" r="4" fill="#ec4899" class="data-point" style="animation-delay: 1s"/>
</svg>
