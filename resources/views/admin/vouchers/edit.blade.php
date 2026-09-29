@extends('layouts.app')

@section('title', 'Ubah Voucher — '.config('app.name'))

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading"><span class="section-code">ADMIN / VOUCHER</span><h1>Ubah voucher</h1></header>
    <form class="editor-form" method="POST" action="{{ route('admin.vouchers.update', $voucher) }}">
        @csrf
        @method('PUT')
        @include('admin.vouchers._form', ['submitLabel' => 'Perbarui voucher'])
    </form>
</div>
@endsection
