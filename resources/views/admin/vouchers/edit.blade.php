@extends('layouts.admin')

@section('title', 'Ubah Voucher — '.config('app.name'))
@section('heading', 'Ubah Voucher')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @method('PUT')
        @include('admin.vouchers._form', ['submitLabel' => 'Perbarui voucher'])
    </form>
</div>
@endsection
