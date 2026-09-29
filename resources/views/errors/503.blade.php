@extends('errors.layout')

@section('title', 'Sedang diperbaiki — '.config('app.name'))
@section('code', '503')
@section('message', 'Toko sedang dalam pemeliharaan.')
@section('description', config('app.name').' sementara tidak dapat diakses karena pembaruan sistem. Silakan kembali lagi dalam beberapa menit.')