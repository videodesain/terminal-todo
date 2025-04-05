<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\Platform;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SocialAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialAccount::with('platform')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->platform_id, function ($query, $platformId) {
                return $query->where('platform_id', $platformId);
            })
            ->when($request->status === 'active', function ($query) {
                return $query->where('is_active', true);
            })
            ->when($request->status === 'inactive', function ($query) {
                return $query->where('is_active', false);
            });

        return Inertia::render('SocialAccounts/Index', [
            'accounts' => $query->paginate(10),
            'platforms' => Platform::where('is_active', true)->get(),
            'filters' => $request->only(['search', 'platform_id', 'status'])
        ]);
    }

    public function create()
    {
        return Inertia::render('SocialAccounts/Create', [
            'platforms' => Platform::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'profile_url' => 'required|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        SocialAccount::create($validated);

        return redirect()->route('social-accounts.index')
            ->with('message', 'Akun berhasil ditambahkan');
    }

    public function edit(SocialAccount $socialAccount)
    {
        $user = auth()->user();
        
        return Inertia::render('SocialAccounts/Edit', [
            'account' => $socialAccount,
            'platforms' => Platform::where('is_active', true)->get(),
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'roles' => $user->roles ? $user->roles->pluck('name')->toArray() : [],
                    'permissions' => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name')->toArray() : [],
                    'status' => $user->status,
                    'email_verified_at' => $user->email_verified_at,
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->diffForHumans() : null,
                    'is_admin' => method_exists($user, 'hasRole') ? $user->hasRole('Super Admin') : false,
                    'is_content_manager' => method_exists($user, 'hasRole') ? $user->hasRole('Content Manager') : false
                ]
            ]
        ]);
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        $validated = $request->validate([
            'platform_id' => 'required|exists:platforms,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'profile_url' => 'required|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $socialAccount->update($validated);

        return redirect()->route('social-accounts.index')
            ->with('message', 'Akun berhasil diperbarui');
    }

    public function destroy(SocialAccount $socialAccount)
    {
        $socialAccount->delete();

        return redirect()->route('social-accounts.index')
            ->with('message', 'Akun berhasil dihapus');
    }

    public function toggleStatus(SocialAccount $socialAccount)
    {
        $socialAccount->update([
            'is_active' => !$socialAccount->is_active
        ]);

        return redirect()->back()
            ->with('message', 'Status akun berhasil diperbarui');
    }
} 