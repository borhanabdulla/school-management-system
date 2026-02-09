@props([
    'chartId' => 'pieChart' . uniqid(),
    'title' => '',
    'labels' => [],
    'data' => [],
    'colors' => [],
    'height' => '300',
])

<div class="w-full">
    @if($title)
        <h4 class="text-sm font-medium text-[rgb(var(--chart-title))] mb-3">{{ $title }}</h4>
    @endif
    <div class="relative" style="height: {{ $height }}px;">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($data),
                backgroundColor: @json($colors),
                borderWidth: 2,
                borderColor: 'rgb(var(--color-white))',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            family: 'Inter, sans-serif',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgb(var(--color-black) / 0.8)',
                    padding: 12,
                    borderRadius: 8,
                    titleFont: {
                        size: 13,
                        family: 'Inter, sans-serif'
                    },
                    bodyFont: {
                        size: 12,
                        family: 'Inter, sans-serif'
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
