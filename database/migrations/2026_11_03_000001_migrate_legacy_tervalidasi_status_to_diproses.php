<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penjualan')) {
            DB::table('penjualan')
                ->where('status_penjualan', 'tervalidasi')
                ->update(['status_penjualan' => 'diproses']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('penjualan')) {
            DB::table('penjualan')
                ->where('status_penjualan', 'diproses')
                ->update(['status_penjualan' => 'tervalidasi']);
        }
    }
};
