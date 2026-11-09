@extends('layouts.app')

@section('title', 'Akun Saya — '.config('app.name'))

@section('content')
<div class="container-page pt-6">
    <div class="mx-auto max-w-5xl">
        {{-- Hero Profil --}}
        <header class="overflow-hidden rounded-[1.75rem] bg-ink-50 p-6 text-ink-900 sm:p-8">
            <span class="text-xs font-medium text-brand-600">Akun / Pusat Pelanggan</span>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-ink-900 text-2xl font-semibold text-white">
                    {{ $user->initials() }}
                </span>
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl font-semibold tracking-tight sm:text-2xl">Halo, {{ $user->username }} 👋</h1>
                    <p class="mt-0.5 truncate text-sm text-ink-500">{{ $user->email }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center gap-1 rounded-full bg-success-500/10 px-2.5 py-1 font-medium text-success-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span> Akun aktif
                        </span>
                        <span class="rounded-full bg-white px-2.5 py-1 font-medium text-ink-600 ring-1 ring-ink-100">
                            {{ $user->is_admin ? 'Admin' : 'Pelanggan' }}
                        </span>
                        <span class="rounded-full bg-white px-2.5 py-1 font-medium text-ink-600 ring-1 ring-ink-100">
                            Bergabung {{ $user->created_at->translatedFormat('M Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        {{-- Kartu Statistik --}}
        <section class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="Ringkasan aktivitas akun">
            @foreach ([
                ['Total Pesanan', $stats['total'], 'i-receipt', 'text-brand-600 bg-brand-50'],
                ['Sedang Diproses', $stats['active'], 'i-truck', 'text-amber-600 bg-amber-50'],
                ['Wishlist', $wishlistCount, 'i-heart', 'text-danger-600 bg-danger-50'],
                ['Total Belanja', 'Rp'.number_format($stats['spent'], 0, ',', '.'), 'i-wallet', 'text-emerald-600 bg-emerald-50'],
            ] as [$label, $value, $icon, $badge])
                <div class="rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $badge }}">
                        <svg class="h-5 w-5"><use href="#{{ $icon }}"/></svg>
                    </span>
                    <span class="mt-2.5 block text-[11px] font-semibold text-ink-500">{{ $label }}</span>
                    <strong class="mt-0.5 block truncate text-lg font-semibold tracking-tight text-ink-900">{{ $value }}</strong>
                </div>
            @endforeach
        </section>

        @if (session('success'))
            <div class="mt-5 flex items-center gap-2.5 rounded-xl border border-success-500/20 bg-success-500/10 px-4 py-3 text-sm font-semibold text-success-600" role="alert">
                <svg class="h-5 w-5 shrink-0"><use href="#i-check-circle"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_300px]">
            {{-- Konten Tab --}}
            <section>
                {{-- Pintasan status pesanan --}}
                <div class="mb-4 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-ink-900">Pesanan Saya</h2>
                        <a href="{{ route('orders.index') }}" class="text-xs font-medium text-brand-600 hover:underline">Lihat semua &rsaquo;</a>
                    </div>
                    <div class="mt-3 grid grid-cols-5 gap-1">
                        @foreach ([
                            ['pending', 'Menunggu', 'i-clock'],
                            ['processing', 'Diproses', 'i-package'],
                            ['shipped', 'Dikirim', 'i-truck'],
                            ['completed', 'Selesai', 'i-check-circle'],
                            ['cancelled', 'Batal', 'i-close'],
                        ] as [$shortcutStatus, $shortcutLabel, $shortcutIcon])
                            <a href="{{ route('orders.index', ['status' => $shortcutStatus]) }}" class="flex flex-col items-center gap-1.5 rounded-xl px-1 py-2 text-center transition hover:bg-ink-50">
                                <span class="relative">
                                    <svg class="h-6 w-6 text-ink-700" aria-hidden="true"><use href="#{{ $shortcutIcon }}"/></svg>
                                    @if (($orderCounts[$shortcutStatus] ?? 0) > 0)
                                        <span class="absolute -right-2 -top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-ink-900 px-1 text-[10px] font-semibold text-white">{{ $orderCounts[$shortcutStatus] }}</span>
                                    @endif
                                </span>
                                <span class="text-[11px] text-ink-600 sm:text-xs">{{ $shortcutLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Tab Nav --}}
                <div class="no-scrollbar flex gap-1.5 overflow-x-auto rounded-2xl border border-ink-100 bg-white p-1.5 shadow-card">
                    @foreach ([
                        ['profil', 'Profil Saya', 'i-user'],
                        ['alamat', 'Alamat Saya', 'i-map-pin'],
                        ['keamanan', 'Keamanan', 'i-lock'],
                        ['pesanan', 'Riwayat Pesanan', 'i-receipt'],
                    ] as [$tab, $tabLabel, $tabIcon])
                        <button type="button" data-account-tab="{{ $tab }}"
                                class="account-tab flex flex-1 shrink-0 items-center justify-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition {{ $loop->first ? 'bg-ink-900 text-white' : 'text-ink-600 hover:bg-ink-50' }}">
                            <svg class="h-4 w-4"><use href="#{{ $tabIcon }}"/></svg>
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Tab: Profil --}}
                <div data-account-panel="profil" class="account-panel mt-4">
                    <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <svg class="h-5 w-5"><use href="#i-user"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-semibold text-ink-900">Data Profil</h2>
                                <p class="text-xs text-ink-500">Perbarui nama pengguna dan alamat email akunmu.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('account.profile') }}" class="mt-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="username" class="block text-sm font-semibold text-ink-700">Nama pengguna</label>
                                <input id="username" name="username" value="{{ old('username', $user->username) }}"
                                       class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                @error('username')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-semibold text-ink-700">Alamat email</label>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                                       class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                @error('email')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center gap-3 pt-1">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-ink-700">
                                    <svg class="h-4 w-4"><use href="#i-check"/></svg>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tab: Alamat Saya --}}
                <div data-account-panel="alamat" class="account-panel mt-4 hidden">
                    <div class="space-y-4">
                        {{-- Daftar Alamat Tersimpan --}}
                        <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card sm:p-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <svg class="h-5 w-5"><use href="#i-map-pin"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-base font-semibold text-ink-900">Daftar Alamat Pengiriman</h2>
                                    <p class="text-xs text-ink-500">Alamat utama otomatis dipakai saat checkout &amp; bisa diubah kapan saja.</p>
                                </div>
                            </div>

                            @if ($addresses->isEmpty())
                                <div class="mt-5 rounded-xl border border-dashed border-ink-200 py-8 text-center">
                                    <p class="font-semibold text-ink-700">Belum ada alamat tersimpan</p>
                                    <p class="mt-1 text-sm text-ink-500">Tambahkan alamat pertamamu di bawah agar checkout berikutnya lebih cepat.</p>
                                </div>
                            @else
                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    @foreach ($addresses as $addr)
                                        <div class="relative flex flex-col rounded-xl border-2 p-4 {{ $addr->is_default ? 'border-brand-500 bg-brand-50/40' : 'border-ink-100' }}">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="text-sm font-bold text-ink-900">{{ $addr->label }}</span>
                                                @if ($addr->is_default)
                                                    <span class="rounded bg-ink-900 px-2 py-0.5 text-[10px] font-medium text-white">Alamat Utama</span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-sm font-bold text-ink-800">{{ $addr->recipient_name }}</p>
                                            <p class="text-xs text-ink-500">{{ $addr->phone }}</p>
                                            <p class="mt-1.5 text-xs leading-relaxed text-ink-600">{{ $addr->summary() }}</p>

                                            <div class="mt-3.5 flex flex-wrap items-center gap-2 border-t border-ink-100 pt-3">
                                                @unless ($addr->is_default)
                                                    <form method="POST" action="{{ route('account.addresses.default', $addr) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-bold text-brand-700 transition hover:bg-brand-50">
                                                            Jadikan Utama
                                                        </button>
                                                    </form>
                                                @endunless
                                                <form method="POST" action="{{ route('account.addresses.destroy', $addr) }}"
                                                      onsubmit="return confirm('Hapus alamat ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-danger-600 transition hover:border-danger-300 hover:bg-danger-500/5">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Form Tambah Alamat Baru --}}
                        <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card sm:p-6">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                    <svg class="h-5 w-5"><use href="#i-plus"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-base font-semibold text-ink-900">Tambah Alamat Baru</h2>
                                    <p class="text-xs text-ink-500">Isi alamat untuk digunakan pada pembelian berikutnya.</p>
                                </div>
                            </div>

                            @if ($errors->any())
                                <div class="mt-4 rounded-xl border border-danger-500/20 bg-danger-500/10 px-4 py-3 text-xs font-semibold text-danger-600">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('account.addresses.store') }}" class="mt-5 space-y-4">
                                @csrf
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label for="label" class="block text-sm font-semibold text-ink-700">Label</label>
                                        <input id="label" name="label" value="{{ old('label', 'Rumah') }}" placeholder="Rumah / Kantor"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div>
                                        <label for="recipient_name" class="block text-sm font-semibold text-ink-700">Nama penerima</label>
                                        <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $user->username) }}"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-semibold text-ink-700">Nomor telepon</label>
                                        <input id="phone" name="phone" value="{{ old('phone') }}"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-semibold text-ink-700">Alamat lengkap</label>
                                    <textarea id="address" name="address" rows="2"
                                              class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">{{ old('address') }}</textarea>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-4">
                                    <div>
                                        <label for="province" class="block text-sm font-semibold text-ink-700">Provinsi</label>
                                        <input id="province" name="province" value="{{ old('province') }}"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div>
                                        <label for="city" class="block text-sm font-semibold text-ink-700">Kota/Kab.</label>
                                        <input id="city" name="city" value="{{ old('city') }}"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div>
                                        <label for="district" class="block text-sm font-semibold text-ink-700">Kecamatan</label>
                                        <input id="district" name="district" value="{{ old('district') }}"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                    <div>
                                        <label for="postal_code" class="block text-sm font-semibold text-ink-700">Kode pos</label>
                                        <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric"
                                               class="mt-1.5 w-full rounded-xl border border-ink-200 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    </div>
                                </div>

                                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-ink-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-ink-700">
                                    <svg class="h-4 w-4"><use href="#i-check"/></svg>
                                    Simpan Alamat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tab: Keamanan --}}
                <div data-account-panel="keamanan" class="account-panel mt-4 hidden">
                    <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5"><use href="#i-lock"/></svg>
                            </span>
                            <div>
                                <h2 class="text-base font-semibold text-ink-900">Keamanan Akun</h2>
                                <p class="text-xs text-ink-500">Ganti password secara berkala untuk menjaga keamanan akun.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('account.password') }}" class="mt-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="current_password" class="block text-sm font-semibold text-ink-700">Password saat ini</label>
                                <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                                       class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                @error('current_password')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-ink-700">Password baru</label>
                                    <input id="password" name="password" type="password" autocomplete="new-password"
                                           class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                    @error('password')<p class="mt-1.5 text-xs font-semibold text-danger-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-ink-700">Konfirmasi password baru</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                                           class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                                </div>
                            </div>
                            <p class="text-xs text-ink-400">Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol.</p>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ink-800">
                                <svg class="h-4 w-4"><use href="#i-shield"/></svg>
                                Ubah Password
                            </button>
                        </form>

                        <div class="mt-6 rounded-xl border border-ink-100 bg-ink-50/60 p-4 text-xs text-ink-600">
                            <p class="flex items-center gap-1.5 font-semibold text-ink-800">
                                <svg class="h-4 w-4 text-emerald-600"><use href="#i-check-circle"/></svg>
                                Tips keamanan
                            </p>
                            <ul class="mt-2 space-y-1 pl-6 list-disc text-ink-500">
                                <li>Jangan bagikan password kepada siapa pun, termasuk pihak yang mengaku admin.</li>
                                <li>Gunakan password yang berbeda untuk setiap akun.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Tab: Pesanan --}}
                <div data-account-panel="pesanan" class="account-panel mt-4 hidden">
                    <div class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-ink-900">Pesanan Terakhir</h2>
                            <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Lihat semua</a>
                        </div>

                        <div class="space-y-2.5">
                            @forelse ($orders as $order)
                                <a href="{{ route('orders.show', $order) }}"
                                   class="flex items-center gap-4 rounded-xl border border-ink-100 px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                                    <div class="min-w-0 flex-1">
                                        <strong class="block truncate text-sm font-bold text-ink-900">{{ $order->order_number }}</strong>
                                        <span class="text-xs text-ink-500">{{ $order->ordered_at->translatedFormat('d M Y') }} &bull; {{ $order->items_count }} barang</span>
                                    </div>
                                    <strong class="shrink-0 text-sm font-semibold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
                                    <x-status-badge :status="$order->status" />
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-ink-200 py-10 text-center">
                                    <p class="font-semibold text-ink-700">Belum ada pesanan</p>
                                    <p class="mt-1 text-sm text-ink-500">Pesanan yang selesai di-checkout akan tampil di sini.</p>
                                    <a href="{{ route('home') }}" class="mt-4 inline-block rounded-full bg-ink-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-700">Buka katalog</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            {{-- Sidebar Aksi --}}
            <aside class="space-y-4">
                <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                    <h2 class="text-base font-semibold text-ink-900">Aksi Cepat</h2>
                    <div class="mt-4 space-y-2.5">
                        <a href="{{ route('cart.index') }}"
                           class="flex items-center justify-between rounded-xl border border-ink-200 px-4 py-3 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4"><use href="#i-cart"/></svg>
                                Keranjang belanja
                            </span>
                            @if ($cartCount > 0)
                                <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-ink-900 px-1.5 text-xs font-medium text-white">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('wishlist.index') }}"
                           class="flex items-center justify-between rounded-xl border border-ink-200 px-4 py-3 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4"><use href="#i-heart"/></svg>
                                Wishlist saya
                            </span>
                            @if ($wishlistCount > 0)
                                <span class="flex h-6 min-w-6 items-center justify-center rounded-full bg-danger-500 px-1.5 text-xs font-bold text-white">{{ $wishlistCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('orders.index') }}"
                           class="flex items-center gap-2 rounded-xl border border-ink-200 px-4 py-3 text-sm font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700">
                            <svg class="h-4 w-4"><use href="#i-receipt"/></svg>
                            Semua pesanan
                        </a>
                        @if ($user->is_admin)
                            <a href="{{ route('admin.dashboard') }}"
                               class="flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-bold text-brand-700 transition hover:bg-brand-100">
                                <svg class="h-4 w-4"><use href="#i-shield"/></svg>
                                Buka panel admin
                            </a>
                        @endif
                    </div>
                </section>

                <section class="rounded-2xl border border-ink-100 bg-white p-5 shadow-card">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <svg class="h-5 w-5"><use href="#i-vinzybot"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-ink-900">Butuh bantuan?</h2>
                            <p class="text-xs text-ink-500">Asisten toko siap membantu lewat chat.</p>
                        </div>
                    </div>
                    <button type="button" onclick="document.querySelector('[data-chatbot-toggle]')?.click()"
                            class="mt-3.5 w-full rounded-full bg-ink-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-ink-700">
                        Chat Asisten Toko
                    </button>
                </section>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-xl border border-ink-200 bg-white px-4 py-3 text-sm font-semibold text-danger-600 transition hover:border-danger-300 hover:bg-danger-500/5">
                        Keluar dari akun
                    </button>
                </form>
            </aside>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('[data-account-tab]');
        const panels = document.querySelectorAll('[data-account-panel]');
        if (!tabs.length) return;

        const activeClasses = ['bg-brand-600', 'text-white', 'shadow-sm'];
        const inactiveClasses = ['text-ink-600', 'hover:bg-ink-50'];

        const activate = (target) => {
            tabs.forEach((tab) => {
                const isActive = tab.dataset.accountTab === target;
                activeClasses.forEach((c) => tab.classList.toggle(c, isActive));
                inactiveClasses.forEach((c) => tab.classList.toggle(c, !isActive));
            });
            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.accountPanel !== target);
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.dataset.accountTab));
        });
    });
</script>
@endsection
