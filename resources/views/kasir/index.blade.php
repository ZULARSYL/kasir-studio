@extends('template.index')

@section('title', 'Menu Kasir POS | Pressent Studio & Cafe')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card p-4">
                    <h5 class="card-title">Menu Kasir (POS)</h5>
                    <p class="card-text">Selamat datang di menu kasir (POS) Pressent Studio & Cafe. Silakan gunakan fitur ini untuk mengelola transaksi penjualan dengan mudah dan efisien.</p>
                    <a href="{{ route('kasir.kasir') }}" class="btn btn-primary">Buka Menu Kasir</a>
                </div>
            </div>
        </div>
    </div>