<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $primaryKey = 'id_penjualan';

    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan',
        'no_wa',
        'id_user',
        'nomor_penjualan',
        'tanggal_penjualan',
        'total_penjualan',
        'status_penjualan',
        'tanggal_validasi',
        'foto_toko',
        'foto_bukti_penyerahan',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'tanggal_validasi' => 'date',
        'tanggal_selesai' => 'date',
        'total_penjualan' => 'decimal:2',
    ];

    public function getStatusPenjualanAttribute($value): OrderStatus|string|null
    {
        if ($value instanceof OrderStatus) {
            return $value;
        }

        return OrderStatus::fromLegacy($value) ?? $value;
    }

    public function setStatusPenjualanAttribute($value): void
    {
        $this->attributes['status_penjualan'] = $value instanceof OrderStatus ? $value->value : $value;
    }

    public function statusValue(): string
    {
        $status = $this->status_penjualan;

        return $status instanceof OrderStatus ? $status->value : (string) $status;
    }

    public function isMenungguValidasi(): bool
    {
        return $this->status_penjualan === OrderStatus::MENUNGGU_VALIDASI || $this->status_penjualan === 'menunggu_validasi';
    }

    public function isDiproses(): bool
    {
        return $this->status_penjualan === OrderStatus::DIPROSES || $this->status_penjualan === 'diproses' || $this->status_penjualan === 'tervalidasi';
    }

    public function isDikirim(): bool
    {
        return $this->status_penjualan === OrderStatus::DIKIRIM || $this->status_penjualan === 'dikirim';
    }

    public function isSelesai(): bool
    {
        return $this->status_penjualan === OrderStatus::SELESAI || $this->status_penjualan === 'selesai';
    }

    public function canTransitionTo(OrderStatus $target): bool
    {
        $current = $this->status_penjualan;
        if (!$current instanceof OrderStatus) {
            $current = OrderStatus::fromLegacy($current);
        }

        if (!$current) {
            return false;
        }

        return $current->canTransitionTo($target);
    }

    public function pelanggan()
    {
        return $this->belongsTo(
            Pelanggan::class,
            'id_pelanggan',
            'id_pelanggan'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }

    public function detailPenjualan()
    {
        return $this->hasMany(
            DetailPenjualan::class,
            'id_penjualan',
            'id_penjualan'
        );
    }
}