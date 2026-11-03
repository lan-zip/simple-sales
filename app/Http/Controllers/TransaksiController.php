<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;


class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->cari;
        $status = $request->status;

        $transaksi = Penjualan::with([
                'pelanggan',
                'user'
            ])
            ->when($cari, function ($query, $cari) {

                $query->where(function ($q) use ($cari) {

                    $q->where(
                        'nomor_penjualan',
                        'like',
                        "%{$cari}%"
                    )
                    ->orWhereHas(
                        'pelanggan',
                        function ($pelanggan) use ($cari) {

                            $pelanggan
                                ->where(
                                    'nama_pelanggan',
                                    'like',
                                    "%{$cari}%"
                                )
                                ->orWhere(
                                    'nama_toko',
                                    'like',
                                    "%{$cari}%"
                                );
                        }
                    )
                    ->orWhereHas(
                        'user',
                        function ($user) use ($cari) {

                            $user->where(
                                'nama_user',
                                'like',
                                "%{$cari}%"
                            );
                        }
                    );
                });
            })

            ->when($status, function ($query, $status) {

                $query->where(
                    'status_penjualan',
                    $status
                );
            })

            ->orderBy('id_penjualan', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'transaksi.index',
            compact(
                'transaksi',
                'cari',
                'status'
            )
        );
    }

    public function show(Penjualan $transaksi)
    {
        $transaksi->load([
            'pelanggan',
            'user',
            'detailPenjualan.produk'
        ]);

        return view('transaksi.show', compact('transaksi'));
    }

    public function validasi(Penjualan $transaksi)
    {
        DB::transaction(function () use ($transaksi) {

            $transaksi = Penjualan::where(
                'id_penjualan',
                $transaksi->id_penjualan
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$transaksi->canTransitionTo(OrderStatus::DIPROSES)) {
                throw ValidationException::withMessages([
                    'transaksi' => 'Transaksi ini sudah diproses sebelumnya.'
                ]);
            }

            $transaksi->load('detailPenjualan');

            foreach ($transaksi->detailPenjualan as $detail) {

                $produk = Produk::where(
                    'id_produk',
                    $detail->id_produk
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($produk->stok_produk < $detail->jumlah_produk) {

                    throw ValidationException::withMessages([
                        'stok' =>
                            "Stok {$produk->nama_produk} tidak mencukupi."
                    ]);
                }

                $produk->decrement(
                    'stok_produk',
                    $detail->jumlah_produk
                );
            }

            $transaksi->update([
                'status_penjualan' => OrderStatus::DIPROSES->value,
                'tanggal_validasi' => now()->toDateString(),
            ]);
        });

        // Ambil ulang data transaksi setelah berhasil divalidasi
        $transaksi->load([
            'pelanggan',
            'user',
            'detailPenjualan.produk'
        ]);

        // Kirim nota otomatis ke WhatsApp
        $hasilWa = $this->kirimNotaOtomatis($transaksi);

        if ($hasilWa) {
            return redirect()
                ->route('transaksi.show', $transaksi)
                ->with(
                    'success',
                    'Transaksi berhasil divalidasi dan nota berhasil dikirim ke WhatsApp customer.'
                );
        }

        return redirect()
            ->route('transaksi.show', $transaksi)
            ->with(
                'warning',
                'Transaksi berhasil divalidasi, tetapi nota gagal dikirim ke WhatsApp customer.'
            );
    }

    private function kirimNotaOtomatis(Penjualan $transaksi)
    {
        $transaksi->load('pelanggan');

        if (!$transaksi->pelanggan || !$transaksi->pelanggan->no_wa) {
            return false;
        }

        $nomor = preg_replace(
            '/[^0-9]/',
            '',
            $transaksi->pelanggan->no_wa
        );

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        $namaPelanggan = $transaksi->pelanggan
            ? $transaksi->pelanggan->nama_pelanggan
            : 'Pelanggan';

        $pesan =
            "Halo, {$namaPelanggan}.\n\n" .
            "Pesanan Anda dari PT Halus Ciptanadi telah divalidasi.\n\n" .
            "Nomor transaksi: {$transaksi->nomor_penjualan}\n" .
            "Total: Rp " .
            number_format(
                $transaksi->total_penjualan,
                0,
                ',',
                '.'
            ) .
            "\n\n" .
            "Terima kasih telah melakukan pemesanan di PT Halus Ciptanadi.";

        try {

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => config('services.fonnte.token'),
                ])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => $nomor,
                    'message' => $pesan,
                    'countryCode' => '62',
                ]);

            return $response->successful()
                && $response->json('status') === true;

        } catch (\Exception $e) {

            \Log::error(
                'Gagal mengirim WhatsApp Fonnte',
                [
                    'transaksi' => $transaksi->nomor_penjualan,
                    'nomor' => $nomor,
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }
    }
    
    public function kirim(Penjualan $transaksi)
    {
        if (!$transaksi->canTransitionTo(OrderStatus::DIKIRIM)) {
            throw ValidationException::withMessages([
                'transaksi' => 'Transaksi tidak dapat diubah ke status dikirim pada status saat ini.'
            ]);
        }

        $transaksi->update([
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        return redirect()
            ->route('transaksi.show', $transaksi)
            ->with('success', 'Status transaksi berhasil diperbarui menjadi dikirim.');
    }

    public function kirimNota(Penjualan $transaksi)
    {
        if ($transaksi->status_penjualan !== 'tervalidasi') {
            return redirect()
                ->back()
                ->with('error', 'Nota hanya bisa dikirim setelah transaksi divalidasi.');
        }

        if (!$transaksi->no_wa) {
            return redirect()
                ->back()
                ->with('error', 'Nomor WhatsApp customer belum diisi.');
        }

        $nomor = preg_replace('/[^0-9]/', '', $transaksi->no_wa);

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        $pesan =
            "Halo, {$transaksi->pelanggan->nama_pelanggan}.\n\n" .
            "Pesanan Anda dari PT Halus Ciptanadi telah divalidasi.\n\n" .
            "Nomor transaksi: {$transaksi->nomor_penjualan}\n" .
            "Total: Rp " . number_format($transaksi->total_penjualan, 0, ',', '.') . "\n\n" .
            "Terima kasih telah melakukan pemesanan.";

        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
            'countryCode' => '62',
        ]);

        if (!$response->successful() || !$response->json('status')) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengirim WhatsApp melalui Fonnte.');
        }

        return redirect()
            ->back()
            ->with('success', 'Pesan WhatsApp berhasil dikirim ke customer.');
    }
}