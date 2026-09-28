<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) ($request->validate(['q' => 'nullable|string|max:100'])['q'] ?? ''));

        $users = User::query()
            ->withCount('orders')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_admin')
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => User::query()->count(),
            'admins' => User::query()->where('is_admin', true)->count(),
            'customers' => User::query()->where('is_admin', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'summary', 'search'));
    }

    public function show(User $user): View
    {
        $user->loadCount('orders');
        $orders = $user->orders()->withCount('items')->latest('ordered_at')->limit(10)->get();

        $stats = [
            'active' => $user->orders()->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
            'spent' => (int) $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
        ];

        return view('admin.users.show', compact('user', 'orders', 'stats'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean'],
        ]);

        if ($user->is($request->user()) && ! $validated['is_admin']) {
            return back()->withErrors(['is_admin' => 'Anda tidak dapat menurunkan peran akun sendiri.']);
        }

        $user->update(['is_admin' => $validated['is_admin']]);

        return back()->with('success', 'Peran akun '.$user->username.' diperbarui.');
    }
}
