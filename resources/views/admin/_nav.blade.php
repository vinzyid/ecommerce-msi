@php
    $items = [
        ['route' => 'admin.dashboard', 'label' => 'Ringkasan', 'icon' => 'i-chart'],
        ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'label' => 'Produk', 'icon' => 'i-box'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Kategori', 'icon' => 'i-layers'],
        ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'label' => 'Pesanan', 'icon' => 'i-receipt'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'Pengguna', 'icon' => 'i-users'],
        ['route' => 'admin.vouchers.index', 'match' => 'admin.vouchers.*', 'label' => 'Voucher', 'icon' => 'i-ticket'],
        ['route' => 'admin.chatbot.index', 'match' => 'admin.chatbot.*', 'label' => 'Chatbot AI', 'icon' => 'i-bolt'],
    ];
@endphp

<nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Navigasi admin">
    @foreach ($items as $item)
        @php $active = request()->routeIs($item['match'] ?? $item['route']); @endphp
        <a href="{{ route($item['route']) }}"
           @class([
               'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
               'bg-brand-600 text-white shadow-sm' => $active,
               'text-ink-600 hover:bg-ink-50 hover:text-ink-900' => ! $active,
           ])>
            <svg class="h-4.5 w-4.5 {{ $active ? 'text-white' : 'text-ink-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <use href="#{{ $item['icon'] }}"/>
            </svg>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
