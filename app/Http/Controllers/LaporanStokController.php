<?php

namespace App\Http\Controllers;

use App\Models\Produk;

class LaporanStokController extends Controller
{
    public function index()
    {
        $produk = Produk::orderBy('nama_produk')
            ->paginate(15)
            ->withQueryString();

        return view('laporan.stok', compact('produk'));
    }
}
