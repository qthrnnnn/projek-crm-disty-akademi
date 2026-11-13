<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CRM Disty Akademi</title>
    <link rel="icon" type="image/png" href="/images/disty-icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            height: 100dvh;
            max-height: 100dvh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        body { font-family: 'Inter', sans-serif; }
        .sidebar-transition { transition: width 200ms ease-in-out; }

        /* Sembunyikan semua penanda scrollbar pada navbar & sidebar */
        .no-scrollbar::-webkit-scrollbar,
        .scrollbar-none::-webkit-scrollbar,
        #sidebar-desktop nav::-webkit-scrollbar,
        #sidebar-desktop::-webkit-scrollbar,
        #sidebar-mobile nav::-webkit-scrollbar,
        #sidebar-mobile::-webkit-scrollbar,
        aside nav::-webkit-scrollbar,
        aside::-webkit-scrollbar,
        #sidebar-desktop nav *::-webkit-scrollbar,
        #sidebar-mobile nav *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
            -webkit-appearance: none !important;
        }

        .no-scrollbar::-webkit-scrollbar-thumb,
        .no-scrollbar::-webkit-scrollbar-track,
        .no-scrollbar::-webkit-scrollbar-button,
        .scrollbar-none::-webkit-scrollbar-thumb,
        .scrollbar-none::-webkit-scrollbar-track,
        .scrollbar-none::-webkit-scrollbar-button,
        #sidebar-desktop nav::-webkit-scrollbar-thumb,
        #sidebar-desktop nav::-webkit-scrollbar-track,
        #sidebar-desktop nav::-webkit-scrollbar-button,
        #sidebar-mobile nav::-webkit-scrollbar-thumb,
        #sidebar-mobile nav::-webkit-scrollbar-track,
        #sidebar-mobile nav::-webkit-scrollbar-button,
        aside nav::-webkit-scrollbar-thumb,
        aside nav::-webkit-scrollbar-track,
        aside nav::-webkit-scrollbar-button {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }

        .no-scrollbar,
        .scrollbar-none,
        nav.no-scrollbar,
        #sidebar-desktop nav,
        #sidebar-desktop,
        #sidebar-mobile nav,
        #sidebar-mobile,
        aside nav,
        aside {
            -ms-overflow-style: none !important;  /* IE and Edge */
            scrollbar-width: none !important;     /* Firefox and modern Chrome/Safari */
        }

        /* Scrollbar halus untuk main content bila diperlukan */
        .scrollbar-thin::-webkit-scrollbar { width: 5px; height: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 999px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

        /* Judul modul operasional responsif: 2 baris default, 1 baris saat maximize (>= 1440px / 2xl) */
        .card-title-br {
            display: block;
        }
        .card-title-space {
            display: none;
        }
        @media (min-width: 1440px) {
            .card-title-br {
                display: none !important;
            }
            .card-title-space {
                display: inline !important;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full h-[100dvh] max-h-[100dvh] overflow-hidden bg-gray-50 text-gray-900 antialiased selection:bg-orange-500 selection:text-white">
    <div id="app" class="h-full h-[100dvh] max-h-[100dvh] overflow-hidden"></div>
</body>
</html>
