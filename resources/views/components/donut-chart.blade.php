@props([
    'chartId' => 'donutChart' . uniqid(),
    'title' => '',
    'labels' => [],
    'data' => [],
    'colors' => [],
    'height' => '300',
    'centerText' => '',
])

<div class="w-full">
    @if($title)
        <h4 class="text-sm font-medium text-[rgb(var(--chart-title))] mb-3">{{ $title }}</h4>
    @endif
    <div class="relative" style="height: {{ $height }}px;">
        <canvas id="{{ $chartId }}"></canvas>
        @if($centerText)
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="text-center">
                    <p class="text-2xl font-bold text-[rgb(var(--chart-center))]">{{ $centerText }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($data),
                backgroundColor: @json($colors),
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
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
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${percentage}%`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
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
