<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->cari;

        $order = Penjualan::with('pelanggan')
            ->where('id_user', auth()->user()->id_user)
            ->when($cari, function ($query, $cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('nomor_penjualan', 'like', "%{$cari}%")
                        ->orWhereHas('pelanggan', function ($pelanggan) use ($cari) {
                            $pelanggan->where('nama_pelanggan', 'like', "%{$cari}%")
                                ->orWhere('nama_toko', 'like', "%{$cari}%");
                        });
                });
            })
            ->orderBy('id_penjualan', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('order.index', compact('order', 'cari'));
    }

    public function create()
    {
        $pelanggan = Pelanggan::where('status_pelanggan', 'aktif')
            ->orderBy('nama_pelanggan')
            ->get();

        $produk = Produk::where('status_produk', 'aktif')
            ->where('stok_produk', '>', 0)
            ->orderBy('nama_produk')
            ->get();

        $produkOptions = $produk->map(function ($item) {
            return [
                'id' => $item->id_produk,
                'nama' => $item->nama_produk,
                'stok' => $item->stok_produk,
                'satuan' => $item->satuan_produk,
                'harga' => $item->harga_produk,
            ];
        })->values();

        return view('order.create', compact(
            'pelanggan',
            'produk',
            'produkOptions'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'foto_toko' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'produk' => 'required|array|min:1',
            'produk.*.id_produk' => 'required|exists:produk,id_produk',
            'produk.*.jumlah' => 'required|integer|min:1',
        ]);

        $fotoTokoPath = $request->file('foto_toko')->store('foto_toko', 'public');

        try {
        DB::transaction(function () use ($data, $fotoTokoPath) {

            $total = 0;
            $detailData = [];

            foreach ($data['produk'] as $item) {

                $produk = Produk::where('id_produk', $item['id_produk'])
                    ->where('status_produk', 'aktif')
                    ->first();

                if (!$produk) {
                    throw ValidationException::withMessages([
                        'produk' => 'Terdapat produk yang sudah tidak aktif.'
                    ]);
                }

                if ($item['jumlah'] > $produk->stok_produk) {
                    throw ValidationException::withMessages([
                        'produk' => "Jumlah {$produk->nama_produk} melebihi stok yang tersedia."
                    ]);
                }

                $harga = $produk->harga_produk;
                $subtotal = $harga * $item['jumlah'];

                $total += $subtotal;

                $detailData[] = [
                    'id_produk' => $produk->id_produk,
                    'jumlah_produk' => $item['jumlah'],
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                ];
            }

            $penjualan = Penjualan::create([
                'id_pelanggan' => $data['id_pelanggan'],
                'id_user' => auth()->user()->id_user,
                'nomor_penjualan' => 'PJ-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'tanggal_penjualan' => now()->toDateString(),
                'total_penjualan' => $total,
                'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
                'foto_toko' => $fotoTokoPath,
            ]);

            foreach ($detailData as $detail) {

                DetailPenjualan::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($fotoTokoPath);
            throw $e;
        }

        return redirect()
            ->route('order.index')
            ->with('success', 'Order penjualan berhasil dibuat.');
    }

    public function show(Penjualan $order)
{
    abort_if(
        $order->id_user !== auth()->user()->id_user,
        403
    );

    $order->load([
        'pelanggan',
        'detailPenjualan.produk'
    ]);

    return view('order.show', compact('order'));
}

public function edit(Penjualan $order)
{
    abort_if(
        $order->id_user !== auth()->user()->id_user,
        403
    );

    abort_if(
        !$order->isMenungguValidasi(),
        403,
        'Order yang sudah diproses tidak dapat diubah.'
    );

    $pelanggan = Pelanggan::where('status_pelanggan', 'aktif')
        ->orderBy('nama_pelanggan')
        ->get();

    $produk = Produk::where('status_produk', 'aktif')
        ->where('stok_produk', '>', 0)
        ->orderBy('nama_produk')
        ->get();

    $produkOptions = $produk->map(function ($item) {
        return [
            'id' => $item->id_produk,
            'nama' => $item->nama_produk,
            'stok' => $item->stok_produk,
            'satuan' => $item->satuan_produk,
            'harga' => $item->harga_produk,
        ];
    })->values();

    $order->load('detailPenjualan');

    $detailOrder = $order->detailPenjualan->map(function ($detail) {
        return [
            'id_produk' => $detail->id_produk,
            'jumlah' => $detail->jumlah_produk,
        ];
    })->values();

    return view('order.edit', compact(
        'order',
        'pelanggan',
        'produk',
        'produkOptions',
        'detailOrder'
    ));
}

public function update(Request $request, Penjualan $order)
{
    abort_if(
        $order->id_user !== auth()->user()->id_user,
        403
    );

    abort_if(
        !$order->isMenungguValidasi(),
        403,
        'Order yang sudah diproses tidak dapat diubah.'
    );

    $data = $request->validate([
        'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
        'foto_toko' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',

        'produk' => 'required|array|min:1',

        'produk.*.id_produk' => [
            'required',
            'distinct',
            'exists:produk,id_produk',
        ],

        'produk.*.jumlah' => 'required|integer|min:1',
    ]);

    $newFotoTokoPath = null;
    $oldFotoTokoPath = $order->foto_toko;

    if ($request->hasFile('foto_toko')) {
        $newFotoTokoPath = $request->file('foto_toko')->store('foto_toko', 'public');
    }

    try {
    DB::transaction(function () use ($data, $order, $newFotoTokoPath) {

        $total = 0;
        $detailBaru = [];

        foreach ($data['produk'] as $item) {

            $produk = Produk::where(
                'id_produk',
                $item['id_produk']
            )
                ->where('status_produk', 'aktif')
                ->first();

            if (!$produk) {
                throw ValidationException::withMessages([
                    'produk' => 'Terdapat produk yang tidak aktif.'
                ]);
            }

            if ($item['jumlah'] > $produk->stok_produk) {
                throw ValidationException::withMessages([
                    'produk' => "Jumlah {$produk->nama_produk} melebihi stok."
                ]);
            }

            $subtotal =
                $produk->harga_produk * $item['jumlah'];

            $total += $subtotal;

            $detailBaru[] = [
                'id_produk' => $produk->id_produk,
                'jumlah_produk' => $item['jumlah'],
                'harga_satuan' => $produk->harga_produk,
                'subtotal' => $subtotal,
            ];
        }

        $updateData = [
            'id_pelanggan' => $data['id_pelanggan'],
            'total_penjualan' => $total,
        ];

        if ($newFotoTokoPath !== null) {
            $updateData['foto_toko'] = $newFotoTokoPath;
        }

        $order->update($updateData);

        DetailPenjualan::where(
            'id_penjualan',
            $order->id_penjualan
        )->delete();

        foreach ($detailBaru as $detail) {

            DetailPenjualan::create([
                'id_penjualan' => $order->id_penjualan,
                'id_produk' => $detail['id_produk'],
                'jumlah_produk' => $detail['jumlah_produk'],
                'harga_satuan' => $detail['harga_satuan'],
                'subtotal' => $detail['subtotal'],
            ]);
        }
    });

    if ($newFotoTokoPath !== null && $oldFotoTokoPath) {
        Storage::disk('public')->delete($oldFotoTokoPath);
    }
    } catch (\Throwable $e) {
        if ($newFotoTokoPath !== null) {
            Storage::disk('public')->delete($newFotoTokoPath);
        }
        throw $e;
    }

    return redirect()
        ->route('order.index')
        ->with('success', 'Order berhasil diubah.');
}

public function uploadBukti(Request $request, Penjualan $order)
{
    abort_if(
        $order->id_user !== auth()->user()->id_user,
        403
    );

    if (!$order->isDikirim()) {
        throw ValidationException::withMessages([
            'order' => 'Bukti penyerahan hanya dapat diunggah saat order berstatus dikirim.'
        ]);
    }

    $request->validate([
        'foto_bukti_penyerahan' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);

    $path = $request->file('foto_bukti_penyerahan')->store('bukti_penyerahan', 'public');

    $order->update([
        'foto_bukti_penyerahan' => $path,
    ]);

    return redirect()
        ->route('order.show', $order)
        ->with('success', 'Foto bukti penyerahan berhasil diunggah.');
}

public function selesai(Penjualan $order)
{
    abort_if(
        $order->id_user !== auth()->user()->id_user,
        403
    );

    if (!$order->canTransitionTo(OrderStatus::SELESAI)) {
        throw ValidationException::withMessages([
            'order' => 'Order tidak dapat diselesaikan pada status saat ini.'
        ]);
    }

    if (empty($order->foto_bukti_penyerahan)) {
        throw ValidationException::withMessages([
            'foto_bukti_penyerahan' => 'Order tidak dapat diselesaikan karena bukti penyerahan belum diunggah.'
        ]);
    }

    $order->update([
        'status_penjualan' => OrderStatus::SELESAI->value,
        'tanggal_selesai' => now()->toDateString(),
    ]);

    return redirect()
        ->route('order.show', $order)
        ->with('success', 'Order berhasil diselesaikan.');
}
}
