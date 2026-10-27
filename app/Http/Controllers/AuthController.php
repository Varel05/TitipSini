<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm(): View
    {
        return view('pages.auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Akun Anda sedang dinonaktifkan. Hubungi admin.']);
            }

            return match ($user->role) {
                UserRole::Vendor => redirect()->intended(route('vendor.dashboard')),
                UserRole::Admin, UserRole::Superadmin => redirect()->intended(route('admin.dashboard')),
                default => redirect()->intended(route('booking.index')),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Quick login helper for demonstration and development convenience.
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $targetRole = match ($role) {
            'superadmin' => UserRole::Superadmin,
            'admin' => UserRole::Admin,
            'vendor' => UserRole::Vendor,
            default => UserRole::Customer,
        };

        $user = User::where('role', $targetRole)->first();

        if (! $user) {
            return back()->with('error', "Pengguna demo dengan role {$role} belum dibuat.");
        }

        Auth::login($user);
        request()->session()->regenerate();

        return match ($targetRole) {
            UserRole::Vendor => redirect()->route('vendor.dashboard')->with('success', "Login demo sebagai {$user->name} (Mitra Vendor)"),
            UserRole::Admin, UserRole::Superadmin => redirect()->route('admin.dashboard')->with('success', "Login demo sebagai {$user->name} ({$targetRole->label()})"),
            default => redirect()->route('booking.index')->with('success', "Login demo sebagai {$user->name} (Customer)"),
        };
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berhasil keluar dari akun.');
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm(): View
    {
        return view('pages.auth.register');
    }

    /**
     * Handle registration for customers or prospective vendors.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:customer,vendor'],
            // Specific vendor registration inputs
            'business_name' => ['required_if:role,vendor', 'nullable', 'string', 'max:150'],
            'address' => ['required_if:role,vendor', 'nullable', 'string', 'max:255'],
        ]);

        $role = $validated['role'] === 'vendor' ? UserRole::Vendor : UserRole::Customer;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'is_active' => true,
        ]);

        if ($role === UserRole::Customer) {
            CustomerProfile::create([
                'user_id' => $user->id,
            ]);
        } else {
            VendorProfile::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'] ?? $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? 'Alamat belum diatur',
                'city' => 'Jakarta',
                'verification_status' => VendorVerificationStatus::Unverified,
                'is_open' => false,
            ]);
        }

        Auth::login($user);

        if ($role === UserRole::Vendor) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'Selamat datang! Akun Mitra Vendor berhasil didaftarkan. Lengkapi profil dan ajukan verifikasi KYC Anda.');
        }

        return redirect()->route('catalog.index')
            ->with('success', 'Akun berhasil dibuat! Silakan temukan tempat penitipan terdekat.');
    }
}
