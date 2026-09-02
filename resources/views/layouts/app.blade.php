<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pengajuan Kredit')  &middot; PT Capella Multidana</title>

    <!-- Tailwind CSS (CDN, dipakai agar project mudah dijalankan tanpa build step) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#4f46e5',
                            600: '#4338ca',
                            700: '#3730a3',
                        },
                    },
                },
            },
        }
    </script>

    <!-- Alpine.js (CDN) untuk interaksi modal & dialog konfirmasi -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-lg bg-brand-600 text-white flex items-center justify-center font-bold">CMD</div>
                <div>
                    <p class="text-sm text-slate-400 leading-none">PT Capella Multidana &middot; Internal Tool</p>
                    <h1 class="text-lg font-semibold text-slate-900 leading-tight">Sistem Pengajuan Pembiayaan</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-slate-400 py-6">
        Coding Test &middot; PT Capella Multidana
    </footer>
</body>
</html>
