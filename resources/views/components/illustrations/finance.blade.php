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

<svg class="illustration-finance {{ $sizeClass }}" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-coin" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <ellipse cx="100" cy="140" rx="40" ry="8" fill="#d97706" opacity="0.3"/>
    
    <g class="coin-stack">
        <ellipse cx="100" cy="130" rx="35" ry="7" fill="url(#grad-coin)"/>
        <ellipse cx="100" cy="130" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="133" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
        
        <ellipse cx="100" cy="110" rx="35" ry="7" fill="url(#grad-coin)" class="coin-float"/>
        <ellipse cx="100" cy="110" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="113" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
        
        <ellipse cx="100" cy="90" rx="35" ry="7" fill="url(#grad-coin)" class="coin-float" style="animation-delay: 0.2s"/>
        <ellipse cx="100" cy="90" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="93" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
    </g>
    
    <path d="M 140 120 L 160 80 L 150 85 M 160 80 L 155 90" 
          stroke="#10b981" stroke-width="4" fill="none" 
          stroke-linecap="round" class="arrow-grow"/>
</svg>
