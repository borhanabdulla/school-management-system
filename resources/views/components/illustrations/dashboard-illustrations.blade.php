<!-- ====================================
     رسوم توضيحية تفاعلية للداشبورد
     ==================================== -->

<!-- 1. رسم توضيحي للطلاب (Students Illustration) -->
<svg class="illustration-students" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
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
    
    <!-- Background circle with pulse -->
    <circle cx="100" cy="100" r="80" fill="url(#grad-student-1)" opacity="0.1" class="animate-pulse-slow"/>
    
    <!-- Student 1 -->
    <g class="student-avatar hover:scale-110 transition-transform cursor-pointer">
        <circle cx="80" cy="90" r="25" fill="url(#grad-student-1)"/>
        <circle cx="80" cy="85" r="12" fill="white" opacity="0.9"/>
        <path d="M 65 105 Q 80 115, 95 105" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
    </g>
    
    <!-- Student 2 -->
    <g class="student-avatar hover:scale-110 transition-transform cursor-pointer" style="animation-delay: 0.2s">
        <circle cx="120" cy="90" r="25" fill="url(#grad-student-2)"/>
        <circle cx="120" cy="85" r="12" fill="white" opacity="0.9"/>
        <path d="M 105 105 Q 120 115, 135 105" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
    </g>
    
    <!-- Floating books -->
    <g class="floating-book animate-float">
        <rect x="60" y="130" width="20" height="15" rx="2" fill="#fbbf24" opacity="0.8"/>
        <line x1="70" y1="130" x2="70" y2="145" stroke="white" stroke-width="1" opacity="0.5"/>
    </g>
    
    <g class="floating-book animate-float" style="animation-delay: 0.5s">
        <rect x="120" y="135" width="20" height="15" rx="2" fill="#34d399" opacity="0.8"/>
        <line x1="130" y1="135" x2="130" y2="150" stroke="white" stroke-width="1" opacity="0.5"/>
    </g>
</svg>

<!-- 2. رسم توضيحي للحضور (Attendance Illustration) -->
<svg class="illustration-attendance" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-check" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Checkmark circle with animation -->
    <circle cx="100" cy="100" r="60" fill="url(#grad-check)" class="scale-animation"/>
    
    <!-- Animated checkmark -->
    <path class="checkmark-path" d="M 70 100 L 90 120 L 130 80" 
          stroke="white" stroke-width="8" fill="none" 
          stroke-linecap="round" stroke-linejoin="round"
          stroke-dasharray="100" stroke-dashoffset="100"/>
    
    <!-- Particles -->
    <circle cx="60" cy="60" r="3" fill="#10b981" class="particle particle-1"/>
    <circle cx="140" cy="70" r="2" fill="#34d399" class="particle particle-2"/>
    <circle cx="150" cy="130" r="3" fill="#10b981" class="particle particle-3"/>
    <circle cx="50" cy="140" r="2" fill="#34d399" class="particle particle-4"/>
</svg>

<!-- 3. رسم توضيحي للمالية (Finance Illustration) -->
<svg class="illustration-finance" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-coin" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Coin stack with 3D effect -->
    <ellipse cx="100" cy="140" rx="40" ry="8" fill="#d97706" opacity="0.3"/>
    
    <g class="coin-stack">
        <!-- Coin 1 -->
        <ellipse cx="100" cy="130" rx="35" ry="7" fill="url(#grad-coin)"/>
        <ellipse cx="100" cy="130" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="133" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
        
        <!-- Coin 2 -->
        <ellipse cx="100" cy="110" rx="35" ry="7" fill="url(#grad-coin)" class="coin-float"/>
        <ellipse cx="100" cy="110" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="113" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
        
        <!-- Coin 3 -->
        <ellipse cx="100" cy="90" rx="35" ry="7" fill="url(#grad-coin)" class="coin-float" style="animation-delay: 0.2s"/>
        <ellipse cx="100" cy="90" rx="35" ry="7" fill="white" opacity="0.2"/>
        <text x="100" y="93" text-anchor="middle" fill="white" font-size="12" font-weight="bold">$</text>
    </g>
    
    <!-- Growth arrow -->
    <path d="M 140 120 L 160 80 L 150 85 M 160 80 L 155 90" 
          stroke="#10b981" stroke-width="4" fill="none" 
          stroke-linecap="round" class="arrow-grow"/>
</svg>

