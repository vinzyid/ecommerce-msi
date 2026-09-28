<nav class="admin-nav" aria-label="Navigasi admin">
    <span>ADMIN</span>
    <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>
        <svg class="icon" aria-hidden="true"><use href="#i-grid"/></svg><span>Ringkasan</span>
    </a>
    <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])>
        <svg class="icon" aria-hidden="true"><use href="#i-box"/></svg><span>Produk</span>
    </a>
    <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>
        <svg class="icon" aria-hidden="true"><use href="#i-layers"/></svg><span>Kategori</span>
    </a>
    <a href="{{ route('admin.orders.index') }}" @class(['active' => request()->routeIs('admin.orders.*')])>
        <svg class="icon" aria-hidden="true"><use href="#i-receipt"/></svg><span>Pesanan</span>
    </a>
    <a href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>
        <svg class="icon" aria-hidden="true"><use href="#i-users"/></svg><span>Pengguna</span>
    </a>
</nav>
