<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Presensi PNS</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS LOGIN PNS -->
    <link rel="stylesheet" href="{{ asset('css/login-pns.css') }}">
    
</head>
<body>

<div class="login-wrapper">
    <div class="login-box">

        <!-- HEADER -->
        <div class="login-header">
            <h1>Login Presensi</h1>
            <p>Sistem Presensi Wajah PNS</p>
        </div>

        <!-- ERROR -->
        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- FORM -->
        <form method="POST" action="{{ route('pns.login.submit') }}">
            @csrf

            <label for="nip">NIP</label>
            <input
                type="text"
                name="nip"
                id="nip"
                value="{{ old('nip') }}"
                placeholder="Masukkan NIP"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                placeholder="Masukkan password"
                required
            >

            <button type="submit">
                Login
            </button>
        </form>

        <!-- FOOTER -->
        <div class="login-footer">
            © Pemerintah Kabupaten Sragen
        </div>

    </div>
</div>

</body>
</html>
