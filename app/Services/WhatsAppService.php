<?php

namespace App\Services;

use App\Models\Penjualan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public function sendOrderNotification(Penjualan $transaksi): bool
    {
        $transaksi->loadMissing('pelanggan');

        $target = $this->normalizePhoneNumber($transaksi->pelanggan?->no_wa);

        if ($target === null) {
            Log::warning('Fonnte WhatsApp notification skipped due to invalid customer phone number', [
                'transaksi_id' => $transaksi->id_penjualan,
            ]);

            return false;
        }

        $token = config('services.fonnte.token');

        if (! is_string($token) || trim($token) === '') {
            Log::warning('Fonnte WhatsApp token is not configured', [
                'transaksi_id' => $transaksi->id_penjualan,
            ]);

            return false;
        }

        $message = $this->formatOrderMessage($transaksi);
        $url = (string) config('services.fonnte.url', 'https://api.fonnte.com/send');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->post($url, [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if ($response->successful() && $response->json('status') === true) {
                return true;
            }

            Log::error('Fonnte WhatsApp notification failed', [
                'transaksi_id' => $transaksi->id_penjualan,
                'status_code' => $response->status(),
            ]);

            return false;
        } catch (Throwable) {
            Log::error('Fonnte WhatsApp notification connection error', [
                'transaksi_id' => $transaksi->id_penjualan,
            ]);

            return false;
        }
    }

    public function normalizePhoneNumber(?string $phoneNumber): ?string
    {
        if ($phoneNumber === null) {
            return null;
        }

        $raw = trim($phoneNumber);

        if ($raw === '') {
            return null;
        }

        if (str_contains($raw, '+')) {
            if (substr_count($raw, '+') !== 1 || ! str_starts_with($raw, '+')) {
                return null;
            }
        }

        if (str_contains($raw, '(') || str_contains($raw, ')')) {
            if (substr_count($raw, '(') !== 1 || substr_count($raw, ')') !== 1) {
                return null;
            }

            if (! preg_match('/^\+?[0-9\s\-]*\([0-9]+\)[0-9\s\-]*$/', $raw)) {
                return null;
            }
        } elseif (! preg_match('/^\+?[0-9\s\-]+$/', $raw)) {
            return null;
        }

        $cleaned = str_replace([' ', '-', '(', ')'], '', $raw);

        if (str_starts_with($cleaned, '+628')) {
            $normalized = substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '08')) {
            $normalized = '62'.substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '628')) {
            $normalized = $cleaned;
        } else {
            return null;
        }

        if (! preg_match('/^628[1-9][0-9]{7,11}$/', $normalized)) {
            return null;
        }

        return $normalized;
    }

    public function formatOrderMessage(Penjualan $transaksi): string
    {
        $namaPelanggan = $transaksi->pelanggan?->nama_pelanggan ?: 'Pelanggan';

        return "Halo, {$namaPelanggan}.\n\n"
            ."Pesanan Anda dari PT Halus Ciptanadi telah divalidasi.\n\n"
            ."Nomor transaksi: {$transaksi->nomor_penjualan}\n"
            .'Total: Rp '.number_format((float) $transaksi->total_penjualan, 0, ',', '.')."\n\n"
            .'Terima kasih telah melakukan pemesanan di PT Halus Ciptanadi.';
    }
}
