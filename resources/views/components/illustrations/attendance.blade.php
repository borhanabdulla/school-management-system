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

<svg class="illustration-attendance {{ $sizeClass }}" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-check" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <circle cx="100" cy="100" r="60" fill="url(#grad-check)" class="scale-animation"/>
    
    <path class="checkmark-path" d="M 70 100 L 90 120 L 130 80" 
          stroke="white" stroke-width="8" fill="none" 
          stroke-linecap="round" stroke-linejoin="round"
          stroke-dasharray="100" stroke-dashoffset="100"/>
    
    <circle cx="60" cy="60" r="3" fill="#10b981" class="particle particle-1"/>
    <circle cx="140" cy="70" r="2" fill="#34d399" class="particle particle-2"/>
    <circle cx="150" cy="130" r="3" fill="#10b981" class="particle particle-3"/>
    <circle cx="50" cy="140" r="2" fill="#34d399" class="particle particle-4"/>
</svg>
