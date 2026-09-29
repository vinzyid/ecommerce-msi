@extends('layouts.app')

@section('title', 'Pengguna Admin — '.config('app.name'))

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading">
        <span class="section-code">ADMIN / PENGGUNA</span>
        <h1>Kelola pengguna</h1>
        <p>Daftar akun yang terdaftar beserta peran dan riwayat pesanannya.</p>
    </header>

    <dl class="stats-strip">
        <div><dt>Total akun</dt><dd>{{ $summary['total'] }}</dd></div>
        <div><dt>Admin</dt><dd>{{ $summary['admins'] }}</dd></div>
        <div><dt>Pelanggan</dt><dd>{{ $summary['customers'] }}</dd></div>
        <div><dt>Hasil filter</dt><dd>{{ $users->total() }}</dd></div>
    </dl>

    <form class="admin-search" method="GET">
        <input name="q" value="{{ $search }}" placeholder="Cari username atau email">
        <button type="submit">Cari</button>
    </form>

    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Pengguna</th><th>Email</th><th>Peran</th><th>Pesanan</th><th>Terdaftar</th><th></th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <span class="user-cell">
                                <span class="avatar" aria-hidden="true">{{ $user->initials() }}</span>
                                <strong>{{ $user->username }}</strong>
                            </span>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><b class="status {{ $user->is_admin ? 'status-shipped' : 'status-completed' }}">{{ $user->is_admin ? 'Admin' : 'Pelanggan' }}</b></td>
                        <td>{{ $user->orders_count }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('admin.users.show', $user) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Pengguna tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <nav class="pagination" aria-label="Navigasi halaman">
            @if ($users->onFirstPage())<span>Sebelumnya</span>@else<a href="{{ $users->previousPageUrl() }}">Sebelumnya</a>@endif
            <b>Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</b>
            @if ($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}">Berikutnya</a>@else<span>Berikutnya</span>@endif
        </nav>
    @endif
</div>
@endsection
