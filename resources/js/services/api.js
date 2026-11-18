/**
 * CRM Disty Akademi — REST API Service for Vue SPA (JWT Auth)
 */

const API_BASE = '/api';
const TOKEN_KEY = 'crm_jwt_token';

class ApiService {
    constructor() {
        this.user = this.getUserFromStorage();
    }

    getUserFromStorage() {
        try {
            const raw = localStorage.getItem('crm_user');
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    }

    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    }

    setToken(token) {
        if (token) {
            localStorage.setItem(TOKEN_KEY, token);
        } else {
            localStorage.removeItem(TOKEN_KEY);
        }
    }

    setUser(user) {
        this.user = user;
        if (user) {
            localStorage.setItem('crm_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('crm_user');
        }
    }

    isAuthenticated() {
        return Boolean(this.getToken() || this.user);
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const token = this.getToken();

        const fetchOptions = { ...options };
        fetchOptions.credentials = 'same-origin';
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        if (fetchOptions.body && !(fetchOptions.body instanceof FormData) && typeof fetchOptions.body === 'object') {
            fetchOptions.body = JSON.stringify(fetchOptions.body);
            headers['Content-Type'] = 'application/json';
        } else if (!(fetchOptions.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }
        fetchOptions.headers = headers;

        try {
            const response = await fetch(url, fetchOptions);

            if (response.status === 401) {
                this.setToken(null);
                this.setUser(null);
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
    async login(email, password, remember = false) {
        const res = await this.request('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password, remember })
        });
        const token = res.token || res.data?.token;
        if (token) {
            this.setToken(token);
        }
        this.setUser(res.user || res.data?.user);
        return res;
    }

    async refresh() {
        throw new Error('JWT token berlaku selama 8 jam tanpa refresh token.');
    }

    async logout() {
        try {
            await this.request('/logout', { method: 'POST' });
        } finally {
            this.setToken(null);
            this.setUser(null);
        }
    }

    async getUser() {
        return await this.request('/user');
    }

    async updateBankAccount(data) {
        return await this.request('/user/bank-account', {
            method: 'POST',
            body: JSON.stringify(data)
        });
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

    // ── Commissions Actions ──
    async approveCommission(id) {
        return await this.request(`/commissions/${id}/approve`, { method: 'PATCH' });
    }

    async payCommission(id, data = {}) {
        return await this.request(`/commissions/${id}/pay`, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }
}

export const api = new ApiService();
export default api;
