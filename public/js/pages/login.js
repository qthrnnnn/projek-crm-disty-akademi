/**
 * Login Page Module — 1:1 Exact Replica of Blade Login View (resources/views/auth/login.blade.php)
 */
(function() {
    App.pages.login = {
        async index(container) {
            container.innerHTML = `
                <div class="h-full min-h-screen bg-slate-50 flex items-center justify-center p-4">
                    <div class="w-full max-w-md">
                        <div class="bg-white border border-blue-100 p-8 shadow-sm">
                            <!-- Logo Disty Akademi -->
                            <div class="flex justify-center mb-6">
                                <img src="/images/disty-logo.png" alt="Disty Akademi" class="h-12 w-auto object-contain" onerror="this.src='/images/disty-icon.png'">
                            </div>

                            <div class="text-center mb-6">
                                <h1 class="text-xl font-bold text-gray-900">Selamat Datang</h1>
                                <p class="text-xs text-gray-500 mt-1">Masuk ke akun Anda untuk melanjutkan</p>
                            </div>

                            <div id="login-error" class="hidden mb-5 bg-red-50 border border-red-200 px-4 py-3 text-xs text-red-700"></div>

                            <form id="form-login" class="space-y-4 text-xs">
                                <div>
                                    <label for="email" class="block font-semibold text-gray-700 mb-1.5">Email</label>
                                    <input id="email" name="email" type="email" required autofocus
                                           value="admin@distyakademi.com"
                                           class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                                           placeholder="nama@distyakademi.com">
                                </div>

                                <div>
                                    <label for="password" class="block font-semibold text-gray-700 mb-1.5">Password</label>
                                    <input id="password" name="password" type="password" required
                                           value="password"
                                           class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                                           placeholder="••••••••">
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                                        <input type="checkbox" name="remember" class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span>Ingat saya</span>
                                    </label>
                                </div>

                                <button type="submit" id="btn-login-submit"
                                        class="w-full rounded-xl bg-orange-500 hover:bg-orange-500 text-white font-semibold py-2.5 shadow-sm transition-colors text-xs mt-2">
                                    Masuk
                                </button>
                            </form>

                            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                                <p class="text-[11px] text-gray-400">
                                    &copy; ${new Date().getFullYear()} Disty Akademi. All rights reserved.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const form = document.getElementById('form-login');
            if (form) {
                form.onsubmit = async (e) => {
                    e.preventDefault();
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const btn = document.getElementById('btn-login-submit');
                    const errEl = document.getElementById('login-error');

                    if (errEl) {
                        errEl.classList.add('hidden');
                        errEl.textContent = '';
                    }

                    btn.disabled = true;
                    btn.textContent = 'Memverifikasi...';

                    try {
                        const res = await api.login(email, password);
                        App.state.user = res.user;
                        App.showToast('Login berhasil! Selamat datang, ' + res.user.name);
                        App.navigate('/');
                    } catch (err) {
                        if (errEl) {
                            errEl.textContent = err.message || 'Login gagal. Periksa kembali email dan password.';
                            errEl.classList.remove('hidden');
                        }
                        App.showToast(err.message, 'error');
                        btn.disabled = false;
                        btn.textContent = 'Masuk';
                    }
                };
            }
        }
    };
})();
