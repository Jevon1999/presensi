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
                label: 'Present',
                data: counts,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
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
            backgroundColor: '#1F2937',
            titleColor: '#FFFFFF',
            bodyColor: '#FFFFFF',
            padding: 12,
            displayColors: false,
            borderRadius: 12,
            titleFont: {
                size: 12,
                weight: 600
            },
            bodyFont: {
                size: 13,
                weight: 'bold'
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                color: '#9CA3AF',
                font: {
                    size: 12,
                    weight: 600
                }
            },
            grid: {
                color: '#F3F4F6',
                lineWidth: 1
            },
            border: {
                display: false
            }
        },
        x: {
            ticks: {
                color: '#9CA3AF',
                font: {
                    size: 12,
                    weight: 600
                }
            },
            grid: {
                display: false
            },
            border: {
                display: false
            }
        }
    }
};
</script>

<template>
    <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold">Attendance Trends</h3>
                <p class="text-sm text-gray-400">Weekly attendance data across all offices</p>
            </div>
            <select class="bg-gray-50 border-none rounded-lg text-xs font-bold py-2 px-4 focus:ring-0">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
            </select>
        </div>
        <div class="h-[300px] w-full">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
