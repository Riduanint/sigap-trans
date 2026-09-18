<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Sistem Kedinasan - SIGAP-TRANS KALSEL</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#EBEDE3] font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4">

    <!-- Tombol Kembali ke WebGIS Publik -->
    <div class="w-full max-w-md mb-4 flex items-center justify-between">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B1849] hover:text-[#124D1C] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Portal WebGIS</span>
        </a>
        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 bg-white/60 px-2 py-0.5 rounded-md border border-slate-200">
            Akses Kedinasan
        </span>
    </div>

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
        
        <!-- Header Kartu Login (Midnight Navy: #0B1849) -->
        <div class="bg-[#0B1849] p-7 text-center text-white border-b-4 border-[#E4B028]">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-[#124D1C] border-2 border-[#E4B028] flex items-center justify-center shadow-lg mb-3">
                <svg class="w-8 h-8 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
            </div>
            <h1 class="text-xl font-black tracking-tight text-white">
                SIGAP-TRANS <span class="text-[#E4B028]">KALSEL</span>
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Sistem Informasi Geospasial Administrasi & Persebaran Transmigrasi
            </p>
            <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider text-[#E4B028] bg-white/10 px-2.5 py-0.5 rounded-full">
                Disnakertrans Prov. Kalsel
            </span>
        </div>

        <!-- Formulir Autentikasi -->
        <div class="p-7">
            
            <!-- Flash Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4 text-xs">
                @csrf

                <!-- Email / NIP -->
                <div>
                    <label for="email" class="font-bold text-slate-700 block mb-1">
                        Alamat Email / Akun Kedinasan
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               placeholder="contoh: superadmin@kalselprov.go.id"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 pl-10 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#124D1C] focus:border-transparent transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="font-bold text-slate-700 block mb-1">
                        Kata Sandi Keamanan
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 pl-10 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#124D1C] focus:border-transparent transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" 
                               class="rounded border-slate-300 text-[#124D1C] focus:ring-[#124D1C]">
                        <span class="text-xs text-slate-600 font-medium">Ingat Sesi Saya</span>
                    </label>
                </div>

                <!-- Tombol Submit (Forest Green: #124D1C) -->
                <button type="submit" 
                        class="w-full bg-[#124D1C] hover:bg-emerald-800 text-white font-extrabold text-sm py-3 px-4 rounded-xl transition shadow-md shadow-[#124D1C]/20 flex items-center justify-center gap-2 mt-4">
                    <span>Masuk ke Ruang Kendali</span>
                    <svg class="w-4 h-4 text-[#E4B028]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

            <!-- Akun Pengujian Cepat untuk 4 Peran (1-Klik Isi Form) -->
            <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-500 bg-slate-50/90 p-3.5 rounded-xl border border-slate-200/60">
                <span class="font-extrabold text-[#0B1849] block mb-2 text-xs">Pilih Cepat Akun Pengujian (Klik untuk Otomatis Isi):</span>
                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    <button type="button" onclick="fillLogin('superadmin@kalselprov.go.id', 'password123')"
                            class="p-2 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-left transition font-medium">
                        <span class="font-bold text-[#0B1849] block">1. Super Admin</span>
                        <span class="text-slate-400 font-mono text-[9px]">superadmin@...</span>
                    </button>
                    <button type="button" onclick="fillLogin('operator.tapin@kalselprov.go.id', 'password123')"
                            class="p-2 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-left transition font-medium">
                        <span class="font-bold text-[#124D1C] block">2. Operator Tapin</span>
                        <span class="text-slate-400 font-mono text-[9px]">operator.tapin@...</span>
                    </button>
                    <button type="button" onclick="fillLogin('kadis@kalselprov.go.id', 'password123')"
                            class="p-2 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-left transition font-medium">
                        <span class="font-bold text-purple-900 block">3. Eksekutif (Kadis)</span>
                        <span class="text-slate-400 font-mono text-[9px]">kadis@...</span>
                    </button>
                    <button type="button" onclick="fillLogin('kanwil.bpn@atrbpn.go.id', 'password123')"
                            class="p-2 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-left transition font-medium">
                        <span class="font-bold text-amber-800 block">4. Mitra Kanwil BPN</span>
                        <span class="text-slate-400 font-mono text-[9px]">kanwil.bpn@...</span>
                    </button>
                </div>
                <div class="mt-2 text-[10px] text-slate-400 text-center font-mono">
                    Password seluruh akun: <strong>password123</strong>
                </div>
            </div>

            <script>
            function fillLogin(email, password) {
                document.getElementById('email').value = email;
                document.getElementById('password').value = password;
            }
            </script>

        </div>
    </div>

</body>
</html>
