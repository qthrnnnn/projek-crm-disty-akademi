/**
 * CRM Disty Akademi — REST API Service for Vue SPA
 */

const API_BASE = '/api';

class ApiService {
    constructor() {
        this.token = localStorage.getItem('crm_token') || null;
        this.user = JSON.parse(localStorage.getItem('crm_user') || 'null');
    }

    setAuth(token, user) {
        this.token = token;
        this.user = user;
        if (token) {
            localStorage.setItem('crm_token', token);
            localStorage.setItem('crm_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('crm_token');
            localStorage.removeItem('crm_user');
        }
    }

    isAuthenticated() {
        return Boolean(this.token && this.user);
    }

    isAdmin() {
        return this.user && this.user.role === 'admin';
    }

    isSales() {
        return this.user && this.user.role === 'sales';
    }

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
                window.location.href = '/login';
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
            method: 'POST',
            body: JSON.stringify({ email, password, device_name: 'disty-vue' })
        });
        if (res.token) this.setAuth(res.token, res.user);
        return res;
    }

    async logout() {
        try {
            await this.request('/logout', { method: 'POST' });
        } catch (e) {
            /* ignore */
        } finally {
            this.setAuth(null, null);
        }
    }

    async getUser() {
        return await this.request('/user');
    }

    // ── Dashboard ──
    async getDashboard() {
        return await this.request('/dashboard');
    }

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
        const isFormData = data instanceof FormData;
        if (isFormData) {
            data.append('_method', 'PUT');
            return await this.request(`/${resource}/${id}`, {
                method: 'POST',
                body: data
            });
        }
        return await this.request(`/${resource}/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async destroy(resource, id) {
        return await this.request(`/${resource}/${id}`, { method: 'DELETE' });
    }
}

export const api = new ApiService();
export default api;
