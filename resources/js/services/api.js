//api service helper
export const api = {
    async login(email, password) {
        
        const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:1337';
        await window.axios.get(`${apiUrl}/sanctum/csrf-cookie`); //jpuk cookie csrf ndisit
        
        return window.axios.post('/login', { email, password });
    },

    async logout() {
        return window.axios.post('/logout');  
    },

    async getUser() {
        return window.axios.get('/me');
    },


    //attendance
    async getAttendances(params = {}) {
        return window.axios.get('/attendances', { params });
    },

    async getAttendance(id) {
        return window.axios.get(`/attendances/${id}`);
    },

    async checkIn(data) {
        return window.axios.post('/attendances/check-in', data);
    },

    async checkOut(data) {
        return window.axios.post('/attendances/check-out', data);
    },

    async resetAttendance(id) {
        return window.axios.post(`/attendances/${id}/reset`);
    },


    //offices
    async getOffices(params = {}) {
        return window.axios.get('/offices', { params });
    },

    async getOffice(id) {
        return window.axios.get(`/offices/${id}`);
    },

    async createOffice(data) {
        return window.axios.post('/offices', data);
    },

    async updateOffice(id, data) {
        return window.axios.put(`/offices/${id}`, data);
    },

    async deleteOffice(id) {
        return window.axios.delete(`/offices/${id}`);
    },

    //members
    async getMembers(params = {}) {
        return window.axios.get('/members', { params });
    },

    async getMember(id) {
        return window.axios.get(`/members/${id}`);
    },

    async createMember(data) {
        return window.axios.post('/members', data);
    },

    async updateMember(id, data) {
        return window.axios.put(`/members/${id}`, data);
    },

    async deleteMember(id) {
        return window.axios.delete(`/members/${id}`);
    },


    //progress
    async getProgresses(params = {}) {
        return window.axios.get('/progresses', { params });
    },

    async getProgress(id) {
        return window.axios.get(`/progresses/${id}`);
    },

    async createProgress(data) {
        return window.axios.post('/progresses', data);
    },

    async updateProgress(id, data) {
        return window.axios.put(`/progresses/${id}`, data);
    },

    async deleteProgress(id) {
        return window.axios.delete(`/progresses/${id}`);
    }
};

//export ke global window
window.api = api;