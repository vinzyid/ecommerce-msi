<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->withCount('items')
            ->latest('ordered_at')
            ->limit(5)
            ->get();

        $stats = [
            'total' => $user->orders()->count(),
            'active' => $user->orders()->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
            'spent' => (int) $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
        ];

        $cartCount = (int) $user->cartItems()->sum('quantity');
        $wishlistCount = (int) $user->wishlists()->count();
        $addresses = $user->addresses()->get();

        return view('account.show', compact('user', 'orders', 'stats', 'cartCount', 'wishlistCount', 'addresses'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
        ], [
            'username.required' => 'Nama pengguna wajib diisi.',
            'username.unique' => 'Nama pengguna sudah digunakan oleh akun lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'address' => ['required', 'string', 'min:10', 'max:1000'],
            'province' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'district' => ['required', 'string', 'max:80'],
            'postal_code' => ['required', 'string', 'max:10'],
        ], [
            'label.required' => 'Label alamat wajib diisi (mis. Rumah/Kantor).',
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
        ]);

        $user = $request->user();
        $isFirst = $user->addresses()->count() === 0;

        $user->addresses()->create($validated + ['is_default' => $isFirst]);

        return back()->with('success', 'Alamat baru berhasil disimpan.');
    }

    public function setDefaultAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Alamat utama berhasil diperbarui.');
    }

    public function deleteAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // Jika yang dihapus adalah alamat utama, jadikan alamat lain sebagai utama.
        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }
}
