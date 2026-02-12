@props([
    'total' => 0,
    'target' => 100,
])

@php
    $totalValue = (float) $total;
    $targetValue = (float) $target;
    $percentage = $targetValue > 0 ? min(100, max(0, ($totalValue / $targetValue) * 100)) : 0;
    $status = abs($totalValue - $targetValue) < 0.01 ? 'ok' : ($totalValue > $targetValue ? 'over' : 'under');

    $config = match($status) {
        'ok' => [
            'bar' => 'from-emerald-400 to-emerald-600',
            'bg' => 'border-emerald-200/60 bg-emerald-50/40 dark:border-emerald-700/40 dark:bg-emerald-900/15',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            'label' => 'جاهز للإغلاق',
            'desc' => 'مجموع الأوزان = ' . number_format($targetValue, 0) . '%',
            'pulse' => true,
        ],
        'over' => [
            'bar' => 'from-rose-400 to-rose-600',
            'bg' => 'border-rose-200/60 bg-rose-50/40 dark:border-rose-700/40 dark:bg-rose-900/15',
            'text' => 'text-rose-700 dark:text-rose-300',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
            'label' => 'تجاوز الحد',
            'desc' => 'خفّض بعض الفئات لتصل إلى ' . number_format($targetValue, 0) . '%',
            'pulse' => false,
        ],
        default => [
            'bar' => 'from-amber-400 to-amber-600',
            'bg' => 'border-amber-200/60 bg-amber-50/40 dark:border-amber-700/40 dark:bg-amber-900/15',
            'text' => 'text-amber-700 dark:text-amber-300',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.25-8.25-3.286Z" />',
            'label' => 'غير مكتمل',
            'desc' => 'أكمل الأوزان لتصل إلى ' . number_format($targetValue, 0) . '%',
            'pulse' => false,
        ],
    };
@endphp

<div class="rounded-2xl border p-4 {{ $config['bg'] }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="relative flex h-8 w-8 items-center justify-center rounded-lg {{ match($status) {
                'ok' => 'bg-emerald-100 dark:bg-emerald-800/40',
                'over' => 'bg-rose-100 dark:bg-rose-800/40',
                default => 'bg-amber-100 dark:bg-amber-800/40',
            } }}">
                <svg class="h-4 w-4 {{ $config['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    {!! $config['icon'] !!}
                </svg>
                @if($config['pulse'])
                    <span class="absolute -right-0.5 -top-0.5 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                @endif
            </div>
            <div>
                <div class="text-xs font-bold {{ $config['text'] }}">{{ $config['label'] }}</div>
                <div class="text-[11px] {{ $config['text'] }} opacity-70">{{ $config['desc'] }}</div>
            </div>
        </div>
        <div class="text-lg font-black {{ $config['text'] }}">
            {{ number_format($totalValue, 1) }}%
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="mt-3 h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-800">
        <div
            class="h-2.5 rounded-full bg-gradient-to-l transition-all duration-700 ease-out {{ $config['bar'] }}"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
