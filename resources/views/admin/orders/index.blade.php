@extends('layouts.admin')

@section('title', 'Pesanan Admin — '.config('app.name'))
@section('heading', 'Pesanan')

@section('content')
<div class="flex flex-wrap gap-1.5">
    <a href="{{ route('admin.orders.index') }}"
       @class([
           'rounded-xl px-3.5 py-2 text-xs font-bold transition',
           'bg-brand-600 text-white shadow-sm' => ! $status,
           'border border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' => (bool) $status,
       ])>Semua</a>
    @foreach (\App\Models\Order::STATUSES as $value)
        <a href="{{ route('admin.orders.index', ['status' => $value]) }}"
           @class([
               'rounded-xl px-3.5 py-2 text-xs font-bold transition',
               'bg-brand-600 text-white shadow-sm' => $status === $value,
               'border border-ink-200 bg-white text-ink-600 hover:border-brand-300 hover:text-brand-700' => $status !== $value,
           ])>{{ ucfirst($value) }}</a>
    @endforeach
</div>

<section class="mt-5 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-ink-100 text-left text-xs font-bold uppercase tracking-wide text-ink-400">
                    <th class="px-5 py-3">Nomor</th>
                    <th class="px-5 py-3">Pelanggan</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3 text-center">Item</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse ($orders as $order)
                    <tr class="transition hover:bg-ink-50/60">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-brand-600 hover:text-brand-700">{{ $order->order_number }}</a>
                        </td>
                        <td class="px-5 py-3.5 font-medium text-ink-700">{{ $order->user->username }}</td>
                        <td class="px-5 py-3.5 text-ink-500">{{ $order->ordered_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3.5 text-center font-bold text-ink-700">{{ $order->items_count }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-ink-900">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5"><x-status-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-ink-400">Belum ada pesanan pada status ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($orders->hasPages())
    <div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