<!-- 4. رسم توضيحي للإنذارات (Alerts Illustration) -->
<svg class="illustration-alerts" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-alert" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Alert triangle with pulse -->
    <polygon points="100,60 140,130 60,130" fill="url(#grad-alert)" class="alert-pulse"/>
    <polygon points="100,60 140,130 60,130" fill="white" opacity="0.1"/>
    
    <!-- Exclamation mark -->
    <line x1="100" y1="85" x2="100" y2="110" stroke="white" stroke-width="6" stroke-linecap="round"/>
    <circle cx="100" cy="120" r="3" fill="white"/>
    
    <!-- Warning waves -->
    <circle cx="100" cy="100" r="50" stroke="#ef4444" stroke-width="2" fill="none" opacity="0" class="warning-wave wave-1"/>
    <circle cx="100" cy="100" r="50" stroke="#ef4444" stroke-width="2" fill="none" opacity="0" class="warning-wave wave-2"/>
    <circle cx="100" cy="100" r="50" stroke="#ef4444" stroke-width="2" fill="none" opacity="0" class="warning-wave wave-3"/>
</svg>

<!-- 5. رسم توضيحي للمعلمين (Teachers Illustration) -->
<svg class="illustration-teachers" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-teacher" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#8b5cf6;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#6366f1;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Blackboard -->
    <rect x="50" y="60" width="100" height="70" rx="5" fill="#1f2937" stroke="#374151" stroke-width="3"/>
    
    <!-- Math equation with animation -->
    <text x="100" y="85" text-anchor="middle" fill="#10b981" font-size="14" font-weight="bold" class="equation-fade">2 + 2 = 4</text>
    <text x="100" y="105" text-anchor="middle" fill="#f59e0b" font-size="14" font-weight="bold" class="equation-fade" style="animation-delay: 0.5s">√16 = 4</text>
    
    <!-- Teacher avatar -->
    <g class="teacher-avatar">
        <circle cx="100" cy="150" r="20" fill="url(#grad-teacher)"/>
        <circle cx="100" cy="145" r="8" fill="white" opacity="0.9"/>
        <path d="M 88 160 Q 100 168, 112 160" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
        
        <!-- Graduation cap -->
        <polygon points="100,125 115,130 100,135 85,130" fill="#1f2937"/>
        <rect x="98" y="135" width="4" height="8" fill="#1f2937"/>
    </g>
</svg>

<!-- 6. رسم توضيحي للإحصائيات (Stats Illustration) -->
<svg class="illustration-stats" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
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
    
    <!-- Animated bars -->
    <rect x="50" y="150" width="25" height="0" rx="3" fill="url(#grad-bar-1)" class="bar-grow" data-height="60"/>
    <rect x="87.5" y="150" width="25" height="0" rx="3" fill="url(#grad-bar-2)" class="bar-grow" data-height="80" style="animation-delay: 0.2s"/>
    <rect x="125" y="150" width="25" height="0" rx="3" fill="url(#grad-bar-3)" class="bar-grow" data-height="50" style="animation-delay: 0.4s"/>
    
    <!-- Trend line -->
    <polyline points="62.5,120 100,100 137.5,130" 
              stroke="#ec4899" stroke-width="3" fill="none" 
              stroke-linecap="round" stroke-linejoin="round"
              stroke-dasharray="150" stroke-dashoffset="150" class="trend-line"/>
    
    <!-- Data points -->
    <circle cx="62.5" cy="120" r="4" fill="#ec4899" class="data-point" style="animation-delay: 0.6s"/>
    <circle cx="100" cy="100" r="4" fill="#ec4899" class="data-point" style="animation-delay: 0.8s"/>
    <circle cx="137.5" cy="130" r="4" fill="#ec4899" class="data-point" style="animation-delay: 1s"/>
</svg>

