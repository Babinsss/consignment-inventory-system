<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Hospital Extensive Consignment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800">
    <div class="max-w-md w-full space-y-6" x-data="{ email: '', password: '' }">
        <!-- Hospital Branding Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 via-purple-600 to-indigo-600 flex items-center justify-center text-white mx-auto shadow-xl shadow-purple-500/20">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h1 class="text-xl font-black text-white tracking-tight">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h1>
            <p class="text-xs font-semibold text-purple-300 uppercase tracking-widest">Hospital Extensive Consignment System (HECS)</p>
            <p class="text-xs text-slate-400">Capiz Provincial Government • Role-Based Inventory & Finance Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl p-8 shadow-2xl border border-slate-800 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900">Hospital Staff Sign In</h2>
                <p class="text-xs text-slate-500">Sign in to access your assigned departmental inventory module</p>
            </div>

            @if(session('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official Hospital Email</label>
                    <input type="email" name="email" x-model="email" required autofocus
                           placeholder="staff@rmph.capiz.gov.ph"
                           class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition">
                    @error('email')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                        <span class="text-[10px] text-slate-400">Default: password</span>
                    </div>
                    <input type="password" name="password" x-model="password" required
                           placeholder="••••••••"
                           class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition">
                    @error('password')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded text-purple-600 focus:ring-purple-500 mr-2">
                        <span>Remember session</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-600/30 transition">
                    Sign In to Portal →
                </button>
            </form>

            <!-- 1-Click Fast Persona Switcher / Login Shortcut for Evaluation -->
            <div class="pt-4 border-t border-slate-100">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">
                    1-Click Staff Login (Evaluation Demo)
                </span>
                <div class="space-y-1.5">
                    @foreach($sampleUsers as $u)
                        <button type="button" 
                                @click="email = '{{ $u->email }}'; password = 'password'; $nextTick(() => $el.closest('form, div').parentElement.querySelector('form').submit())"
                                class="w-full p-2.5 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 flex items-center justify-between text-left transition group">
                            <div class="truncate">
                                <span class="block text-xs font-bold text-slate-900 group-hover:text-purple-700 truncate">{{ $u->name }}</span>
                                <span class="block text-[10px] text-slate-500 truncate">{{ $u->title }} • {{ $u->department }}</span>
                            </div>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border flex-shrink-0 {{ $u->role_badge_color }}">
                                {{ $u->role_label }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500">
            Hospital Extensive Consignment System • Roxas Memorial Provincial Hospital
        </p>
    </div>
</body>
</html>
