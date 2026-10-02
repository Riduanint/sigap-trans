<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — SIGAP-TRANS KALSEL</title>

    <!-- Tipografi Atlas: Barlow Semi Condensed (judul) + Source Sans 3 (isi) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/css/atlas.css', 'resources/js/app.js'])
</head>
<body class="atlas-auth">

    <!-- Baris atas: kembali ke peta + penanda akses -->
    <div class="atlas-auth-top">
        <a href="{{ url('/') }}">
            <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg>
            <span>Kembali ke peta publik</span>
        </a>
        <span class="atlas-auth-tag">Akses kedinasan</span>
    </div>

    <!-- Kartu login Atlas -->
    <div class="atlas-auth-card">
        <div class="atlas-auth-brand">
            <span class="atlas-brand-mark"><x-admin.icon name="map" /></span>
            <span>
                <strong>SIGAP-TRANS</strong>
                <small>Disnakertrans Prov. Kalsel · Kalimantan Selatan</small>
            </span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="atlas-auth-body">
            @csrf

            @if(session('status'))
                <div class="atlas-alert atlas-alert--success" role="status">{{ session('status') }}</div>
            @endif

            <div class="atlas-field">
                <label for="email">Alamat email kedinasan</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="contoh: superadmin@kalselprov.go.id">
                @if($errors->has('email'))
                    <p style="color: var(--atlas-critical); font-size: 12px; margin-top: 4px;">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <div class="atlas-field">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                @if($errors->has('password'))
                    <p style="color: var(--atlas-critical); font-size: 12px; margin-top: 4px;">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <label class="atlas-auth-remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Ingat sesi saya</span>
            </label>

            <button type="submit" class="atlas-button atlas-button--primary" style="width: 100%;">
                Masuk ke ruang kerja
                <svg class="atlas-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </button>
        </form>

        @unless(app()->environment('production'))
        <div class="atlas-auth-foot">
            <div class="atlas-auth-test">
                <span style="font-weight: 600;">Akun pengujian — klik untuk mengisi formulir</span>
                <div class="atlas-auth-test-grid">
                    <button type="button" onclick="fillLogin('superadmin@kalselprov.go.id', 'password123')">
                        <strong>Super Admin</strong>
                        <code>superadmin@…</code>
                    </button>
                    <button type="button" onclick="fillLogin('operator.tapin@kalselprov.go.id', 'password123')">
                        <strong>Operator Tapin</strong>
                        <code>operator.tapin@…</code>
                    </button>
                    <button type="button" onclick="fillLogin('kadis@kalselprov.go.id', 'password123')">
                        <strong>Eksekutif (Kadis)</strong>
                        <code>kadis@…</code>
                    </button>
                    <button type="button" onclick="fillLogin('kanwil.bpn@atrbpn.go.id', 'password123')">
                        <strong>Mitra Kanwil BPN</strong>
                        <code>kanwil.bpn@…</code>
                    </button>
                </div>
                <span style="text-align: center;">Kata sandi seluruh akun: <code>password123</code></span>
            </div>
        </div>
        @endunless
    </div>

    <script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
    </script>

</body>
</html>
