<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // If not logged in, check if a demo session user exists, or log in default Consignment Staff
        if (!Auth::check()) {
            $defaultUser = User::where('role', User::ROLE_MEDICAL_SUPPLY)->first()
                ?? User::first();
            if ($defaultUser) {
                Auth::login($defaultUser);
            }
        }

        $user = Auth::user();

        if (!$user || !$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your staff account is inactive. Please contact the hospital administrator.');
        }

        // Admins have universal clearance
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the requested roles
        if (!empty($roles) && !$user->hasRole($roles)) {
            $allowedLabels = array_map(function ($r) {
                return match ($r) {
                    User::ROLE_ADMIN => 'Hospital Administrator',
                    User::ROLE_MEDICAL_SUPPLY => 'Consignment / Medical Supplies Staff',
                    User::ROLE_PHARMACY => 'Pharmacy Staff / Pharmacist',
                    User::ROLE_FINANCE => 'Hospital Finance / Consignment Auditor',
                    default => ucfirst(str_replace('_', ' ', $r)),
                };
            }, $roles);

            $message = sprintf(
                '🔒 Access Restricted: You are currently active as "%s" (%s). This module requires: %s.',
                $user->name,
                $user->role_label,
                implode(' or ', $allowedLabels)
            );

            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
