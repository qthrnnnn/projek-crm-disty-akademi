/**
 * CRM Disty Akademi — REST API Client
 * Base URL: /api/...
 * Auth: Bearer Token via Laravel Sanctum
 */

const API_BASE = window.API_BASE || '/api';

class ApiService {
    constructor() {
        this.token = localStorage.getItem('crm_token') || null;
        this.user = JSON.parse(localStorage.getItem('crm_user') || 'null');
    }

    setAuth(token, user) {
        this.token = token;
        this.user = user;
        if (window.App && window.App.state) {
            window.App.state.user = user;
        }
        if (token) {
            localStorage.setItem('crm_token', token);
            localStorage.setItem('crm_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('crm_token');
            localStorage.removeItem('crm_user');
        }
    }

    isAuthenticated() { return !!this.token; }
    isAdmin() { return this.user && this.user.role === 'admin'; }
    isSales() { return this.user && this.user.role === 'sales'; }

    async request(endpoint, options = {}) {
        const url = `${API_BASE}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`;
        const headers = { 'Accept': 'application/json', ...(options.headers || {}) };

        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, { ...options, headers });

            if (response.status === 401) {
                this.setAuth(null, null);
                if (window.App) App.navigate('/login');
                throw new Error('Sesi telah berakhir, silakan login kembali.');
            }
            if (response.status === 204) return null;

            const data = await response.json();
            if (!response.ok) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Terjadi kesalahan.');
                throw new Error(msg);
            }
            return data;
        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Tidak dapat terhubung ke server API.');
            }
            throw error;
        }
    }

    // ── Auth ──
    async login(email, password) {
        const res = await this.request('/login', {
            method: 'POST', body: JSON.stringify({ email, password })
        });
        if (res.token) this.setAuth(res.token, res.user);
        return res;
    }

    async logout() {
        try { await this.request('/logout', { method: 'POST' }); } catch (e) { /* ignore */ }
        finally { this.setAuth(null, null); }
    }

    async getUser() { return await this.request('/user'); }

    // ── Dashboard ──
    async getDashboard() { return await this.request('/dashboard'); }

    // ── Generic CRUD ──
    async list(resource, params = {}) {
        const q = new URLSearchParams(params).toString();
        return await this.request(`/${resource}${q ? '?' + q : ''}`);
    }

    async get(resource, id) {
        return await this.request(`/${resource}/${id}`);
    }

    async create(resource, data) {
        const isFormData = data instanceof FormData;
        return await this.request(`/${resource}`, {
            method: 'POST',
            body: isFormData ? data : JSON.stringify(data)
        });
    }

    async update(resource, id, data) {
        return await this.request(`/${resource}/${id}`, {
            method: 'PUT', body: JSON.stringify(data)
        });
    }

    async remove(resource, id) {
        return await this.request(`/${resource}/${id}`, { method: 'DELETE' });
    }

    // ── Lead Actions ──
    async claimLead(id) {
        return await this.request(`/leads/${id}/claim`, { method: 'POST' });
    }

    async updateLeadStatus(id, data) {
        return await this.request(`/leads/${id}/status`, {
            method: 'PATCH', body: JSON.stringify(data)
        });
    }

    async addLeadActivity(leadId, data) {
        return await this.request(`/leads/${leadId}/activities`, {
            method: 'POST', body: JSON.stringify(data)
        });
    }

    async importLeads(formData) {
        return await this.request('/leads/import', { method: 'POST', body: formData });
    }

    async downloadLeadTemplate() {
        window.open(`${API_BASE}/leads/template`, '_blank');
    }

    // ── Follow-up Actions ──
    async completeFollowUp(id, data = {}) {
        return await this.request(`/follow-ups/${id}/complete`, {
            method: 'PATCH', body: JSON.stringify(data)
        });
    }

    async cancelFollowUp(id) {
        return await this.request(`/follow-ups/${id}/cancel`, { method: 'PATCH' });
    }

    // ── Payment Actions ──
    async storePayment(formData) {
        return await this.request('/payments', { method: 'POST', body: formData });
    }

    async verifyPayment(id) {
        return await this.request(`/payments/${id}/verify`, { method: 'PATCH' });
    }

    // ── Commission Actions ──
    async approveCommission(id) {
        return await this.request(`/commissions/${id}/approve`, { method: 'PATCH' });
    }

    async payCommission(id) {
        return await this.request(`/commissions/${id}/pay`, { method: 'PATCH' });
    }

    // ── Reminder Actions ──
    async remindSales(leadId, data) {
        return await this.request(`/leads/${leadId}/remind-sales`, {
            method: 'POST', body: JSON.stringify(data)
        });
    }

    async markAllRemindersRead() {
        return await this.request('/reminders/mark-all-read', { method: 'POST' });
    }

    async markReminderRead(id) {
        return await this.request(`/reminders/${id}/read`, { method: 'PATCH' });
    }

    async completeReminder(id, data = {}) {
        return await this.request(`/reminders/${id}/complete`, {
            method: 'PATCH', body: JSON.stringify(data)
        });
    }

    // ── Customer Import/Export ──
    async importCustomers(formData) {
        return await this.request('/customers/import', { method: 'POST', body: formData });
    }

    async exportCustomers() {
        window.open(`${API_BASE}/customers/export`, '_blank');
    }

    async downloadCustomerTemplate() {
        window.open(`${API_BASE}/customers/template`, '_blank');
    }
}

window.api = new ApiService();
