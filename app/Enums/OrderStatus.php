<?php

namespace App\Enums;

enum OrderStatus: string
{
    case MENUNGGU_VALIDASI = 'menunggu_validasi';
    case DIPROSES = 'diproses';
    case DIKIRIM = 'dikirim';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => 'Menunggu Validasi',
            self::DIPROSES => 'Diproses',
            self::DIKIRIM => 'Dikirim',
            self::SELESAI => 'Selesai',
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => 'warning',
            self::DIPROSES => 'info',
            self::DIKIRIM => 'info',
            self::SELESAI => 'success',
        };
    }

    public function badgeIcon(): string
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => 'clock',
            self::DIPROSES => 'clock',
            self::DIKIRIM => 'box',
            self::SELESAI => 'check',
        };
    }

    /**
     * Map a raw database value to a canonical status, safely handling legacy states.
     */
    public static function fromLegacy(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        if ($value === 'tervalidasi') {
            return self::DIPROSES;
        }

        return self::tryFrom($value);
    }

    /**
     * Determine whether transition from current status to target status is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::MENUNGGU_VALIDASI => $target === self::DIPROSES,
            self::DIPROSES => $target === self::DIKIRIM,
            self::DIKIRIM => $target === self::SELESAI,
            self::SELESAI => false,
        };
    }
}
