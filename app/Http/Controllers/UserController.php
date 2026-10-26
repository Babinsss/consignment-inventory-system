<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of hospital users and their roles
     */
    public function index(Request $request)
    {
        $roleFilter = $request->input('role');
        $search = $request->input('search');

        $query = User::query()->orderBy('name');

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'medical_supply' => User::where('role', User::ROLE_MEDICAL_SUPPLY)->count(),
            'pharmacy' => User::where('role', User::ROLE_PHARMACY)->count(),
            'finance' => User::where('role', User::ROLE_FINANCE)->count(),
            'admin' => User::where('role', User::ROLE_ADMIN)->count(),
        ];

        return view('users.index', compact('users', 'stats', 'roleFilter', 'search'));
    }

    /**
     * Show form to create a new user
     */
    public function create()
    {
        $roles = [
            User::ROLE_MEDICAL_SUPPLY => 'Consignment & Medical Supplies Staff',
            User::ROLE_PHARMACY => 'Pharmacy Staff / Pharmacist',
            User::ROLE_FINANCE => 'Hospital Finance / Consignment Auditor',
            User::ROLE_ADMIN => 'Hospital Administrator',
        ];

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_MEDICAL_SUPPLY, User::ROLE_PHARMACY, User::ROLE_FINANCE])],
            'department' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:100',
            'password' => 'required|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        return redirect()->route('users.index')
            ->with('success', "Staff member {$user->name} successfully registered with role: {$user->role_label}.");
    }

    /**
     * Show form to edit user
     */
    public function edit(User $user)
    {
        $roles = [
            User::ROLE_MEDICAL_SUPPLY => 'Consignment & Medical Supplies Staff',
            User::ROLE_PHARMACY => 'Pharmacy Staff / Pharmacist',
            User::ROLE_FINANCE => 'Hospital Finance / Consignment Auditor',
            User::ROLE_ADMIN => 'Hospital Administrator',
        ];

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update user details
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_MEDICAL_SUPPLY, User::ROLE_PHARMACY, User::ROLE_FINANCE])],
            'department' => 'nullable|string|max:100',
            'title' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', "Staff account for {$user->name} successfully updated.");
    }

    /**
     * Toggle active status or delete
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own logged-in account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $action = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$action}.");
    }
}
