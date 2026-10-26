@extends('layouts.app')

@section('title', 'Register Staff User - RMPH')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800 flex items-center mb-1">
                &larr; Back to Staff Directory
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Register New Hospital Staff</h1>
            <p class="text-xs text-slate-500">Assign departmental role and module access clearance.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Maria Clara Santos, RN" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                @error('name')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hospital Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="staff@rmph.capiz.gov.ph" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                    @error('email')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Designated User Role *</label>
                    <select name="role" required class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 font-semibold text-slate-800">
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Department / Unit</label>
                    <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Central Supply Room (CSR) / HDU" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Official Position / Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Administrative Aide III (Consignment Custodian)" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                    @error('password')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required placeholder="Re-type password" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center text-xs font-bold text-slate-800 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded text-purple-600 focus:ring-purple-500 mr-2">
                    <span>Activate account immediately</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-600/30 transition">
                    Save Staff User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
