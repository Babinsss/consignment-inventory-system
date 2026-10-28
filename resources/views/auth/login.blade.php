<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Staff Sign In - Roxas Memorial Provincial Hospital (HECS)</title>
    
    <!-- Local Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fallback CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 antialiased text-slate-800 bg-slate-950 font-sans selection:bg-purple-500 selection:text-white">
    <div class="max-w-md w-full space-y-5 sm:space-y-6" x-data="{ email: '', password: '' }">
        
        <!-- Hospital Branding Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-sky-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white mx-auto shadow-xl shadow-purple-600/30">
                <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-white tracking-tight">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h1>
                <p class="text-[11px] sm:text-xs font-bold text-sky-400 uppercase tracking-widest mt-0.5">Hospital Extensive Consignment System (HECS)</p>
                <p class="text-[11px] text-slate-400 mt-1">Capiz Provincial Government &bull; Role-Based Inventory & Finance Portal</p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base sm:text-lg font-bold text-slate-900">Hospital Staff Sign In</h2>
                <p class="text-xs text-slate-500">Sign in to access your assigned departmental inventory module</p>
            </div>

            @if(session('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center space-x-2">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official Hospital Email</label>
                    <input type="email" name="email" x-model="email" required autofocus
                           placeholder="staff@rmph.capiz.gov.ph"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 focus:outline-none transition">
                    @error('email')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                        <span class="text-[10px] text-slate-400">Default: password</span>
                    </div>
                    <input type="password" name="password" x-model="password" required
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                           class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 focus:outline-none transition">
                    @error('password')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded text-purple-600 focus:ring-purple-500 mr-2">
                        <span>Remember session</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 via-indigo-600 to-sky-600 hover:from-purple-700 hover:to-sky-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-purple-600/30 transition active:scale-98">
                    Sign In to Portal &rarr;
                </button>
            </form>

            <!-- 1-Click Fast Persona Switcher / Login Shortcut for Evaluation -->
            <div class="pt-4 border-t border-slate-100">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 text-center">
                    1-Click Staff Login (Evaluation Demo)
                </span>
                <div class="space-y-1.5 max-h-56 overflow-y-auto pr-0.5 custom-scroll">
                    @foreach($sampleUsers as $u)
                        <button type="button" 
                                @click="email = '{{ $u->email }}'; password = 'password'; $nextTick(() => $el.closest('form, div').parentElement.querySelector('form').submit())"
                                class="w-full p-2.5 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 flex items-center justify-between text-left transition group">
                            <div class="truncate mr-2">
                                <span class="block text-xs font-bold text-slate-900 group-hover:text-purple-700 truncate">{{ $u->name }}</span>
                                <span class="block text-[10px] text-slate-500 truncate">{{ $u->title }} &bull; {{ $u->department }}</span>
                            </div>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border flex-shrink-0 {{ $u->role_badge_color }}">
                                {{ $u->role_label }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] sm:text-xs text-slate-500">
            Hospital Extensive Consignment System &bull; Roxas Memorial Provincial Hospital
        </p>
    </div>
</body>
</html>
