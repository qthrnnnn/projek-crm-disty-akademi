/**
 * Login Page Module
 */
(function() {
    App.pages.login = {
        async index(container) {
            container.innerHTML = `
                <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-orange-950 flex items-center justify-center p-4">
                    <div class="w-full max-w-md bg-white rounded-3xl p-8 sm:p-10 shadow-2xl space-y-6">
                        <div class="text-center space-y-2">
                            <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white font-extrabold text-2xl flex items-center justify-center mx-auto shadow-lg shadow-orange-500/30">
                                <img src="/images/disty-icon.png" alt="Logo" class="w-10 h-10 object-contain rounded-xl" onerror="this.outerHTML='<span class=\\'text-2xl font-black\\'>D</span>'">
                            </div>
                            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Selamat Datang</h1>
                            <p class="text-xs text-gray-500">Masuk ke CRM Disty Akademi untuk melanjutkan</p>
                        </div>

                        <form id="form-login" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                                <input type="email" id="login-email" value="admin@distyakademi.com" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all"
                                    placeholder="nama@distyakademi.com">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                                <input type="password" id="login-password" value="password" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all"
                                    placeholder="••••••••">
                            </div>

                            <button type="submit" id="btn-login-submit"
                                class="w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-lg shadow-orange-500/30 transition-all flex items-center justify-center gap-2">
                                <span>Masuk ke CRM</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>

                        <div class="text-center pt-2 border-t border-gray-100">
                            <span class="text-[11px] text-gray-400">Arsitektur Murni Single Page Application &bull; PT Disty</span>
                        </div>
                    </div>
                </div>
            `;

            const form = document.getElementById('form-login');
            if (form) {
                form.onsubmit = async (e) => {
                    e.preventDefault();
                    const email = document.getElementById('login-email').value;
                    const password = document.getElementById('login-password').value;
                    const btn = document.getElementById('btn-login-submit');

                    btn.disabled = true;
                    btn.innerHTML = `<svg class="w-4 h-4 animate-spin mr-1.5 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Memverifikasi...`;

                    try {
                        const res = await api.login(email, password);
                        App.state.user = res.user;
                        App.showToast('Login berhasil! Selamat datang, ' + res.user.name);
                        App.navigate('/');
                    } catch (err) {
                        App.showToast(err.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = `<span>Masuk ke CRM</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>`;
                    }
                };
            }
        }
    };
})();
