<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Presensi PNS | Kabupaten Sragen</title>
    
    <!-- Google Fonts & Tailwind CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans min-h-screen flex items-center justify-center p-4 antialiased text-slate-800">

    <div class="w-full max-w-sm">
        
        <!-- CARD CONTAINER -->
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 p-8">
            
            <!-- LOGO & HEADER -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-600 mb-3 border border-blue-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-900">E-Presensi PNS</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pemerintah Kabupaten Sragen</p>
            </div>

            <!-- ERROR ALERT -->
            @if ($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl p-3 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="{{ route('pns.login.submit') }}" class="space-y-4">
                @csrf

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        NIP
                    </label>
                    <input
                        type="text"
                        name="nip"
                        id="nip"
                        value="{{ old('nip') }}"
                        placeholder="Masukkan NIP"
                        required
                        autofocus
                        class="w-full bg-slate-50 border border-slate-300 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-slate-900 text-sm rounded-xl px-3.5 py-2.5 outline-none transition-all placeholder:text-slate-400"
                    >
                </div>

                <!-- PASSWORD -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            required
                            class="w-full bg-slate-50 border border-slate-300 focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 text-slate-900 text-sm rounded-xl px-3.5 py-2.5 pr-10 outline-none transition-all placeholder:text-slate-400"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- SUBMIT -->
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-2.5 px-4 rounded-xl text-sm shadow-md shadow-blue-500/20 transition-all mt-2"
                >
                    Masuk
                </button>
            </form>

        </div>

        <!-- FOOTER -->
        <div class="text-center mt-6">
            <p class="text-[11px] text-slate-400">
                &copy; {{ date('Y') }} Pemerintah Kabupaten Sragen
            </p>
        </div>

    </div>

    <!-- SCRIPT LIHAT PASSWORD -->
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />`;
            } else {
                pwd.type = 'password';
                eye.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        }
    </script>
</body>
</html>
