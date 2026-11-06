<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UxAccessibilityConsistencyTest extends TestCase
{
    private User $admin;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'ux_testing',
            'database.connections' => [
                'ux_testing' => [
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

        $this->admin = User::create([
            'nama_user' => 'Admin Utama',
            'username' => 'admin_ux',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'no_telp' => '08120000001',
            'alamat' => 'Kantor Pusat',
            'status_user' => 'aktif',
        ]);

        $this->sales = User::create([
            'nama_user' => 'Sales Lapangan',
            'username' => 'sales_ux',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'no_telp' => '08120000002',
            'alamat' => 'Area Timur',
            'status_user' => 'aktif',
        ]);
    }

    public function test_produk_validation_messages_are_localized_and_actionable(): void
    {
        $this->actingAs($this->admin);

        // Required fields validation
        $response = $this->post('/produk', []);
        $response->assertSessionHasErrors([
            'kode_produk' => 'Kode produk wajib diisi.',
            'nama_produk' => 'Nama produk wajib diisi.',
            'kategori_produk' => 'Kategori produk wajib diisi.',
            'harga_produk' => 'Harga produk wajib diisi.',
            'stok_produk' => 'Stok produk wajib diisi.',
            'satuan_produk' => 'Satuan produk wajib diisi.',
        ]);

        // Duplicate code validation
        Produk::create([
            'kode_produk' => 'PRD-EXIST',
            'nama_produk' => 'Produk Awal',
            'kategori_produk' => 'Kategori Awal',
            'harga_produk' => 10000,
            'stok_produk' => 10,
            'satuan_produk' => 'pcs',
            'status_produk' => 'aktif',
        ]);

        $duplicateResponse = $this->post('/produk', [
            'kode_produk' => 'PRD-EXIST',
            'nama_produk' => 'Produk Baru',
            'kategori_produk' => 'Kategori Baru',
            'harga_produk' => 15000,
            'stok_produk' => 5,
            'satuan_produk' => 'pcs',
        ]);
        $duplicateResponse->assertSessionHasErrors([
            'kode_produk' => 'Kode produk sudah digunakan.',
        ]);

        // Negative values validation
        $negativeResponse = $this->post('/produk', [
            'kode_produk' => 'PRD-NEG',
            'nama_produk' => 'Produk Negatif',
            'kategori_produk' => 'Kategori Negatif',
            'harga_produk' => -100,
            'stok_produk' => -5,
            'satuan_produk' => 'pcs',
        ]);
        $negativeResponse->assertSessionHasErrors([
            'harga_produk' => 'Harga produk tidak boleh kurang dari 0.',
            'stok_produk' => 'Stok produk tidak boleh kurang dari 0.',
        ]);
    }

    public function test_pelanggan_validation_messages_are_localized_and_actionable(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/pelanggan', []);
        $response->assertSessionHasErrors([
            'kode_pelanggan' => 'Kode pelanggan wajib diisi.',
            'nama_pelanggan' => 'Nama pelanggan wajib diisi.',
            'nama_toko' => 'Nama toko wajib diisi.',
            'alamat_pelanggan' => 'Alamat pelanggan wajib diisi.',
            'no_wa' => 'Nomor WhatsApp wajib diisi.',
        ]);

        Pelanggan::create([
            'kode_pelanggan' => 'PLG-EXIST',
            'nama_pelanggan' => 'Pelanggan A',
            'nama_toko' => 'Toko A',
            'alamat_pelanggan' => 'Jalan A',
            'no_wa' => '08123456789',
            'status_pelanggan' => 'aktif',
        ]);

        $duplicateResponse = $this->post('/pelanggan', [
            'kode_pelanggan' => 'PLG-EXIST',
            'nama_pelanggan' => 'Pelanggan B',
            'nama_toko' => 'Toko B',
            'alamat_pelanggan' => 'Jalan B',
            'no_wa' => '08129876543',
        ]);
        $duplicateResponse->assertSessionHasErrors([
            'kode_pelanggan' => 'Kode pelanggan sudah digunakan.',
        ]);
    }

    public function test_sales_validation_messages_are_localized_and_actionable(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/sales', []);
        $response->assertSessionHasErrors([
            'nama_user' => 'Nama sales wajib diisi.',
            'username' => 'Username wajib diisi.',
            'password' => 'Password wajib diisi.',
            'no_telp' => 'Nomor telepon wajib diisi.',
            'alamat' => 'Alamat wajib diisi.',
        ]);

        $shortPasswordResponse = $this->post('/sales', [
            'nama_user' => 'Sales Baru',
            'username' => 'sales_baru',
            'password' => '12345',
            'no_telp' => '08129999999',
            'alamat' => 'Jalan Baru',
        ]);
        $shortPasswordResponse->assertSessionHasErrors([
            'password' => 'Password minimal 6 karakter.',
        ]);

        $duplicateResponse = $this->post('/sales', [
            'nama_user' => 'Sales Kloning',
            'username' => 'sales_ux',
            'password' => 'secret123',
            'no_telp' => '08128888888',
            'alamat' => 'Jalan Kloning',
        ]);
        $duplicateResponse->assertSessionHasErrors([
            'username' => 'Username sudah digunakan.',
        ]);
    }

    public function test_order_validation_messages_are_localized_and_actionable(): void
    {
        $this->actingAs($this->sales);

        $response = $this->post('/order', []);
        $response->assertSessionHasErrors([
            'id_pelanggan' => 'Pilih pelanggan terlebih dahulu.',
            'foto_toko' => 'Foto toko wajib diunggah.',
            'produk' => 'Pilih minimal satu produk.',
        ]);

        $customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-ORD',
            'nama_pelanggan' => 'Pelanggan Order',
            'nama_toko' => 'Toko Order',
            'alamat_pelanggan' => 'Jalan Toko',
            'no_wa' => '08123456789',
            'status_pelanggan' => 'aktif',
        ]);

        $productA = Produk::create([
            'kode_produk' => 'PRD-OA',
            'nama_produk' => 'Produk OA',
            'kategori_produk' => 'Kategori OA',
            'harga_produk' => 10000,
            'stok_produk' => 100,
            'satuan_produk' => 'pcs',
            'status_produk' => 'aktif',
        ]);

        $duplicateStoreResponse = $this->post('/order', [
            'id_pelanggan' => $customer->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [
                ['id_produk' => $productA->id_produk, 'jumlah' => 1],
                ['id_produk' => $productA->id_produk, 'jumlah' => 2],
            ],
        ]);
        $duplicateStoreResponse->assertSessionHasErrors([
            'produk.0.id_produk' => 'Produk tidak boleh dipilih lebih dari satu kali.',
        ]);

        // Duplicate product rows in order update
        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-TEST-001',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 10000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
            'foto_toko' => 'foto_toko/test.jpg',
        ]);

        DetailPenjualan::create([
            'id_produk' => $productA->id_produk,
            'id_penjualan' => $order->id_penjualan,
            'jumlah_produk' => 1,
            'harga_satuan' => 10000,
            'subtotal' => 10000,
        ]);

        $duplicateRowResponse = $this->put('/order/'.$order->id_penjualan, [
            'id_pelanggan' => $customer->id_pelanggan,
            'produk' => [
                ['id_produk' => $productA->id_produk, 'jumlah' => 1],
                ['id_produk' => $productA->id_produk, 'jumlah' => 2],
            ],
        ]);
        $duplicateRowResponse->assertSessionHasErrors([
            'produk.0.id_produk' => 'Produk tidak boleh dipilih lebih dari satu kali.',
        ]);
    }

    public function test_order_upload_bukti_validation_messages_are_localized(): void
    {
        $this->actingAs($this->sales);

        $customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-BUKTI',
            'nama_pelanggan' => 'Pelanggan Bukti',
            'nama_toko' => 'Toko Bukti',
            'alamat_pelanggan' => 'Jalan Bukti',
            'no_wa' => '08123456789',
            'status_pelanggan' => 'aktif',
        ]);

        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-BUKTI-001',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 10000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_toko' => 'foto_toko/test.jpg',
        ]);

        $response = $this->post('/order/'.$order->id_penjualan.'/upload-bukti', []);
        $response->assertSessionHasErrors([
            'foto_bukti_penyerahan' => 'Foto bukti penyerahan wajib diunggah.',
        ]);
    }

    public function test_order_delivery_proof_button_has_primary_variant(): void
    {
        $this->actingAs($this->sales);

        $customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-VIEW',
            'nama_pelanggan' => 'Pelanggan View',
            'nama_toko' => 'Toko View',
            'alamat_pelanggan' => 'Jalan View',
            'no_wa' => '08123456789',
            'status_pelanggan' => 'aktif',
        ]);

        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-VIEW-001',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 10000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_toko' => 'foto_toko/test.jpg',
        ]);

        $response = $this->get('/order/'.$order->id_penjualan);
        $response->assertOk();

        // Submit button for upload bukti penyerahan must have primary variant
        $response->assertSee('ui-button--primary', false);
        $response->assertSee('Simpan Bukti Penyerahan');
    }

    public function test_css_contains_keyboard_focus_indicators(): void
    {
        $cssContent = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.ui-button:focus-visible', $cssContent);
        $this->assertStringContainsString('.text-link:focus-visible', $cssContent);
        $this->assertStringContainsString('.ui-page-number:focus-visible', $cssContent);
        $this->assertStringContainsString('outline: 2px solid var(--color-brand-900)', $cssContent);
        $this->assertStringContainsString('box-shadow: 0 0 0 3px rgba(111, 13, 13, .2)', $cssContent);
    }
}
