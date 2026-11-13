/**
 * Reminders Page Module
 * Sales Notifications Center: Follow-up Alerts & Commission Payouts
 */
(function() {
    App.pages.reminders = {
        state: {
            page: 1,
        },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('reminders', { page: this.state.page, per_page: 25 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            this.container.innerHTML = `
                <div class="max-w-4xl mx-auto space-y-6">
                    ${App.pageHeader(
                        'Pusat Notifikasi & Pengingat',
                        'Instruksi follow-up dari Admin serta pemberitahuan komisi closing',
                        `<button onclick="App.pages.reminders.markAllRead()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tandai Semua Dibaca
                        </button>`
                    )}

                    <div class="space-y-3">
                        ${items.length === 0 ? `
                            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <p class="text-xs">Belum ada notifikasi atau pengingat baru.</p>
                            </div>
                        ` : items.map(r => {
                            const isCommission = r.type === 'commission_paid';
                            const isUrgent = r.priority === 'urgent';
                            const cleanPhone = (r.lead?.whatsapp || r.lead?.phone || '').replace(/[^0-9]/g, '');
                            const waLink = cleanPhone ? (cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone) : null;

                            return `
                                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs hover:shadow-xs transition-all relative overflow-hidden ${r.is_read ? 'opacity-80' : 'ring-1 ring-orange-500/20'}">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 ${isCommission ? 'bg-emerald-500' : (isUrgent ? 'bg-red-500' : 'bg-amber-500')}"></div>

                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pl-2">
                                        <div class="space-y-1.5 flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider ${isCommission ? 'bg-emerald-50 text-emerald-700' : (isUrgent ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700')}">
                                                    ${isCommission ? 'Komisi Masuk' : (isUrgent ? 'Mendesak' : 'Pengingat Follow-Up')}
                                                </span>
                                                <span class="text-[10px] text-gray-400">${App.formatDateTime(r.created_at)}</span>
                                            </div>

                                            <p class="text-xs text-gray-900 font-semibold leading-relaxed">${r.message || '-'}</p>

                                            ${r.lead ? `
                                                <p class="text-[11px] text-gray-500">
                                                    Prospek: <strong class="text-gray-800">${r.lead.name}</strong> (${r.lead.company || 'Perorangan'})
                                                </p>
                                            ` : ''}
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 pt-2 sm:pt-0">
                                            ${waLink ? `
                                                <a href="https://wa.me/${waLink}?text=${encodeURIComponent('Halo Kak ' + (r.lead?.name || '') + ', salam dari Disty Akademi.')}"
                                                   target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs">
                                                    Chat WhatsApp
                                                </a>
                                            ` : ''}
                                            ${!r.is_completed && !isCommission ? `
                                                <button onclick="App.pages.reminders.handleComplete(${r.id})" class="px-3 py-1.5 rounded-xl bg-gray-900 hover:bg-black text-white font-semibold text-xs">
                                                    Tandai Selesai
                                                </button>
                                            ` : ''}
                                            ${isCommission ? `
                                                <button onclick="App.navigate('/commissions')" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs">
                                                    Lihat Komisi
                                                </button>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async markAllRead() {
            try {
                await api.markAllRemindersRead();
                App.showToast('Semua notifikasi ditandai dibaca.');
                await this.loadList();
                if (App.loadReminders) App.loadReminders();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        async handleComplete(id) {
            const reply = prompt('Catatan tindakan yang telah dilakukan (opsional):', '');
            if (reply === null) return;
            try {
                await api.completeReminder(id, { sales_reply: reply });
                App.showToast('Pengingat ditandai selesai.');
                await this.loadList();
                if (App.loadReminders) App.loadReminders();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        }
    };
})();
