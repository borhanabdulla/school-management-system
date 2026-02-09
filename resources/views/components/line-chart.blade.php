@props([
    'chartId' => 'lineChart' . uniqid(),
    'title' => '',
    'labels' => [],
    'datasets' => [],
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
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: @json($datasets)
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
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
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgb(var(--color-black) / 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
