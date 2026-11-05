<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

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
        $hasilWa = $this->whatsAppService->sendOrderNotification($transaksi);

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
        $user = Auth::user();

        abort_if(! $user, 403, 'Akses ditolak.');
        abort_if(! in_array($user->role, ['admin', 'sales'], true), 403, 'Akses ditolak.');

        if ($user->role === 'sales' && (int) $transaksi->id_user !== (int) $user->id_user) {
            abort(403, 'Akses ditolak.');
        }

        $rawStatus = (string) ($transaksi->getRawOriginal('status_penjualan') ?? $transaksi->statusValue());

        if (! in_array($rawStatus, [
            OrderStatus::DIPROSES->value,
            OrderStatus::DIKIRIM->value,
            OrderStatus::SELESAI->value,
        ], true)) {
            return redirect()
                ->back()
                ->with('error', 'Nota hanya dapat dikirim untuk pesanan yang sudah diproses.');
        }

        $berhasil = $this->whatsAppService->sendOrderNotification($transaksi);

        if (! $berhasil) {
            return redirect()
                ->back()
                ->with('warning', 'Gagal mengirim pesan WhatsApp ke customer.');
        }

        return redirect()
            ->back()
            ->with('success', 'Pesan WhatsApp berhasil dikirim ke customer.');
    }
}