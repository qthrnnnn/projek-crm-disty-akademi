<?php

return [
    'rahasia' => env('JWT_SECRET', 'kunci-rahasia-crm-disty-akademi-2026-sangat-aman'),
    'berlaku_detik' => (int) env('JWT_EXPIRES_SECONDS', 28800), // 8 jam
];