<!-- 7. رسم توضيحي للتقويم (Calendar Illustration) -->
<svg class="illustration-calendar" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad-calendar" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#ec4899;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
        </linearGradient>
    </defs>
    
    <!-- Calendar body -->
    <rect x="60" y="70" width="80" height="80" rx="8" fill="white" stroke="url(#grad-calendar)" stroke-width="3"/>
    
    <!-- Calendar header -->
    <rect x="60" y="70" width="80" height="20" rx="8" fill="url(#grad-calendar)"/>
    <rect x="60" y="80" width="80" height="10" fill="url(#grad-calendar)"/>
    
    <!-- Binding rings -->
    <circle cx="75" cy="70" r="4" fill="#6366f1"/>
    <circle cx="125" cy="70" r="4" fill="#6366f1"/>
    
    <!-- Calendar dates -->
    <g class="calendar-dates">
        <circle cx="80" cy="110" r="5" fill="#e5e7eb" class="date-pulse"/>
        <circle cx="100" cy="110" r="5" fill="#e5e7eb" class="date-pulse" style="animation-delay: 0.1s"/>
        <circle cx="120" cy="110" r="5" fill="#e5e7eb" class="date-pulse" style="animation-delay: 0.2s"/>
        
        <circle cx="80" cy="130" r="5" fill="#10b981" class="date-pulse" style="animation-delay: 0.3s"/>
        <circle cx="100" cy="130" r="5" fill="#e5e7eb" class="date-pulse" style="animation-delay: 0.4s"/>
        <circle cx="120" cy="130" r="5" fill="#e5e7eb" class="date-pulse" style="animation-delay: 0.5s"/>
    </g>
</svg>

<style>
/* ===================================
   تحريكات الرسوم التوضيحية
   =================================== */

/* Pulse animation */
@keyframes pulse-slow {
    0%, 100% { transform: scale(1); opacity: 0.1; }
    50% { transform: scale(1.1); opacity: 0.2; }
}

.animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
}

/* Float animation */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

/* Scale animation */
@keyframes scale-animation {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

.scale-animation {
    animation: scale-animation 0.6s ease-out forwards;
}

/* Checkmark path animation */
@keyframes draw-checkmark {
    to { stroke-dashoffset: 0; }
}

.checkmark-path {
    animation: draw-checkmark 0.8s ease-out 0.3s forwards;
}

/* Particle animation */
@keyframes particle-float {
    0% { transform: translate(0, 0); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: translate(var(--tx), var(--ty)); opacity: 0; }
}

.particle-1 { animation: particle-float 2s ease-in-out infinite; --tx: -20px; --ty: -20px; }
.particle-2 { animation: particle-float 2s ease-in-out infinite 0.5s; --tx: 20px; --ty: -15px; }
.particle-3 { animation: particle-float 2s ease-in-out infinite 1s; --tx: 15px; --ty: 20px; }
.particle-4 { animation: particle-float 2s ease-in-out infinite 1.5s; --tx: -15px; --ty: 15px; }

/* Coin float animation */
@keyframes coin-float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
}

.coin-float {
    animation: coin-float 2s ease-in-out infinite;
}

/* Arrow grow animation */
@keyframes arrow-grow {
    0% { stroke-dasharray: 100; stroke-dashoffset: 100; }
    100% { stroke-dasharray: 100; stroke-dashoffset: 0; }
}

.arrow-grow {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    animation: arrow-grow 1s ease-out 0.5s forwards;
}

/* Alert pulse */
@keyframes alert-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.alert-pulse {
    animation: alert-pulse 1.5s ease-in-out infinite;
}

/* Warning waves */
@keyframes warning-wave {
    0% { r: 50; opacity: 0.6; }
    100% { r: 80; opacity: 0; }
}

.wave-1 { animation: warning-wave 2s ease-out infinite; }
.wave-2 { animation: warning-wave 2s ease-out infinite 0.6s; }
.wave-3 { animation: warning-wave 2s ease-out infinite 1.2s; }

/* Equation fade */
@keyframes equation-fade {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.equation-fade {
    animation: equation-fade 2s ease-in-out infinite;
}

/* Bar grow animation */
@keyframes bar-grow {
    from { height: 0; }
    to { height: var(--bar-height); }
}

.bar-grow {
    animation: bar-grow 1s ease-out forwards;
}

/* Trend line animation */
@keyframes draw-trend {
    to { stroke-dashoffset: 0; }
}

.trend-line {
    animation: draw-trend 1.5s ease-out forwards;
}

/* Data point animation */
@keyframes data-point-appear {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); opacity: 1; }
}

.data-point {
    transform-origin: center;
    animation: data-point-appear 0.5s ease-out forwards;
    opacity: 0;
}

/* Date pulse */
@keyframes date-pulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.2); opacity: 1; }
}

.date-pulse {
    animation: date-pulse 2s ease-in-out infinite;
}

/* Hover effects */
.student-avatar:hover {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

/* Responsive sizing */
.illustration-students,
.illustration-attendance,
.illustration-finance,
.illustration-alerts,
.illustration-teachers,
.illustration-stats,
.illustration-calendar {
    width: 100%;
    height: auto;
    max-width: 200px;
}
</style>
