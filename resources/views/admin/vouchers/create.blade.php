@extends('layouts.admin')

@section('title', 'Tambah Voucher — '.config('app.name'))
@section('heading', 'Tambah Voucher')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.vouchers.store') }}"
          class="rounded-2xl border border-ink-100 bg-white p-6 shadow-card sm:p-7">
        @csrf
        @include('admin.vouchers._form', ['voucher' => null, 'submitLabel' => 'Simpan voucher'])
    </form>
</div>
@endsection
