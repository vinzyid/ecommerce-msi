@extends('layouts.app')

@section('title', 'Tambah Voucher — '.config('app.name'))

@section('content')
<div class="admin-shell">
    @include('admin._nav')
    <header class="page-heading admin-heading"><span class="section-code">ADMIN / VOUCHER</span><h1>Tambah voucher</h1></header>
    <form class="editor-form" method="POST" action="{{ route('admin.vouchers.store') }}">
        @csrf
        @include('admin.vouchers._form', ['voucher' => null, 'submitLabel' => 'Simpan voucher'])
    </form>
</div>
@endsection
