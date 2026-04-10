<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | APOTEK SUMBER SEHAT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-container": "#c4d2ff",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed-variant": "#812800",
                        "on-tertiary-container": "#ffc6b2",
                        "tertiary-fixed": "#ffdbcf",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed": "#021945",
                        "surface-container": "#edeeef",
                        "on-surface-variant": "#434654",
                        "surface-variant": "#e1e3e4",
                        "secondary-fixed": "#dae2ff",
                        "primary-fixed": "#dae2ff",
                        "tertiary-container": "#a33500",
                        "surface-dim": "#d9dadb",
                        "primary-container": "#0052cc",
                        "tertiary-fixed-dim": "#ffb59b",
                        "on-secondary-container": "#415382",
                        "on-primary-fixed": "#001848",
                        "surface-tint": "#0c56d0",
                        "on-primary-fixed-variant": "#0040a2",
                        "inverse-surface": "#2e3132",
                        "surface-bright": "#f8f9fa",
                        "outline": "#737685",
                        "on-error": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "secondary-container": "#b6c8fe",
                        "surface": "#f8f9fa",
                        "surface-container-highest": "#e1e3e4",
                        "background": "#f8f9fa",
                        "secondary": "#4c5d8d",
                        "on-secondary": "#ffffff",
                        "on-tertiary-fixed": "#380d00",
                        "outline-variant": "#c3c6d6",
                        "error": "#ba1a1a",
                        "on-error-container": "#93000a",
                        "primary-fixed-dim": "#b2c5ff",
                        "tertiary": "#7b2600",
                        "inverse-on-surface": "#f0f1f2",
                        "surface-container-low": "#f3f4f5",
                        "on-secondary-fixed-variant": "#344573",
                        "on-surface": "#191c1d",
                        "on-background": "#191c1d",
                        "primary": "#003d9b",
                        "secondary-fixed-dim": "#b4c5fb",
                        "inverse-primary": "#b2c5ff"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"],
                        label: ["Inter"]
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24;
        }
    </style>
