<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\Request;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'id_pelanggan' => ['nullable', 'integer'],
            'id_user' => ['nullable', 'integer'],
        ], [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'] ?? null;
        $tanggalSelesai = $validated['tanggal_selesai'] ?? null;
        $idPelanggan = $validated['id_pelanggan'] ?? null;
        $idUser = $validated['id_user'] ?? null;

        $query = Penjualan::with(['pelanggan', 'user'])
            ->where('status_penjualan', OrderStatus::SELESAI->value)
            ->when($tanggalMulai, fn ($q) => $q->whereDate('tanggal_penjualan', '>=', $tanggalMulai))
            ->when($tanggalSelesai, fn ($q) => $q->whereDate('tanggal_penjualan', '<=', $tanggalSelesai))
            ->when($idPelanggan, fn ($q) => $q->where('id_pelanggan', $idPelanggan))
            ->when($idUser, fn ($q) => $q->where('id_user', $idUser));

        $totalPendapatan = (float) ((clone $query)->sum('total_penjualan') ?? 0);
        $totalTransaksi = (clone $query)->count();

        $transaksi = $query->orderBy('id_penjualan', 'desc')
            ->paginate(15)
            ->withQueryString();

        $pelangganList = Pelanggan::whereHas('penjualan', fn ($q) => $q->where('status_penjualan', OrderStatus::SELESAI->value))
            ->orderBy('nama_pelanggan')
            ->get(['id_pelanggan', 'nama_pelanggan', 'nama_toko']);

        $salesList = User::whereHas('penjualan', fn ($q) => $q->where('status_penjualan', OrderStatus::SELESAI->value))
            ->where('role', 'sales')
            ->orderBy('nama_user')
            ->get(['id_user', 'nama_user']);

        $isFiltered = (bool) ($tanggalMulai || $tanggalSelesai || $idPelanggan || $idUser);

        return view('laporan.penjualan', compact(
            'transaksi',
            'totalPendapatan',
            'totalTransaksi',
            'pelangganList',
            'salesList',
            'tanggalMulai',
            'tanggalSelesai',
            'idPelanggan',
            'idUser',
            'isFiltered'
        ));
    }

    public function show(Penjualan $penjualan)
    {
        if (! $penjualan->isSelesai()) {
            abort(404);
        }

        $penjualan->load([
            'pelanggan',
            'user',
            'detailPenjualan.produk',
        ]);

        return view('laporan.show', compact('penjualan'));
    }
}
