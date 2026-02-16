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

<svg class="illustration-students {{ $sizeClass }}" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-student-1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
        </linearGradient>
        <linearGradient id="grad-student-2" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#ec4899;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#f43f5e;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <circle cx="100" cy="100" r="80" fill="url(#grad-student-1)" opacity="0.1" class="animate-pulse-slow"/>
    
    <g class="student-avatar hover:scale-110 transition-transform cursor-pointer">
        <circle cx="80" cy="90" r="25" fill="url(#grad-student-1)"/>
        <circle cx="80" cy="85" r="12" fill="white" opacity="0.9"/>
        <path d="M 65 105 Q 80 115, 95 105" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
    </g>
    
    <g class="student-avatar hover:scale-110 transition-transform cursor-pointer" style="animation-delay: 0.2s">
        <circle cx="120" cy="90" r="25" fill="url(#grad-student-2)"/>
        <circle cx="120" cy="85" r="12" fill="white" opacity="0.9"/>
        <path d="M 105 105 Q 120 115, 135 105" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
    </g>
    
    <g class="floating-book animate-float">
        <rect x="60" y="130" width="20" height="15" rx="2" fill="#fbbf24" opacity="0.8"/>
        <line x1="70" y1="130" x2="70" y2="145" stroke="white" stroke-width="1" opacity="0.5"/>
    </g>
    
    <g class="floating-book animate-float" style="animation-delay: 0.5s">
        <rect x="120" y="135" width="20" height="15" rx="2" fill="#34d399" opacity="0.8"/>
        <line x1="130" y1="135" x2="130" y2="150" stroke="white" stroke-width="1" opacity="0.5"/>
    </g>
</svg>
