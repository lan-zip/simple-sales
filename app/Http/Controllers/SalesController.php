<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $rawCari = $request->input('cari');
        $cari = is_string($rawCari) ? trim($rawCari) : null;

        $rawStatus = $request->input('status');
        $status = is_string($rawStatus) && in_array($rawStatus, ['aktif', 'nonaktif'], true)
            ? $rawStatus
            : null;

        $sales = User::where('role', 'sales')
            ->when($cari, function ($query, $cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('nama_user', 'like', "%{$cari}%")
                        ->orWhere('username', 'like', "%{$cari}%")
                        ->orWhere('no_telp', 'like', "%{$cari}%");
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status_user', $status);
            })
            ->orderBy('id_user', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales', 'cari', 'status'));
    }

    public function create()
    {
        return view('sales.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_user' => 'required|max:100',
            'username' => 'required|max:50|unique:user,username',
            'password' => 'required|min:6',
            'no_telp' => 'required|max:20',
            'alamat' => 'required',
        ]);

        User::create([
            'nama_user' => $data['nama_user'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'sales',
            'no_telp' => $data['no_telp'],
            'alamat' => $data['alamat'],
            'status_user' => 'aktif',
        ]);

        return redirect()
            ->route('sales.index')
            ->with('success', 'Data sales berhasil ditambahkan.');
    }

    public function edit(User $sales)
    {
        abort_if($sales->role !== 'sales', 404);

        return view('sales.edit', compact('sales'));
    }

    public function update(Request $request, User $sales)
    {
        abort_if($sales->role !== 'sales', 404);

        $data = $request->validate([
            'nama_user' => 'required|max:100',
            'username' => [
                'required',
                'max:50',
                Rule::unique('user', 'username')
                    ->ignore($sales->id_user, 'id_user'),
            ],
            'no_telp' => 'required|max:20',
            'alamat' => 'required',
            'password' => 'nullable|min:6',
        ]);

        $sales->nama_user = $data['nama_user'];
        $sales->username = $data['username'];
        $sales->no_telp = $data['no_telp'];
        $sales->alamat = $data['alamat'];

        if (!empty($data['password'])) {
            $sales->password = Hash::make($data['password']);
        }

        $sales->save();

        return redirect()
            ->route('sales.index')
            ->with('success', 'Data sales berhasil diubah.');
    }

    public function nonaktifkan(User $sales)
    {
        abort_if($sales->role !== 'sales', 404);

        $sales->update([
            'status_user' => 'nonaktif'
        ]);

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sales berhasil dinonaktifkan.');
    }

    public function aktifkan(User $sales)
    {
        abort_if($sales->role !== 'sales', 404);

        $sales->update([
            'status_user' => 'aktif'
        ]);

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sales berhasil diaktifkan kembali.');
    }
}