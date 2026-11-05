<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportPrintTest extends TestCase
{
    private User $director;

    private User $admin;

    private User $sales;

    private Pelanggan $customer;

    private Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'report_print_testing',
            'database.connections' => [
                'report_print_testing' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ],
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);

        DB::purge();
        Http::preventStrayRequests();
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        Storage::fake('public');

        Schema::create('user', function (Blueprint $t) {
            $t->id('id_user');
            foreach (['nama_user', 'username', 'password', 'role', 'no_telp', 'alamat', 'status_user'] as $column) {
                $t->string($column);
            }
            $t->unique('username');
        });

        Schema::create('produk', function (Blueprint $t) {
            $t->id('id_produk');
            foreach (['kode_produk', 'nama_produk', 'kategori_produk', 'satuan_produk', 'status_produk'] as $column) {
                $t->string($column);
            }
            $t->decimal('harga_produk', 12, 2);
            $t->integer('stok_produk');
        });

        Schema::create('pelanggan', function (Blueprint $t) {
            $t->id('id_pelanggan');
            foreach (['kode_pelanggan', 'nama_pelanggan', 'nama_toko', 'alamat_pelanggan', 'no_wa', 'status_pelanggan'] as $column) {
                $t->string($column);
            }
        });

        Schema::create('penjualan', function (Blueprint $t) {
            $t->id('id_penjualan');
            $t->unsignedBigInteger('id_pelanggan');
            $t->unsignedBigInteger('id_user');
            $t->string('nomor_penjualan');
            $t->string('no_wa')->nullable();
            $t->date('tanggal_penjualan');
            $t->decimal('total_penjualan', 12, 2);
            $t->string('status_penjualan');
            foreach (['tanggal_validasi', 'tanggal_selesai'] as $column) {
                $t->date($column)->nullable();
            }
            foreach (['foto_toko', 'foto_bukti_penyerahan'] as $column) {
                $t->string($column)->nullable();
            }
        });

        Schema::create('detail_penjualan', function (Blueprint $t) {
            $t->id('id_detail_penjualan');
            $t->unsignedBigInteger('id_produk');
            $t->unsignedBigInteger('id_penjualan');
            $t->integer('jumlah_produk');
            $t->decimal('harga_satuan', 12, 2);
            $t->decimal('subtotal', 12, 2);
        });

        $this->director = $this->createUser('direktur', 'direktur_test');
        $this->admin = $this->createUser('admin', 'admin_test');
        $this->sales = $this->createUser('sales', 'sales_test');

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-01',
            'nama_pelanggan' => 'I Wayan Sudiarta',
            'nama_toko' => 'Toko Sudiarta',
            'alamat_pelanggan' => 'Jl. Gatot Subroto No. 1',
            'no_wa' => '081234567891',
            'status_pelanggan' => 'aktif',
        ]);

        $this->product = Produk::create([
            'kode_produk' => 'PRD-01',
            'nama_produk' => 'Beras Bali 10kg',
            'kategori_produk' => 'Sembako',
            'harga_produk' => 150000.00,
            'stok_produk' => 100,
            'satuan_produk' => 'sak',
            'status_produk' => 'aktif',
        ]);

        $this->createCompletedSale('PJ-20261001-0001', '2026-10-01', 300000.00);
    }

    private function createUser(string $role, string $username): User
    {
        return User::create([
            'nama_user' => ucfirst($role).' '.$username,
            'username' => $username,
            'password' => Hash::make('password123'),
            'role' => $role,
            'no_telp' => '081234567800',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);
    }

    private function createCompletedSale(string $nomor, string $date, float $total): Penjualan
    {
        $sale = Penjualan::create([
            'id_pelanggan' => $this->customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => $nomor,
            'tanggal_penjualan' => $date,
            'total_penjualan' => $total,
            'status_penjualan' => OrderStatus::SELESAI->value,
            'tanggal_validasi' => $date,
            'tanggal_selesai' => $date,
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $sale->id_penjualan,
            'id_produk' => $this->product->id_produk,
            'jumlah_produk' => 2,
            'harga_satuan' => 150000.00,
            'subtotal' => 300000.00,
        ]);

        return $sale;
    }

    public function test_direktur_can_view_print_action_on_sales_report(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan'));

        $response->assertOk();
        $response->assertSee('data-print', false);
        $response->assertSee('Cetak Laporan');
    }

    public function test_direktur_can_view_print_action_on_stock_report(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.stok'));

        $response->assertOk();
        $response->assertSee('data-print', false);
        $response->assertSee('Cetak Laporan');
    }

    public function test_sales_report_print_button_has_no_print_class(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan'));

        $response->assertOk();
        $response->assertSee('data-print', false);
        $response->assertSee('ui-button ui-button--primary no-print', false);
    }

    public function test_stock_report_print_button_has_no_print_class(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.stok'));

        $response->assertOk();
        $response->assertSee('data-print', false);
        $response->assertSee('ui-button ui-button--primary no-print', false);
    }

    public function test_sales_report_contains_print_header_brand_and_timestamp(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan'));

        $response->assertOk();
        $response->assertSee('print-brand print-report-header', false);
        $response->assertSee('PT. Halus Ciptanadi');
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Dicetak pada:');
        $response->assertSee('print-report-footer print-only', false);
    }

    public function test_stock_report_contains_print_header_brand_and_timestamp(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.stok'));

        $response->assertOk();
        $response->assertSee('print-brand print-report-header', false);
        $response->assertSee('PT. Halus Ciptanadi');
        $response->assertSee('Laporan Stok');
        $response->assertSee('Status Produk:');
        $response->assertSee('Semua Produk (Aktif & Nonaktif)');
        $response->assertSee('Dicetak pada:');
        $response->assertSee('print-report-footer print-only', false);
    }

    public function test_sales_report_print_header_reflects_active_filters(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan', [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-15',
            'id_pelanggan' => $this->customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
        ]));

        $response->assertOk();
        $response->assertSee('Periode:');
        $response->assertSee('01 Okt 2026 s/d 15 Okt 2026');
        $response->assertSee('Pelanggan:');
        $response->assertSee($this->customer->nama_pelanggan.' — '.$this->customer->nama_toko);
        $response->assertSee('Sales:');
        $response->assertSee($this->sales->nama_user);
    }

    public function test_sales_report_print_header_displays_default_when_unfiltered(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan'));

        $response->assertOk();
        $response->assertSee('Semua Periode');
        $response->assertSee('Semua Pelanggan');
        $response->assertSee('Semua Sales');
    }

    public function test_sales_report_hides_filter_form_actions_and_pagination_for_print(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.penjualan'));

        $response->assertOk();
        // Filter card wrapper has no-print
        $response->assertSee('<div class="mb-6 no-print">', false);
        // Table actions column has no-print
        $response->assertSee('<th scope="col" class="numeric no-print">Aksi</th>', false);
        $response->assertSee('<td class="numeric no-print">', false);
        // Pagination has no-print wrapper
        $response->assertSee('<div class="no-print">', false);
    }

    public function test_stock_report_hides_pagination_for_print(): void
    {
        $response = $this->actingAs($this->director)->get(route('laporan.stok'));

        $response->assertOk();
        // Pagination has no-print wrapper
        $response->assertSee('<div class="no-print">', false);
    }

    public function test_unauthenticated_and_unauthorized_users_cannot_access_reports(): void
    {
        // Guests redirected to login
        $this->get(route('laporan.penjualan'))->assertRedirect(route('login'));
        $this->get(route('laporan.stok'))->assertRedirect(route('login'));

        // Admin gets 403
        $this->actingAs($this->admin)->get(route('laporan.penjualan'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('laporan.stok'))->assertForbidden();

        // Sales gets 403
        $this->actingAs($this->sales)->get(route('laporan.penjualan'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('laporan.stok'))->assertForbidden();
    }
}
