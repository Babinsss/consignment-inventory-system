@extends('layouts.app')

@section('title', 'Edit Staff Role - ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800 flex items-center mb-1">
                &larr; Back to Staff Directory
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Staff Role & Permissions</h1>
            <p class="text-xs text-slate-500">Updating profile for {{ $user->name }} ({{ $user->email }}).</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                @error('name')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Hospital Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                    @error('email')<p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">User Role & Clearance *</label>
                    <select name="role" required class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600 font-semibold text-slate-800">
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
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
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Position / Title</label>
                    <input type="text" name="title" value="{{ old('title', $user->title) }}" class="w-full text-xs rounded-xl border border-slate-300 p-3 focus:ring-2 focus:ring-purple-600">
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Change Password (Leave blank to keep current)</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <input type="password" name="password" placeholder="New password" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                    <div>
                        <input type="password" name="password_confirmation" placeholder="Confirm new password" class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-purple-600">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center text-xs font-bold text-slate-800 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded text-purple-600 focus:ring-purple-500 mr-2">
                    <span>Account Active</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-purple-600/30 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
