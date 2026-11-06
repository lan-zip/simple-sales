<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $rawCari = $request->input('cari');
        $cari = is_string($rawCari) ? trim($rawCari) : null;

        $rawStatus = $request->input('status');
        $status = is_string($rawStatus) && in_array($rawStatus, ['aktif', 'nonaktif'], true)
            ? $rawStatus
            : null;

        $produk = Produk::when($cari, function ($query, $cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('kode_produk', 'like', "%{$cari}%")
                    ->orWhere('nama_produk', 'like', "%{$cari}%")
                    ->orWhere('kategori_produk', 'like', "%{$cari}%");
            });
        })
            ->when($status, function ($query, $status) {
                $query->where('status_produk', $status);
            })
            ->orderBy('id_produk', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('produk.index', compact('produk', 'cari', 'status'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_produk' => 'required|max:30|unique:produk,kode_produk',
            'nama_produk' => 'required|max:100',
            'kategori_produk' => 'required|max:100',
            'harga_produk' => 'required|numeric|min:0',
            'stok_produk' => 'required|integer|min:0',
            'satuan_produk' => 'required|max:50',
        ], [
            'kode_produk.required' => 'Kode produk wajib diisi.',
            'kode_produk.max' => 'Kode produk maksimal 30 karakter.',
            'kode_produk.unique' => 'Kode produk sudah digunakan.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'nama_produk.max' => 'Nama produk maksimal 100 karakter.',
            'kategori_produk.required' => 'Kategori produk wajib diisi.',
            'kategori_produk.max' => 'Kategori produk maksimal 100 karakter.',
            'harga_produk.required' => 'Harga produk wajib diisi.',
            'harga_produk.numeric' => 'Harga produk harus berupa angka.',
            'harga_produk.min' => 'Harga produk tidak boleh kurang dari 0.',
            'stok_produk.required' => 'Stok produk wajib diisi.',
            'stok_produk.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stok_produk.min' => 'Stok produk tidak boleh kurang dari 0.',
            'satuan_produk.required' => 'Satuan produk wajib diisi.',
            'satuan_produk.max' => 'Satuan produk maksimal 50 karakter.',
        ]);

        $data['status_produk'] = 'aktif';

        Produk::create($data);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, Produk $produk)
    {
        $data = $request->validate([
            'kode_produk' => [
                'required',
                'max:30',
                Rule::unique('produk', 'kode_produk')
                    ->ignore($produk->id_produk, 'id_produk'),
            ],
            'nama_produk' => 'required|max:100',
            'kategori_produk' => 'required|max:100',
            'harga_produk' => 'required|numeric|min:0',
            'stok_produk' => 'required|integer|min:0',
            'satuan_produk' => 'required|max:50',
        ], [
            'kode_produk.required' => 'Kode produk wajib diisi.',
            'kode_produk.max' => 'Kode produk maksimal 30 karakter.',
            'kode_produk.unique' => 'Kode produk sudah digunakan.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'nama_produk.max' => 'Nama produk maksimal 100 karakter.',
            'kategori_produk.required' => 'Kategori produk wajib diisi.',
            'kategori_produk.max' => 'Kategori produk maksimal 100 karakter.',
            'harga_produk.required' => 'Harga produk wajib diisi.',
            'harga_produk.numeric' => 'Harga produk harus berupa angka.',
            'harga_produk.min' => 'Harga produk tidak boleh kurang dari 0.',
            'stok_produk.required' => 'Stok produk wajib diisi.',
            'stok_produk.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stok_produk.min' => 'Stok produk tidak boleh kurang dari 0.',
            'satuan_produk.required' => 'Satuan produk wajib diisi.',
            'satuan_produk.max' => 'Satuan produk maksimal 50 karakter.',
        ]);

        $produk->update($data);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diubah.');
    }

    public function nonaktifkan(Produk $produk)
    {
        $produk->update([
            'status_produk' => 'nonaktif',
        ]);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }
}
