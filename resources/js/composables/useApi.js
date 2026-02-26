import axios from 'axios';
import { router } from '@inertiajs/vue3';

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'https://api.globalintermedia.online',
    withCredentials: false,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Response interceptor for error handling
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            router.visit('/login');
        }
        return Promise.reject(error);
    }
);

export const useApi = () => {
    return {
        // Dashboard stats
        async getDashboardStats(token) {
            const today = new Date().toISOString().split('T')[0];
            const [attendances, members, offices] = await Promise.all([
                apiClient.get('/api/attendances', {
                    params: { date: today },
                    headers: { Authorization: `Bearer ${token}` },
                }),
                apiClient.get('/api/members', {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                apiClient.get('/api/offices', {
                    headers: { Authorization: `Bearer ${token}` },
                }),
            ]);

            return {
                attendances: attendances.data,
                members: members.data,
                offices: offices.data,
            };
        },

        // Get attendance report for charts
        async getAttendanceReport(token, startDate, endDate, filters = {}) {
            const response = await apiClient.get('/api/attendances/report', {
                params: { start_date: startDate, end_date: endDate, ...filters },
                headers: { Authorization: `Bearer ${token}` },
            });
            return response.data;
        },

        // Get recent attendances
        async getRecentAttendances(token, limit = 10) {
            const response = await apiClient.get('/api/attendances', {
                params: { per_page: limit },
                headers: { Authorization: `Bearer ${token}` },
            });
            return response.data;
        },

        // Get members without today's attendance
        async getMembersWithoutAttendance(token) {
            const today = new Date().toISOString().split('T')[0];
            const [members, todayAttendances] = await Promise.all([
                apiClient.get('/api/members', {
                    headers: { Authorization: `Bearer ${token}` },
                }),
                apiClient.get('/api/attendances', {
                    params: { date: today },
                    headers: { Authorization: `Bearer ${token}` },
                }),
            ]);

            const attendanceMembers = new Set(
                (todayAttendances.data.data || []).map(a => a.member_id)
            );
            
            return (members.data.data || []).filter(
                m => m.status_aktif && !attendanceMembers.has(m.id)
            );
        },
    };
};