</head>
<body class="bg-background font-body text-on-surface min-h-screen flex items-center justify-center overflow-hidden">
    <div class="fixed inset-0 z-0">
        <img class="w-full h-full object-cover" alt="Pharmacy interior background" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA0ndPP_T25NwhfIk8rkaYaboXUOpLsZI_peBzmzAfBsRFVrJCQlVnEgaqRJMyZum3fyBzRRet3t0A_hMdmjxqrW1N0HLESVf1fmbN66pxrJhfNaos_cFmVUZXhHZurmLUtF8F5uFNqrdTwCTupCvPz04duVBtypJ7gQVMReBzjvL9FOwPx3a8UyFu1UVsy5rOGpUbFyFwSfIn_IA4IdcUj4rJg45Qq-bFWnMoul0tSZXuZBfaZRTDit50xqfPAOPcTe3rCaMOFec4" />
        <div class="absolute inset-0 bg-primary/40 backdrop-blur-[2px]"></div>
    </div>

    <main class="relative z-10 w-full max-w-screen-xl mx-auto flex flex-col md:flex-row items-center justify-center px-6 gap-12">
        <div class="hidden lg:flex flex-col flex-1 text-white max-w-xl">
            <div class="relative overflow-hidden rounded-[1.75rem] border border-white/20 bg-white/10 p-8 backdrop-blur-md shadow-[0_24px_60px_-16px_rgba(0,0,0,0.45)]">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-1.5">
                    <span class="material-symbols-outlined text-base text-primary-fixed">workspace_premium</span>
                    <span class="font-label text-[11px] font-bold uppercase tracking-[0.22em] text-white/85">Portal Apotek</span>
                </div>

                <h1 class="mt-6 font-headline text-[3.35rem] font-extrabold leading-[1.05] tracking-[-0.02em] drop-shadow-[0_10px_24px_rgba(0,0,0,0.35)]">
                    <span class="block text-white">Selamat Datang di</span>
                    <span class="block bg-gradient-to-r from-primary-fixed via-secondary-fixed-dim to-tertiary-fixed bg-clip-text text-transparent">
                        Kelola Database
                    </span>
                    <span class="block text-white/95">Apotik Sehat</span>
                </h1>

                <p class="mt-5 max-w-md text-[15px] leading-relaxed text-white/80">
                    Kelola data obat, stok, transaksi, dan laporan dalam satu dashboard yang rapi, cepat, dan akurat.
                </p>

                <div class="pointer-events-none absolute -right-14 -top-14 h-44 w-44 rounded-full bg-primary-fixed/20 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-16 -left-8 h-40 w-40 rounded-full bg-tertiary-fixed/20 blur-2xl"></div>
            </div>
        </div>

        <div class="w-full max-w-md">
            <div class="bg-surface-container-lowest/80 backdrop-blur-2xl rounded-[2rem] p-8 md:p-12 shadow-[0_32px_64px_-12px_rgba(0,0,0,0.1)] border border-white/20">
                <div class="flex flex-col items-center mb-10 text-center">
                    <div class="w-14 h-14 bg-primary rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-white text-3xl">medical_services</span>
                    </div>
                    <h2 class="font-headline text-2xl font-black text-primary tracking-tight">APOTEK SUMBER SEHAT</h2>
                    <p class="text-on-surface-variant text-sm font-label uppercase tracking-[0.1em] mt-1">Pharmacy Portal</p>
                </div>

                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-emerald-50 text-emerald-700 px-4 py-3 text-sm font-semibold">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-error-container text-on-error-container px-4 py-3 text-sm font-semibold">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form class="space-y-6" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    @php $defaultRole = old('role', \App\Models\User::ROLE_MASTER_ADMIN); @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
                        <label class="relative flex items-center justify-center p-3 rounded-xl bg-surface-container cursor-pointer border-2 border-transparent transition-all hover:bg-surface-container-high has-[:checked]:bg-primary-container has-[:checked]:text-white">
                            <input class="hidden" type="radio" name="role" value="{{ \App\Models\User::ROLE_DOKTER }}" @checked($defaultRole === \App\Models\User::ROLE_DOKTER) />
                            <span class="font-label text-[10px] font-bold uppercase tracking-wider text-center">Dokter</span>
                        </label>
                        <label class="relative flex items-center justify-center p-3 rounded-xl bg-surface-container cursor-pointer border-2 border-transparent transition-all hover:bg-surface-container-high has-[:checked]:bg-primary-container has-[:checked]:text-white">
                            <input class="hidden" type="radio" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}" @checked($defaultRole === \App\Models\User::ROLE_ADMIN) />
                            <span class="font-label text-[10px] font-bold uppercase tracking-wider text-center">Admin</span>
                        </label>
                        <label class="relative flex items-center justify-center p-3 rounded-xl bg-surface-container cursor-pointer border-2 border-transparent transition-all hover:bg-surface-container-high has-[:checked]:bg-primary-container has-[:checked]:text-white">
                            <input class="hidden" type="radio" name="role" value="{{ \App\Models\User::ROLE_KASIR }}" @checked($defaultRole === \App\Models\User::ROLE_KASIR) />
                            <span class="font-label text-[10px] font-bold uppercase tracking-wider text-center">Kasir</span>
                        </label>
                        <label class="relative flex items-center justify-center p-3 rounded-xl bg-surface-container cursor-pointer border-2 border-transparent transition-all hover:bg-surface-container-high has-[:checked]:bg-primary-container has-[:checked]:text-white">
                            <input class="hidden" type="radio" name="role" value="{{ \App\Models\User::ROLE_MASTER_ADMIN }}" @checked($defaultRole === \App\Models\User::ROLE_MASTER_ADMIN) />
                            <span class="font-label text-[10px] font-bold uppercase tracking-wider text-center">Master Admin</span>
                        </label>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant px-1" for="identifier">Email / Nama</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg">person</span>
                            <input
                                class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all text-on-surface placeholder:text-outline/50 font-medium"
                                id="identifier"
                                name="identifier"
                                type="text"
                                required
                                value="{{ old('identifier') }}"
                                placeholder="contoh: kasir@apotik.test"
                                autocomplete="username"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password">Password</label>
                            <a class="text-xs font-bold text-primary hover:text-primary-container transition-colors" href="#">Forgot?</a>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg">lock</span>
                            <input
                                class="w-full pl-12 pr-4 py-4 bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary/20 focus:bg-surface-container-lowest transition-all text-on-surface placeholder:text-outline/50 font-medium"
                                id="password"
                                name="password"
                                type="password"
                                required
                                placeholder="••••••••"
                                autocomplete="current-password"
                            />
                        </div>
                    </div>

                    <button class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-white font-bold rounded-xl shadow-lg shadow-primary/30 hover:scale-[0.98] active:scale-95 transition-all flex items-center justify-center gap-2 mt-4" type="submit">
                        <span>Login to Dashboard</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-on-surface-variant text-xs font-medium">
                        Need assistance? <a class="text-primary font-bold hover:underline" href="#">Contact Support</a>
                    </p>
                    <p class="text-on-surface-variant/80 text-[11px] font-medium mt-3">
                        Seeder login utama: <span class="font-bold">dokter/admin/kasir/masteradmin@apotik.test</span> | Password:
                        <span class="font-bold">rahasia123</span>
                    </p>
                </div>
            </div>

            <p class="text-center mt-8 text-white/60 text-xs font-label uppercase tracking-[0.2em] pointer-events-none">
                Solusi Manajemen Apotek Modern
            </p>
        </div>
    </main>

    <div class="fixed bottom-0 right-0 p-12 hidden md:block">
        <div class="flex flex-col items-end gap-1">
            <span class="text-white/40 text-[10px] font-label uppercase tracking-widest">Version 2.4.0</span>
            <span class="text-white/40 text-[10px] font-label uppercase tracking-widest">© 2024 APOTEK SUMBER SEHAT</span>
        </div>
    </div>
</body>
</html>
