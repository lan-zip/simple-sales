<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\Penjualan;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {

            $totalProduk = Produk::where('status_produk', 'aktif')->count();

            $totalSales = User::where('role', 'sales')
                ->where('status_user', 'aktif')
                ->count();

            $totalPelanggan = Pelanggan::where(
                'status_pelanggan',
                'aktif'
            )->count();

            $transaksiMenunggu = Penjualan::where(
                'status_penjualan',
                'menunggu_validasi'
            )->count();

            $totalPendapatan = Penjualan::where('status_penjualan', 'selesai')
                ->sum('total_penjualan');

            $orderTerbaru = Penjualan::with([
                'pelanggan:id_pelanggan,nama_pelanggan,nama_toko',
                'user:id_user,nama_user',
            ])
                ->orderByDesc('id_penjualan')
                ->limit(5)
                ->get();

            return view('dashboard.admin', compact(
                'totalProduk',
                'totalSales',
                'totalPelanggan',
                'transaksiMenunggu',
                'totalPendapatan',
                'orderTerbaru'
            ));
        }

        if ($user->role === 'sales') {

            $totalOrder = Penjualan::where(
                'id_user',
                $user->id_user
            )->count();

            $orderMenunggu = Penjualan::where(
                'id_user',
                $user->id_user
            )
                ->where('status_penjualan', 'menunggu_validasi')
                ->count();

            $orderDikirim = Penjualan::where(
                'id_user',
                $user->id_user
            )
                ->where('status_penjualan', 'dikirim')
                ->count();

            $orderSelesai = Penjualan::where(
                'id_user',
                $user->id_user
            )
                ->where('status_penjualan', 'selesai')
                ->count();

            return view('dashboard.sales', compact(
                'totalOrder',
                'orderMenunggu',
                'orderDikirim',
                'orderSelesai'
            ));
        }

        if ($user->role === 'direktur') {

            $totalPenjualan = Penjualan::where(
                'status_penjualan',
                'selesai'
            )->sum('total_penjualan');

            $totalTransaksi = Penjualan::where(
                'status_penjualan',
                'selesai'
            )->count();

            $totalProduk = Produk::where(
                'status_produk',
                'aktif'
            )->count();

            $stokRendah = Produk::where(
                'status_produk',
                'aktif'
            )
                ->where('stok_produk', '<=', 10)
                ->count();

            return view('dashboard.direktur', compact(
                'totalPenjualan',
                'totalTransaksi',
                'totalProduk',
                'stokRendah'
            ));
        }

        abort(403);
    }
}
