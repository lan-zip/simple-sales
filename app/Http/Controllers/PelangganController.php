<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $rawCari = $request->input('cari');
        $cari = is_string($rawCari) ? trim($rawCari) : null;

        $rawStatus = $request->input('status');
        $status = is_string($rawStatus) && in_array($rawStatus, ['aktif', 'nonaktif'], true)
            ? $rawStatus
            : null;

        $pelanggan = Pelanggan::when($cari, function ($query, $cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('kode_pelanggan', 'like', "%{$cari}%")
                    ->orWhere('nama_pelanggan', 'like', "%{$cari}%")
                    ->orWhere('nama_toko', 'like', "%{$cari}%")
                    ->orWhere('no_wa', 'like', "%{$cari}%");
            });
        })
            ->when($status, function ($query, $status) {
                $query->where('status_pelanggan', $status);
            })
            ->orderBy('id_pelanggan', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggan.index', compact('pelanggan', 'cari', 'status'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_pelanggan' => 'required|max:30|unique:pelanggan,kode_pelanggan',
            'nama_pelanggan' => 'required|max:100',
            'nama_toko' => 'required|max:100',
            'alamat_pelanggan' => 'required',
            'no_wa' => 'required|max:20',
        ], [
            'kode_pelanggan.required' => 'Kode pelanggan wajib diisi.',
            'kode_pelanggan.max' => 'Kode pelanggan maksimal 30 karakter.',
            'kode_pelanggan.unique' => 'Kode pelanggan sudah digunakan.',
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter.',
            'nama_toko.required' => 'Nama toko wajib diisi.',
            'nama_toko.max' => 'Nama toko maksimal 100 karakter.',
            'alamat_pelanggan.required' => 'Alamat pelanggan wajib diisi.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.max' => 'Nomor WhatsApp maksimal 20 karakter.',
        ]);

        $data['status_pelanggan'] = 'aktif';

        Pelanggan::create($data);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        abort_if(auth()->user()?->role !== 'admin', 403, 'Akses ditolak.');

        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        abort_if(auth()->user()?->role !== 'admin', 403, 'Akses ditolak.');

        $data = $request->validate([
            'kode_pelanggan' => [
                'required',
                'max:30',
                Rule::unique('pelanggan', 'kode_pelanggan')
                    ->ignore($pelanggan->id_pelanggan, 'id_pelanggan'),
            ],
            'nama_pelanggan' => 'required|max:100',
            'nama_toko' => 'required|max:100',
            'alamat_pelanggan' => 'required',
            'no_wa' => 'required|max:20',
        ], [
            'kode_pelanggan.required' => 'Kode pelanggan wajib diisi.',
            'kode_pelanggan.max' => 'Kode pelanggan maksimal 30 karakter.',
            'kode_pelanggan.unique' => 'Kode pelanggan sudah digunakan.',
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter.',
            'nama_toko.required' => 'Nama toko wajib diisi.',
            'nama_toko.max' => 'Nama toko maksimal 100 karakter.',
            'alamat_pelanggan.required' => 'Alamat pelanggan wajib diisi.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.max' => 'Nomor WhatsApp maksimal 20 karakter.',
        ]);

        $pelanggan->update($data);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diubah.');
    }

    public function nonaktifkan(Pelanggan $pelanggan)
    {
        abort_if(auth()->user()?->role !== 'admin', 403, 'Akses ditolak.');

        $pelanggan->update([
            'status_pelanggan' => 'nonaktif',
        ]);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dinonaktifkan.');
    }
}
