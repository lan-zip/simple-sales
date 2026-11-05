<?php

namespace Tests\Feature;

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

class DirectorStockReportTest extends TestCase
{
    private User $director;

    private User $admin;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'stock_report_testing',
            'database.connections' => [
                'stock_report_testing' => [
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

        $this->director = $this->createUser('direktur', 'direktur_stok');
        $this->admin = $this->createUser('admin', 'admin_stok');
        $this->sales = $this->createUser('sales', 'sales_stok');
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

    private function createProduct(array $attributes = []): Produk
    {
        static $counter = 1;

        return Produk::create(array_merge([
            'kode_produk' => sprintf('BRG-%03d', $counter++),
            'nama_produk' => 'Produk Tes '.$counter,
            'kategori_produk' => 'Kategori Umum',
            'harga_produk' => 25000.00,
            'stok_produk' => 50,
            'satuan_produk' => 'pcs',
            'status_produk' => 'aktif',
        ], $attributes));
    }

    public function test_guest_cannot_access_director_stock_report(): void
    {
        $response = $this->get('/laporan-stok');
        $response->assertRedirect('/login');
    }

    public function test_sales_user_cannot_access_director_stock_report(): void
    {
        $this->actingAs($this->sales);

        $response = $this->get('/laporan-stok');
        $response->assertForbidden();
    }

    public function test_admin_user_cannot_access_director_stock_report(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/laporan-stok');
        $response->assertForbidden();
    }

    public function test_director_can_access_stock_report_page(): void
    {
        $this->actingAs($this->director);

        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('Laporan Stok');
        $response->assertSee('Informasi ketersediaan dan jumlah stok seluruh produk.');

        // Table headers
        $response->assertSee('Kode Produk');
        $response->assertSee('Nama Produk');
        $response->assertSee('Kategori');
        $response->assertSee('Stok');
        $response->assertSee('Satuan');
        $response->assertSee('Status');

        // Sidebar active link assertion
        $response->assertSee('href="http://localhost/laporan-stok" class="sidebar-item is-active"', false);
    }

    public function test_stock_data_matches_database_product_records(): void
    {
        $this->actingAs($this->director);

        $this->createProduct([
            'kode_produk' => 'PRD-M01',
            'nama_produk' => 'Kopi Arabika Bali',
            'stok_produk' => 45,
            'satuan_produk' => 'bungkus',
        ]);

        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('45');
        $response->assertSee('bungkus');
    }

    public function test_product_fields_displayed_accurately(): void
    {
        $this->actingAs($this->director);

        $this->createProduct([
            'kode_produk' => 'BRG-X01',
            'nama_produk' => 'Minyak Kelapa 1L',
            'kategori_produk' => 'Minyak',
            'stok_produk' => 25,
            'satuan_produk' => 'botol',
            'status_produk' => 'aktif',
        ]);

        $response = $this->get('/laporan-stok');
        $response->assertOk();

        // Asserts all 6 core attributes are visible
        $response->assertSee('BRG-X01');
        $response->assertSee('Minyak Kelapa 1L');
        $response->assertSee('Minyak');
        $response->assertSee('25');
        $response->assertSee('botol');
        $response->assertSee('Aktif');
    }

    public function test_active_and_inactive_products_are_clearly_distinguished(): void
    {
        $this->actingAs($this->director);

        $this->createProduct([
            'kode_produk' => 'PRD-AKTIF',
            'nama_produk' => 'Gula Pasir 1kg',
            'status_produk' => 'aktif',
        ]);

        $this->createProduct([
            'kode_produk' => 'PRD-NONAKTIF',
            'nama_produk' => 'Tepung Terigu 1kg',
            'status_produk' => 'nonaktif',
        ]);

        $response = $this->get('/laporan-stok');
        $response->assertOk();

        // Active product displays success badge
        $response->assertSee('Gula Pasir 1kg');
        $response->assertSee('ui-tone-success', false);
        $response->assertSee('Aktif');

        // Inactive product displays neutral badge
        $response->assertSee('Tepung Terigu 1kg');
        $response->assertSee('ui-tone-neutral', false);
        $response->assertSee('Nonaktif');
    }

    public function test_stock_report_provides_no_mutation_actions(): void
    {
        $this->actingAs($this->director);

        $this->createProduct([
            'kode_produk' => 'PRD-RO',
            'nama_produk' => 'Beras Pandan Wangi',
        ]);

        $response = $this->get('/laporan-stok');
        $response->assertOk();

        // Must not contain mutation routes or buttons
        $response->assertDontSee('/produk/tambah', false);
        $response->assertDontSee('/edit', false);
        $response->assertDontSee('/nonaktifkan', false);
        $response->assertDontSee('Tambah Produk');
        $response->assertDontSee('Ubah');
        $response->assertDontSee('Nonaktifkan');
        $response->assertDontSee('Hapus');
    }

    public function test_pagination_works_when_product_dataset_exceeds_page_limit(): void
    {
        $this->actingAs($this->director);

        // Seed 16 products alphabetically (page limit is 15)
        for ($i = 1; $i <= 16; $i++) {
            $this->createProduct([
                'kode_produk' => sprintf('PRD-%02d', $i),
                'nama_produk' => sprintf('Item Produk %02d', $i),
                'stok_produk' => $i * 10,
            ]);
        }

        // Page 1
        $response1 = $this->get('/laporan-stok');
        $response1->assertOk();
        $response1->assertSee('Item Produk 01');
        $response1->assertSee('Item Produk 15');
        $response1->assertDontSee('Item Produk 16');

        // Page 2
        $response2 = $this->get('/laporan-stok?page=2');
        $response2->assertOk();
        $response2->assertSee('Item Produk 16');
        $response2->assertDontSee('Item Produk 01');
    }

    public function test_stock_updates_on_product_are_immediately_reflected_on_report(): void
    {
        $this->actingAs($this->director);

        $product = $this->createProduct([
            'kode_produk' => 'PRD-LIVE',
            'nama_produk' => 'Susu Kental Manis',
            'stok_produk' => 50,
        ]);

        $response1 = $this->get('/laporan-stok');
        $response1->assertOk();
        $response1->assertSee('50');

        // Stock deduction or adjustment on Produk model
        $product->update(['stok_produk' => 38]);

        // Subsequent request reflects the updated stock immediately
        $response2 = $this->get('/laporan-stok');
        $response2->assertOk();
        $response2->assertSee('38');
        $response2->assertDontSee('50');
    }

    public function test_stock_report_does_not_derive_stock_from_sales_transactions(): void
    {
        $this->actingAs($this->director);

        $product = $this->createProduct([
            'kode_produk' => 'PRD-DIRECT',
            'nama_produk' => 'Teh Celup Kotak',
            'stok_produk' => 100,
        ]);

        $customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-S01',
            'nama_pelanggan' => 'Budi Retail',
            'nama_toko' => 'Toko Budi',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567890',
            'status_pelanggan' => 'aktif',
        ]);

        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-TX-01',
            'tanggal_penjualan' => '2026-11-06',
            'total_penjualan' => 75000.00,
            'status_penjualan' => 'selesai',
        ]);

        // Detail penjualan exists with quantity 30
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $product->id_produk,
            'jumlah_produk' => 30,
            'harga_satuan' => 2500.00,
            'subtotal' => 75000.00,
        ]);

        // Because stock is direct from Produk table (stok_produk = 100), it must display 100, not 70 or 30
        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('100');
        $response->assertDontSee('70');
    }
}
