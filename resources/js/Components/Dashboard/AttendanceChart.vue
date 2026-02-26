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
                label: 'Kehadiran',
                data: counts,
                borderColor: '#4a5568',
                backgroundColor: 'rgba(74, 85, 104, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#4a5568',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
            backgroundColor: '#3d4451',
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 12,
            displayColors: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                color: '#9ca3af'
            },
            grid: {
                color: 'rgba(156, 163, 175, 0.1)'
            }
        },
        x: {
            ticks: {
                color: '#9ca3af'
            },
            grid: {
                display: false
            }
        }
    }
};
</script>

<template>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">Tren Kehadiran Mingguan</h3>
            <div class="h-64">
                <Line :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>
