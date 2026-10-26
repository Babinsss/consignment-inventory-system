@extends('layouts.app')

@section('title', 'Hospital User Roles & Staff Access - RMPH')

@section('content')
<div class="space-y-6">

    <!-- Header Block -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-rose-700 uppercase tracking-widest mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-rose-600"></span>
                    <span>Hospital Administration • Security & Access Control</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Hospital Staff User Roles & Permissions</h1>
                <p class="text-sm text-slate-500">Manage departmental staff roles, module authorizations, and consignment custodians.</p>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-600 to-indigo-600 hover:from-rose-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition space-x-1.5">
                    <span>+ Register New Staff User</span>
                </a>
            </div>
        </div>

        <!-- Role Count Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-purple-50 rounded-xl p-3 border border-purple-200/60">
                <div class="text-[11px] font-semibold text-purple-700 uppercase tracking-wide">Consignment Staff</div>
                <div class="text-xl font-black text-purple-900 mt-0.5">{{ $stats['medical_supply'] }}</div>
                <div class="text-[10px] text-purple-600">Medical Supplies & HECS</div>
            </div>

            <div class="bg-sky-50 rounded-xl p-3 border border-sky-200/60">
                <div class="text-[11px] font-semibold text-sky-700 uppercase tracking-wide">Pharmacy Staff</div>
                <div class="text-xl font-black text-sky-900 mt-0.5">{{ $stats['pharmacy'] }}</div>
                <div class="text-[10px] text-sky-600">Dispensary & Prescriptions</div>
            </div>

            <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-200/60">
                <div class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Finance & Audit</div>
                <div class="text-xl font-black text-emerald-900 mt-0.5">{{ $stats['finance'] }}</div>
                <div class="text-[10px] text-emerald-600">Consignment Settlements</div>
            </div>

            <div class="bg-rose-50 rounded-xl p-3 border border-rose-200/60">
                <div class="text-[11px] font-semibold text-rose-700 uppercase tracking-wide">Administrators</div>
                <div class="text-xl font-black text-rose-900 mt-0.5">{{ $stats['admin'] }}</div>
                <div class="text-[10px] text-rose-600">Chief of Hospital & IT</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex items-center space-x-2 w-full sm:w-auto">
            <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded-lg font-bold {{ empty($roleFilter) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">All ({{ $stats['total'] }})</a>
            <a href="{{ route('users.index', ['role' => \App\Models\User::ROLE_MEDICAL_SUPPLY]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $roleFilter === \App\Models\User::ROLE_MEDICAL_SUPPLY ? 'bg-purple-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Consignment Staff</a>
            <a href="{{ route('users.index', ['role' => \App\Models\User::ROLE_PHARMACY]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $roleFilter === \App\Models\User::ROLE_PHARMACY ? 'bg-sky-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Pharmacy</a>
            <a href="{{ route('users.index', ['role' => \App\Models\User::ROLE_FINANCE]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $roleFilter === \App\Models\User::ROLE_FINANCE ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Finance</a>
            <a href="{{ route('users.index', ['role' => \App\Models\User::ROLE_ADMIN]) }}" class="px-3 py-1.5 rounded-lg font-bold {{ $roleFilter === \App\Models\User::ROLE_ADMIN ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Admin</a>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="w-full sm:w-72">
            @if($roleFilter)
                <input type="hidden" name="role" value="{{ $roleFilter }}">
            @endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search staff name or department..." class="w-full text-xs rounded-xl border border-slate-300 pl-8 pr-3 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-200">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Role & Module Clearance</th>
                        <th class="py-3 px-4">Department & Position</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs ring-1 ring-slate-200">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border inline-block {{ $user->role_badge_color }}">
                                    {{ $user->role_label }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    @if($user->isAdmin())
                                        Universal clearance across all hospital operations
                                    @elseif($user->isMedicalSupplyStaff())
                                        Medical supplies, HECS stock cards, SPR/CO deliveries & issuances
                                    @elseif($user->isPharmacyStaff())
                                        Pharmacy stocks, FEFO drug batches, dispensing billing
                                    @elseif($user->isFinanceOfficer())
                                        Consignment settlements, accounts payable & StCG audits
                                    @endif
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $user->department ?: 'General Administration' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $user->title ?: 'Hospital Staff' }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('users.edit', $user) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                    Edit Role
                                </a>

                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Toggle active status for this staff member?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg transition {{ $user->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 italic">No users found matching your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
