import { defineAsyncComponent } from 'vue';

// Lazy-loaded page components organized matching resources/views
export const routes = {
    // Auth
    'Auth.Login': defineAsyncComponent(() => import('../Pages/Auth/Login.vue')),

    // Dashboard
    'Dashboard.Index': defineAsyncComponent(() => import('../Pages/Dashboard/Index.vue')),

    // Activities
    'Activities.Index': defineAsyncComponent(() => import('../Pages/Activities/Index.vue')),

    // Leads
    'Leads.Index': defineAsyncComponent(() => import('../Pages/Leads/Index.vue')),
    'Leads.Create': defineAsyncComponent(() => import('../Pages/Leads/Create.vue')),
    'Leads.Edit': defineAsyncComponent(() => import('../Pages/Leads/Edit.vue')),
    'Leads.Show': defineAsyncComponent(() => import('../Pages/Leads/Show.vue')),

    // Follow-ups
    'FollowUps.Index': defineAsyncComponent(() => import('../Pages/FollowUps/Index.vue')),
    'FollowUps.Create': defineAsyncComponent(() => import('../Pages/FollowUps/Create.vue')),
    'FollowUps.Edit': defineAsyncComponent(() => import('../Pages/FollowUps/Edit.vue')),

    // Customers
    'Customers.Index': defineAsyncComponent(() => import('../Pages/Customers/Index.vue')),
    'Customers.Create': defineAsyncComponent(() => import('../Pages/Customers/Create.vue')),
    'Customers.Edit': defineAsyncComponent(() => import('../Pages/Customers/Edit.vue')),
    'Customers.Show': defineAsyncComponent(() => import('../Pages/Customers/Show.vue')),

    // Institutions
    'Institutions.Index': defineAsyncComponent(() => import('../Pages/Institutions/Index.vue')),
    'Institutions.Create': defineAsyncComponent(() => import('../Pages/Institutions/Create.vue')),
    'Institutions.Edit': defineAsyncComponent(() => import('../Pages/Institutions/Edit.vue')),

    // Registrations
    'Registrations.Index': defineAsyncComponent(() => import('../Pages/Registrations/Index.vue')),
    'Registrations.Create': defineAsyncComponent(() => import('../Pages/Registrations/Create.vue')),
    'Registrations.Edit': defineAsyncComponent(() => import('../Pages/Registrations/Edit.vue')),

    // Training Schedules
    'TrainingSchedules.Index': defineAsyncComponent(() => import('../Pages/TrainingSchedules/Index.vue')),
    'TrainingSchedules.Create': defineAsyncComponent(() => import('../Pages/TrainingSchedules/Create.vue')),
    'TrainingSchedules.Edit': defineAsyncComponent(() => import('../Pages/TrainingSchedules/Edit.vue')),

    // Trainers
    'Trainers.Index': defineAsyncComponent(() => import('../Pages/Trainers/Index.vue')),
    'Trainers.Create': defineAsyncComponent(() => import('../Pages/Trainers/Create.vue')),
    'Trainers.Edit': defineAsyncComponent(() => import('../Pages/Trainers/Edit.vue')),

    // Products
    'Products.Index': defineAsyncComponent(() => import('../Pages/Products/Index.vue')),
    'Products.Create': defineAsyncComponent(() => import('../Pages/Products/Create.vue')),
    'Products.Edit': defineAsyncComponent(() => import('../Pages/Products/Edit.vue')),

    // Payments
    'Payments.Index': defineAsyncComponent(() => import('../Pages/Payments/Index.vue')),
    'Payments.Create': defineAsyncComponent(() => import('../Pages/Payments/Create.vue')),
    'Payments.Show': defineAsyncComponent(() => import('../Pages/Payments/Show.vue')),

    // Commissions
    'Commissions.Index': defineAsyncComponent(() => import('../Pages/Commissions/Index.vue')),

    // Reminders
    'Reminders.Index': defineAsyncComponent(() => import('../Pages/Reminders/Index.vue')),

    // Users
    'Users.Index': defineAsyncComponent(() => import('../Pages/Users/Index.vue')),
    'Users.Create': defineAsyncComponent(() => import('../Pages/Users/Create.vue')),
    'Users.Edit': defineAsyncComponent(() => import('../Pages/Users/Edit.vue')),

    // Imports
    'Imports.Index': defineAsyncComponent(() => import('../Pages/Imports/Index.vue')),
    'Imports.Create': defineAsyncComponent(() => import('../Pages/Imports/Create.vue')),
    'Imports.Show': defineAsyncComponent(() => import('../Pages/Imports/Show.vue')),
};

export function resolveRoute(path) {
    const clean = path.replace(/^\//, '').split('?')[0];
    const parts = clean.split('/').filter(Boolean);

    if (parts.length === 0 || parts[0] === 'dashboard') {
        return { componentKey: 'Dashboard.Index', title: 'Dashboard', id: null };
    }
    if (parts[0] === 'login') {
        return { componentKey: 'Auth.Login', title: 'Login', id: null };
    }

    const resourceMap = {
        'activities': { key: 'Activities', title: 'Riwayat Aktivitas' },
        'leads': { key: 'Leads', title: 'Leads & Prospek' },
        'follow-ups': { key: 'FollowUps', title: 'Follow-up' },
        'customers': { key: 'Customers', title: 'Customer' },
        'institutions': { key: 'Institutions', title: 'Instansi' },
        'registrations': { key: 'Registrations', title: 'Pendaftaran' },
        'training-schedules': { key: 'TrainingSchedules', title: 'Jadwal Pelatihan' },
        'trainers': { key: 'Trainers', title: 'Trainer' },
        'products': { key: 'Products', title: 'Katalog Program' },
        'payments': { key: 'Payments', title: 'Pembayaran' },
        'commissions': { key: 'Commissions', title: 'Komisi Sales' },
        'reminders': { key: 'Reminders', title: 'Notifikasi' },
        'users': { key: 'Users', title: 'Pengguna' },
        'lead-imports': { key: 'Imports', title: 'Import Batch' },
        'imports': { key: 'Imports', title: 'Import Batch' },
    };

    const res = resourceMap[parts[0]];
    if (!res) {
        return { componentKey: 'Dashboard.Index', title: 'Dashboard', id: null };
    }

    // e.g. /leads/create
    if (parts[1] === 'create') {
        return { componentKey: `${res.key}.Create`, title: res.title, id: null };
    }

    // e.g. /leads/123/edit
    if (parts.length === 3 && parts[2] === 'edit') {
        return { componentKey: `${res.key}.Edit`, title: res.title, id: parts[1] };
    }

    // e.g. /leads/123
    if (parts.length === 2 && /^\d+$/.test(parts[1])) {
        // If resource has a Show page, use Show; otherwise fallback to Edit or Index
        const showKey = `${res.key}.Show`;
        return {
            componentKey: routes[showKey] ? showKey : `${res.key}.Edit`,
            title: res.title,
            id: parts[1],
        };
    }

    return { componentKey: `${res.key}.Index`, title: res.title, id: null };
}
