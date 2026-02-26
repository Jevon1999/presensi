<script setup>
import { ref, onMounted, computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    weeklyStats: Object,
    weeklyAttendances: Array
});

const chartData = computed(() => {
    const attendances = props.weeklyAttendances || [];
    
    // Group by date
    const dateMap = {};
    attendances.forEach(att => {
        if (!dateMap[att.tanggal]) {
            dateMap[att.tanggal] = 0;
        }
        if (att.status === 'hadir') {
            dateMap[att.tanggal]++;
        }
    });

    // Get last 7 days
    const days = [];
    const counts = [];
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        const dayName = date.toLocaleDateString('id-ID', { weekday: 'short' });
        
        days.push(dayName);
        counts.push(dateMap[dateStr] || 0);
    }

    return {
        labels: days,
        datasets: [
            {
                label: 'ATTENDANCE',
                data: counts,
                borderColor: '#FF8C42',
                backgroundColor: 'rgba(255, 140, 66, 0.1)',
                fill: true,
                tension: 0.2,
                pointBackgroundColor: '#FF8C42',
                pointBorderColor: '#0F1115',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        title: {
            display: false
        },
        tooltip: {
            backgroundColor: '#171A1F',
            titleColor: '#E8E9EA',
            bodyColor: '#E8E9EA',
            padding: 8,
            displayColors: false,
            titleFont: {
                family: 'monospace',
                size: 10
            },
            bodyFont: {
                family: 'monospace',
                size: 12,
                weight: 'bold'
            },
            borderColor: '#FF8C42',
            borderWidth: 1
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                color: '#666B73',
                font: {
                    family: 'monospace',
                    size: 10
                }
            },
            grid: {
                color: '#1E2228',
                lineWidth: 1
            },
            border: {
                color: '#666B73'
            }
        },
        x: {
            ticks: {
                color: '#666B73',
                font: {
                    family: 'monospace',
                    size: 10
                }
            },
            grid: {
                color: '#1E2228',
                lineWidth: 1
            },
            border: {
                color: '#666B73'
            }
        }
    }
};
</script>

<template>
    <div class="bg-base-200 border border-neutral/30 rounded-md">
        <div class="p-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-4 bg-primary rounded"></div>
                <h3 class="text-xs font-mono font-semibold tracking-wider uppercase">WEEKLY TELEMETRY</h3>
            </div>
            <div class="h-64">
                <Line :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>
