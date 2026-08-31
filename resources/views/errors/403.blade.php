@extends('errors.layout')

@section('title', 'Akses ditolak — Etalase')
@section('code', '403')
@section('message', 'Anda tidak memiliki akses ke halaman ini.')
@section('description', 'Halaman ini khusus untuk peran tertentu, misalnya menu admin yang hanya dapat dibuka oleh admin toko. Masuk menggunakan akun yang sesuai untuk melanjutkan.')