<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Disty Akademi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="w-full max-w-md">
 <div class="bg-white border border-blue-100 p-8 shadow-sm">
        {{-- Logo Disty Akademi --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/disty-logo.png') }}" alt="Disty Akademi" class="h-12 w-auto object-contain">
        </div>

        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Selamat Datang</h1>
            <p class="text-xs text-gray-500 mt-1">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        @if($errors->any())
 <div class="mb-5 bg-red-50 border border-red-200 px-4 py-3 text-xs text-red-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="email" class="block font-semibold text-gray-700 mb-1.5">Email</label>
                <input id="email" name="email" type="email" required autofocus
                       value="{{ old('email') }}"
 class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                       placeholder="nama@distyakademi.com">
            </div>

            <div>
                <label for="password" class="block font-semibold text-gray-700 mb-1.5">Password</label>
                <input id="password" name="password" type="password" required
 class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-gray-600">
 <input type="checkbox" name="remember" class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 shadow-sm transition-colors text-xs mt-2">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-100 text-center">
            <p class="text-[11px] text-gray-400">
                © {{ date('Y') }} Disty Akademi. All rights reserved.
            </p>
        </div>
    </div>
</div>

</body>
</html>
