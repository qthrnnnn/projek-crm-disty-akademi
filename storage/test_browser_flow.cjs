const http = require('http');

// First login to get token
const loginData = JSON.stringify({ email: 'admin@distyakademi.com', password: 'password' });

const loginReq = http.request({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/login',
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Content-Length': loginData.length
    }
}, (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        const loginRes = JSON.parse(data);
        const token = loginRes.token;
        console.log('Got token:', token ? 'YES' : 'NO');

        // Now call /api/dashboard
        const dashReq = http.request({
            hostname: '127.0.0.1',
            port: 8000,
            path: '/api/dashboard',
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        }, (res2) => {
            let dashData = '';
            res2.on('data', chunk => dashData += chunk);
            res2.on('end', () => {
                console.log('Dashboard status:', res2.statusCode);
                try {
                    const parsed = JSON.parse(dashData);
                    console.log('Dashboard data keys:', Object.keys(parsed.data));
                    console.log('pipelineLeadsData type:', typeof parsed.data.pipelineLeadsData, 'isArray:', Array.isArray(parsed.data.pipelineLeadsData));
                    console.log('sixMonthLeads type:', typeof parsed.data.sixMonthLeads, 'isArray:', Array.isArray(parsed.data.sixMonthLeads));
                    console.log('recentActivities type:', typeof parsed.data.recentActivities, 'isArray:', Array.isArray(parsed.data.recentActivities));
                    console.log('upcomingFollowUps type:', typeof parsed.data.upcomingFollowUps, 'isArray:', Array.isArray(parsed.data.upcomingFollowUps));
                } catch (e) {
                    console.error('JSON parse error:', e);
                }
            });
        });
        dashReq.end();
    });
});

loginReq.write(loginData);
loginReq.end();
