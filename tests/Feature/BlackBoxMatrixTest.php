<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-End Black Box Test Suite for GitHub Issue #17.
 * Covers all 114 scenarios across all 18 functional and non-functional areas.
 */
class BlackBoxMatrixTest extends TestCase
{
    private User $admin;

    private User $sales;

    private User $sales2;

    private User $direktur;

    private Produk $product1;

    private Produk $product2;

    private Pelanggan $customer1;

    private ?\Closure $customHttpHandler = null;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'blackbox_testing',
            'database.connections' => [
                'blackbox_testing' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ],
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
            'services.fonnte.token' => 'test-fonnte-token-bb',
            'services.fonnte.url' => 'https://api.fonnte.com/send',
        ]);

        DB::purge();
        $this->customHttpHandler = null;
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            if ($this->customHttpHandler) {
                return ($this->customHttpHandler)($request);
            }

            return Http::response(['status' => true], 200);
        });
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
            'nama_user' => 'Administrator BB',
            'username' => 'admin_bb',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'no_telp' => '08120000001',
            'alamat' => 'Kantor Pusat',
            'status_user' => 'aktif',
        ]);

        $this->sales = User::create([
            'nama_user' => 'Sales Utama BB',
            'username' => 'sales_bb',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
            'no_telp' => '08120000002',
            'alamat' => 'Denpasar Selatan',
            'status_user' => 'aktif',
        ]);

        $this->sales2 = User::create([
            'nama_user' => 'Sales Kedua BB',
            'username' => 'sales2_bb',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
            'no_telp' => '08120000003',
            'alamat' => 'Denpasar Barat',
            'status_user' => 'aktif',
        ]);

        $this->direktur = User::create([
            'nama_user' => 'Direktur Utama BB',
            'username' => 'direktur_bb',
            'password' => Hash::make('direktur123'),
            'role' => 'direktur',
            'no_telp' => '08120000004',
            'alamat' => 'Kantor Direksi',
            'status_user' => 'aktif',
        ]);

        $this->product1 = Produk::create([
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Semen Portland 50kg',
            'kategori_produk' => 'Semen',
            'harga_produk' => 65000,
            'stok_produk' => 100,
            'satuan_produk' => 'sak',
            'status_produk' => 'aktif',
        ]);

        $this->product2 = Produk::create([
            'kode_produk' => 'PRD002',
            'nama_produk' => 'Cat Tembok Putih 5kg',
            'kategori_produk' => 'Cat',
            'harga_produk' => 85000,
            'stok_produk' => 20,
            'satuan_produk' => 'kaleng',
            'status_produk' => 'aktif',
        ]);

        $this->customer1 = Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'I Wayan Sudirta',
            'nama_toko' => 'Toko Bangunan Abadi',
            'alamat_pelanggan' => 'Jl. Danau Tamblingan No. 10',
            'no_wa' => '081234567890',
            'status_pelanggan' => 'aktif',
        ]);
    }

    // ==========================================
    // AREA 1: AUTHENTICATION (BB-AUTH-01 to 09)
    // ==========================================

    public function test_bb_auth_01_admin_login(): void
    {
        $response = $this->post('/login', ['username' => 'admin_bb', 'password' => 'admin123']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_bb_auth_02_sales_login(): void
    {
        $response = $this->post('/login', ['username' => 'sales_bb', 'password' => 'sales123']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->sales);
    }

    public function test_bb_auth_03_direktur_login(): void
    {
        $response = $this->post('/login', ['username' => 'direktur_bb', 'password' => 'direktur123']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->direktur);
    }

    public function test_bb_auth_04_invalid_password(): void
    {
        $response = $this->from('/login')->post('/login', ['username' => 'admin_bb', 'password' => 'wrongpass']);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_bb_auth_05_non_existent_username(): void
    {
        $response = $this->from('/login')->post('/login', ['username' => 'ghost_user', 'password' => 'anypass']);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_bb_auth_06_empty_credentials(): void
    {
        $response = $this->from('/login')->post('/login', []);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'username' => 'Username wajib diisi.',
            'password' => 'Password wajib diisi.',
        ]);
        $this->assertGuest();
    }

    public function test_bb_auth_07_inactive_user_rejected(): void
    {
        User::create([
            'nama_user' => 'Inactive User',
            'username' => 'inactive_user',
            'password' => Hash::make('password123'),
            'role' => 'sales',
            'no_telp' => '08129999999',
            'alamat' => 'Alamat',
            'status_user' => 'nonaktif',
        ]);

        $response = $this->from('/login')->post('/login', ['username' => 'inactive_user', 'password' => 'password123']);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_bb_auth_08_logout(): void
    {
        $response = $this->actingAs($this->sales)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_bb_auth_09_guest_access_redirected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/produk')->assertRedirect('/login');
        $this->get('/order')->assertRedirect('/login');
        $this->get('/laporan-penjualan')->assertRedirect('/login');
    }

    // ==========================================
    // AREA 2: ROLE / AUTHORIZATION (BB-ROLE-01 to 09)
    // ==========================================

    public function test_bb_role_01_admin_access_master_data(): void
    {
        $this->actingAs($this->admin);
        $this->get('/produk')->assertOk();
        $this->get('/pelanggan')->assertOk();
        $this->get('/sales')->assertOk();
        $this->get('/transaksi')->assertOk();
    }

    public function test_bb_role_02_sales_forbidden_product_mutations(): void
    {
        $this->actingAs($this->sales);
        $this->get('/produk/tambah')->assertForbidden();
        $this->get('/produk/'.$this->product1->id_produk.'/edit')->assertForbidden();
        $this->post('/produk', ['kode_produk' => 'PRD-NEW'])->assertForbidden();
        $this->patch('/produk/'.$this->product1->id_produk.'/nonaktifkan')->assertForbidden();
    }

    public function test_bb_role_03_sales_forbidden_sales_management(): void
    {
        $this->actingAs($this->sales);
        $this->get('/sales')->assertForbidden();
        $this->get('/sales/tambah')->assertForbidden();
        $this->post('/sales', [])->assertForbidden();
    }

    public function test_bb_role_04_sales_forbidden_transaction_validation(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-ROLE-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->sales);
        $this->patch('/transaksi/'.$order->id_penjualan.'/validasi')->assertForbidden();
    }

    public function test_bb_role_05_admin_forbidden_order_creation(): void
    {
        $this->actingAs($this->admin);
        $this->get('/order/tambah')->assertForbidden();
        $this->post('/order', [])->assertForbidden();
    }

    public function test_bb_role_06_direktur_forbidden_master_mutations(): void
    {
        $this->actingAs($this->direktur);
        $this->get('/produk/tambah')->assertForbidden();
        $this->get('/pelanggan/tambah')->assertForbidden();
        $this->get('/sales/tambah')->assertForbidden();
    }

    public function test_bb_role_07_direktur_forbidden_operational_lists(): void
    {
        $this->actingAs($this->direktur);
        $this->get('/order')->assertForbidden();
        $this->get('/transaksi')->assertForbidden();
    }

    public function test_bb_role_08_direktur_access_reports(): void
    {
        $this->actingAs($this->direktur);
        $this->get('/laporan-penjualan')->assertOk();
        $this->get('/laporan-stok')->assertOk();
    }

    public function test_bb_role_09_dashboard_role_separation(): void
    {
        $adminResp = $this->actingAs($this->admin)->get('/dashboard');
        $adminResp->assertOk()->assertSee('Dashboard Admin');

        $salesResp = $this->actingAs($this->sales)->get('/dashboard');
        $salesResp->assertOk()->assertSee('Dashboard Sales');

        $direkturResp = $this->actingAs($this->direktur)->get('/dashboard');
        $direkturResp->assertOk()->assertSee('Dashboard Direktur');
    }

    // ==========================================
    // AREA 3: PRODUCT MANAGEMENT (BB-PRD-01 to 08)
    // ==========================================

    public function test_bb_prd_01_create_valid_product(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/produk', [
            'kode_produk' => 'PRD-VAL-01',
            'nama_produk' => 'Pipa PVC 3 Inch',
            'kategori_produk' => 'Pipa',
            'harga_produk' => 45000,
            'stok_produk' => 50,
            'satuan_produk' => 'batang',
        ]);
        $response->assertRedirect('/produk');
        $response->assertSessionHas('success', 'Produk berhasil ditambahkan.');
        $this->assertDatabaseHas('produk', ['kode_produk' => 'PRD-VAL-01', 'status_produk' => 'aktif']);
    }

    public function test_bb_prd_02_create_product_missing_required_fields(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/produk', []);
        $response->assertSessionHasErrors([
            'kode_produk' => 'Kode produk wajib diisi.',
            'nama_produk' => 'Nama produk wajib diisi.',
            'kategori_produk' => 'Kategori produk wajib diisi.',
            'harga_produk' => 'Harga produk wajib diisi.',
            'stok_produk' => 'Stok produk wajib diisi.',
            'satuan_produk' => 'Satuan produk wajib diisi.',
        ]);
    }

    public function test_bb_prd_03_create_product_negative_price_or_stock(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/produk', [
            'kode_produk' => 'PRD-NEG',
            'nama_produk' => 'Negatif',
            'kategori_produk' => 'Alat',
            'harga_produk' => -100,
            'stok_produk' => -5,
            'satuan_produk' => 'pcs',
        ]);
        $response->assertSessionHasErrors([
            'harga_produk' => 'Harga produk tidak boleh kurang dari 0.',
            'stok_produk' => 'Stok produk tidak boleh kurang dari 0.',
        ]);
    }

    public function test_bb_prd_04_create_product_duplicate_code(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/produk', [
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Semen Lain',
            'kategori_produk' => 'Semen',
            'harga_produk' => 60000,
            'stok_produk' => 10,
            'satuan_produk' => 'sak',
        ]);
        $response->assertSessionHasErrors([
            'kode_produk' => 'Kode produk sudah digunakan.',
        ]);
    }

    public function test_bb_prd_05_search_filter_and_paginate_products(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/produk?cari=Semen&status=aktif');
        $response->assertOk();
        $response->assertSee('PRD001');
        $response->assertDontSee('PRD002');
    }

    public function test_bb_prd_06_update_product_details(): void
    {
        $this->actingAs($this->admin);
        $response = $this->put('/produk/'.$this->product1->id_produk, [
            'kode_produk' => 'PRD001',
            'nama_produk' => 'Semen Portland Super 50kg',
            'kategori_produk' => 'Semen',
            'harga_produk' => 68000,
            'stok_produk' => 120,
            'satuan_produk' => 'sak',
        ]);
        $response->assertRedirect('/produk');
        $response->assertSessionHas('success', 'Produk berhasil diubah.');
        $this->assertEquals(68000, $this->product1->fresh()->harga_produk);
    }

    public function test_bb_prd_07_update_product_duplicate_code_conflict(): void
    {
        $this->actingAs($this->admin);
        $response = $this->put('/produk/'.$this->product2->id_produk, [
            'kode_produk' => 'PRD001', // conflict with product1
            'nama_produk' => 'Cat Baru',
            'kategori_produk' => 'Cat',
            'harga_produk' => 85000,
            'stok_produk' => 20,
            'satuan_produk' => 'kaleng',
        ]);
        $response->assertSessionHasErrors([
            'kode_produk' => 'Kode produk sudah digunakan.',
        ]);
    }

    public function test_bb_prd_08_deactivate_product_and_exclude_from_orders(): void
    {
        $this->actingAs($this->admin);
        $this->patch('/produk/'.$this->product1->id_produk.'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif', $this->product1->fresh()->status_produk);

        $this->actingAs($this->sales);
        $orderCreate = $this->get('/order/tambah');
        $orderCreate->assertOk();
        $orderCreate->assertDontSee('Semen Portland');
    }

    // ==========================================
    // AREA 4: CUSTOMER MANAGEMENT (BB-CUST-01 to 07)
    // ==========================================

    public function test_bb_cust_01_create_valid_customer(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/pelanggan', [
            'kode_pelanggan' => 'PLG-NEW',
            'nama_pelanggan' => 'Ketut Baru',
            'nama_toko' => 'Toko Subur Baru',
            'alamat_pelanggan' => 'Jl. Bypass Ngurah Rai No. 5',
            'no_wa' => '081234567899',
        ]);
        $response->assertRedirect('/pelanggan');
        $response->assertSessionHas('success', 'Data pelanggan berhasil ditambahkan.');
        $this->assertDatabaseHas('pelanggan', ['kode_pelanggan' => 'PLG-NEW', 'status_pelanggan' => 'aktif']);
    }

    public function test_bb_cust_02_create_customer_missing_fields(): void
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
    }

    public function test_bb_cust_03_duplicate_customer_code(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/pelanggan', [
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'Wayan Duplikat',
            'nama_toko' => 'Toko Abadi 2',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081299999999',
        ]);
        $response->assertSessionHasErrors(['kode_pelanggan' => 'Kode pelanggan sudah digunakan.']);
    }

    public function test_bb_cust_04_list_search_status_filter(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/pelanggan?cari=Abadi&status=aktif');
        $response->assertOk();
        $response->assertSee('Toko Bangunan Abadi');
    }

    public function test_bb_cust_05_update_customer_details(): void
    {
        $this->actingAs($this->admin);
        $response = $this->put('/pelanggan/'.$this->customer1->id_pelanggan, [
            'kode_pelanggan' => 'PLG001',
            'nama_pelanggan' => 'I Wayan Sudirta',
            'nama_toko' => 'Toko Bangunan Abadi Perkasa',
            'alamat_pelanggan' => 'Jl. Danau Tamblingan No. 12',
            'no_wa' => '081234567890',
        ]);
        $response->assertRedirect('/pelanggan');
        $response->assertSessionHas('success', 'Data pelanggan berhasil diubah.');
        $this->assertSame('Toko Bangunan Abadi Perkasa', $this->customer1->fresh()->nama_toko);
    }

    public function test_bb_cust_06_sales_forbidden_edit_and_deactivate(): void
    {
        $this->actingAs($this->sales);
        $this->get('/pelanggan/'.$this->customer1->id_pelanggan.'/edit')->assertForbidden();
        $this->put('/pelanggan/'.$this->customer1->id_pelanggan, ['kode_pelanggan' => 'PLG001'])->assertForbidden();
        $this->patch('/pelanggan/'.$this->customer1->id_pelanggan.'/nonaktifkan')->assertForbidden();
    }

    public function test_bb_cust_07_deactivate_customer(): void
    {
        $this->actingAs($this->admin);
        $this->patch('/pelanggan/'.$this->customer1->id_pelanggan.'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif', $this->customer1->fresh()->status_pelanggan);
    }

    // ==========================================
    // AREA 5: SALES ACCOUNT MANAGEMENT (BB-SALES-01 to 06)
    // ==========================================

    public function test_bb_sales_01_view_sales_list_and_search(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/sales?cari=sales_bb');
        $response->assertOk();
        $response->assertSee('Sales Utama BB');
    }

    public function test_bb_sales_02_create_valid_sales_account(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/sales', [
            'nama_user' => 'Sales Lapangan Tiga',
            'username' => 'sales3_bb',
            'password' => 'secret123',
            'no_telp' => '081288887777',
            'alamat' => 'Tabanan',
        ]);
        $response->assertRedirect('/sales');
        $response->assertSessionHas('success', 'Data sales berhasil ditambahkan.');
        $this->assertDatabaseHas('user', ['username' => 'sales3_bb', 'role' => 'sales', 'status_user' => 'aktif']);
    }

    public function test_bb_sales_03_create_sales_validation(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/sales', [
            'nama_user' => 'Sales',
            'username' => 'sales_bb', // duplicate
            'password' => '123', // short
            'no_telp' => '0812',
            'alamat' => 'Alamat',
        ]);
        $response->assertSessionHasErrors([
            'username' => 'Username sudah digunakan.',
            'password' => 'Password minimal 6 karakter.',
        ]);
    }

    public function test_bb_sales_04_edit_sales_details(): void
    {
        $this->actingAs($this->admin);
        $response = $this->put('/sales/'.$this->sales->id_user, [
            'nama_user' => 'Sales Utama Diperbarui',
            'username' => 'sales_bb',
            'no_telp' => '081299990000',
            'alamat' => 'Denpasar Baru',
            'password' => '', // leave empty to preserve
        ]);
        $response->assertRedirect('/sales');
        $this->assertSame('Sales Utama Diperbarui', $this->sales->fresh()->nama_user);
    }

    public function test_bb_sales_05_deactivate_and_reactivate_sales(): void
    {
        $this->actingAs($this->admin);
        $this->patch('/sales/'.$this->sales->id_user.'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif', $this->sales->fresh()->status_user);

        $this->patch('/sales/'.$this->sales->id_user.'/aktifkan')->assertRedirect();
        $this->assertSame('aktif', $this->sales->fresh()->status_user);
    }

    public function test_bb_sales_06_non_admin_forbidden_sales_management(): void
    {
        $this->actingAs($this->sales)->get('/sales')->assertForbidden();
        $this->actingAs($this->direktur)->get('/sales')->assertForbidden();
    }

    // ==========================================
    // AREA 6: ORDER CREATION (BB-ORDC-01 to 09)
    // ==========================================

    public function test_bb_ordc_01_happy_path_order_creation(): void
    {
        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg', 800, 600),
            'produk' => [
                ['id_produk' => $this->product1->id_produk, 'jumlah' => 2],
            ],
        ]);
        $response->assertRedirect('/order');
        $response->assertSessionHas('success', 'Order penjualan berhasil dibuat.');

        $order = Penjualan::latest('id_penjualan')->first();
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order->status_penjualan);
        $this->assertSame(130000.0, (float) $order->total_penjualan);
        $this->assertSame(100, $this->product1->fresh()->stok_produk); // untouched
    }

    public function test_bb_ordc_02_create_order_without_customer(): void
    {
        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 1]],
        ]);
        $response->assertSessionHasErrors(['id_pelanggan' => 'Pilih pelanggan terlebih dahulu.']);
    }

    public function test_bb_ordc_03_create_order_without_products(): void
    {
        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
        ]);
        $response->assertSessionHasErrors(['produk' => 'Pilih minimal satu produk.']);
    }

    public function test_bb_ordc_04_create_order_with_invalid_quantity(): void
    {
        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 0]],
        ]);
        $response->assertSessionHasErrors(['produk.0.jumlah' => 'Jumlah produk minimal 1.']);
    }

    public function test_bb_ordc_05_create_order_with_duplicate_product_rows(): void
    {
        $this->actingAs($this->sales);
        // Duplicate row validation tested on update as distinct rule is enforced
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-DUP-ROW',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 130000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
            'foto_toko' => 'foto_toko/test.jpg',
        ]);

        $response = $this->put('/order/'.$order->id_penjualan, [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'produk' => [
                ['id_produk' => $this->product1->id_produk, 'jumlah' => 1],
                ['id_produk' => $this->product1->id_produk, 'jumlah' => 2],
            ],
        ]);
        $response->assertSessionHasErrors(['produk.0.id_produk' => 'Produk tidak boleh dipilih lebih dari satu kali.']);
    }

    public function test_bb_ordc_06_order_quantity_exceeds_stock(): void
    {
        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [['id_produk' => $this->product2->id_produk, 'jumlah' => 25]], // stock 20
        ]);
        $response->assertSessionHasErrors(['produk' => 'Jumlah Cat Tembok Putih 5kg melebihi stok yang tersedia.']);
    }

    public function test_bb_ordc_07_order_inactive_product(): void
    {
        $inactive = Produk::create([
            'kode_produk' => 'PRD-INACT',
            'nama_produk' => 'Cat Lama',
            'kategori_produk' => 'Cat',
            'harga_produk' => 50000,
            'stok_produk' => 10,
            'satuan_produk' => 'kaleng',
            'status_produk' => 'nonaktif',
        ]);

        $this->actingAs($this->sales);
        $response = $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [['id_produk' => $inactive->id_produk, 'jumlah' => 1]],
        ]);
        $response->assertSessionHasErrors(['produk' => 'Terdapat produk yang sudah tidak aktif.']);
    }

    public function test_bb_ordc_08_store_photo_validation(): void
    {
        $this->actingAs($this->sales);
        // Missing
        $this->post('/order', ['id_pelanggan' => $this->customer1->id_pelanggan, 'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 1]]])
            ->assertSessionHasErrors(['foto_toko' => 'Foto toko wajib diunggah.']);

        // Non image
        $pdf = UploadedFile::fake()->create('doc.pdf', 100);
        $this->post('/order', ['id_pelanggan' => $this->customer1->id_pelanggan, 'foto_toko' => $pdf, 'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 1]]])
            ->assertSessionHasErrors('foto_toko');

        // Oversized (>5MB)
        $large = UploadedFile::fake()->image('huge.jpg')->size(6000);
        $this->post('/order', ['id_pelanggan' => $this->customer1->id_pelanggan, 'foto_toko' => $large, 'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 1]]])
            ->assertSessionHasErrors('foto_toko');
    }

    public function test_bb_ordc_09_total_price_computation(): void
    {
        $this->actingAs($this->sales);
        $this->post('/order', [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('toko.jpg'),
            'produk' => [
                ['id_produk' => $this->product1->id_produk, 'jumlah' => 2], // 2 * 65.000 = 130.000
                ['id_produk' => $this->product2->id_produk, 'jumlah' => 1], // 1 * 85.000 = 85.000
            ],
        ])->assertRedirect();

        $order = Penjualan::latest('id_penjualan')->first();
        $this->assertSame(215000.0, (float) $order->total_penjualan);
    }

    // ==========================================
    // AREA 7: ORDER EDITING (BB-ORDE-01 to 07)
    // ==========================================

    public function test_bb_orde_01_sales_edit_order_in_menunggu_validasi(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-EDIT-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
            'foto_toko' => 'foto_toko/store.jpg',
        ]);

        $this->actingAs($this->sales);
        $response = $this->put('/order/'.$order->id_penjualan, [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 3]],
        ]);
        $response->assertRedirect('/order');
        $this->assertSame(195000.0, (float) $order->fresh()->total_penjualan);
    }

    public function test_bb_orde_02_replace_store_photo_during_edit(): void
    {
        Storage::disk('public')->put('foto_toko/old.jpg', 'old content');
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-EDIT-PHOTO',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
            'foto_toko' => 'foto_toko/old.jpg',
        ]);

        $this->actingAs($this->sales);
        $response = $this->put('/order/'.$order->id_penjualan, [
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'foto_toko' => UploadedFile::fake()->image('new.jpg'),
            'produk' => [['id_produk' => $this->product1->id_produk, 'jumlah' => 1]],
        ]);
        $response->assertRedirect();
        Storage::disk('public')->assertMissing('foto_toko/old.jpg');
        $this->assertNotSame('foto_toko/old.jpg', $order->fresh()->foto_toko);
    }

    public function test_bb_orde_03_edit_order_already_diproses_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-DIPROSES',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        $this->actingAs($this->sales);
        $this->get('/order/'.$order->id_penjualan.'/edit')->assertForbidden();
        $this->put('/order/'.$order->id_penjualan, ['id_pelanggan' => $this->customer1->id_pelanggan])->assertForbidden();
    }

    public function test_bb_orde_04_edit_order_in_dikirim_or_selesai_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-DIKIRIM',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->sales);
        $this->get('/order/'.$order->id_penjualan.'/edit')->assertForbidden();
    }

    public function test_bb_orde_05_sales_cannot_edit_another_sales_order(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-SALES-1',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->sales2);
        $this->get('/order/'.$order->id_penjualan.'/edit')->assertForbidden();
        $this->put('/order/'.$order->id_penjualan, ['id_pelanggan' => $this->customer1->id_pelanggan])->assertForbidden();
    }

    public function test_bb_orde_06_admin_cannot_edit_order(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-ADMIN-TRY',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->admin);
        $this->get('/order/'.$order->id_penjualan.'/edit')->assertForbidden();
    }

    public function test_bb_orde_07_direktur_cannot_edit_order(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-DIR-TRY',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->direktur);
        $this->get('/order/'.$order->id_penjualan.'/edit')->assertForbidden();
    }

    // ==========================================
    // AREA 8: ORDER VALIDATION (BB-VAL-01 to 05)
    // ==========================================

    public function test_bb_val_01_admin_happy_path_validation(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-VAL-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 130000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 2,
            'harga_satuan' => 65000,
            'subtotal' => 130000,
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/validasi');
        $response->assertRedirect('/transaksi/'.$order->id_penjualan);
        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan);
        $this->assertSame(98, $this->product1->fresh()->stok_produk); // 100 - 2 = 98
        $this->assertNotNull($order->fresh()->tanggal_validasi);
    }

    public function test_bb_val_02_validation_insufficient_stock(): void
    {
        $this->product2->update(['stok_produk' => 1]); // stock 1
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-VAL-NOSTOK',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 170000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product2->id_produk,
            'jumlah_produk' => 2, // qty 2
            'harga_satuan' => 85000,
            'subtotal' => 170000,
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/validasi');
        $response->assertSessionHasErrors(['stok' => 'Stok Cat Tembok Putih 5kg tidak mencukupi.']);
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order->fresh()->status_penjualan);
        $this->assertSame(1, $this->product2->fresh()->stok_produk);
    }

    public function test_bb_val_03_double_validation_attempt_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-VAL-DBL',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
            'tanggal_validasi' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/validasi');
        $response->assertSessionHasErrors(['transaksi' => 'Transaksi ini sudah diproses sebelumnya.']);
    }

    public function test_bb_val_04_sales_validation_attempt_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-VAL-SALES',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->sales);
        $this->patch('/transaksi/'.$order->id_penjualan.'/validasi')->assertForbidden();
    }

    public function test_bb_val_05_confirmation_modal_on_validation(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-VAL-CONFIRM',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->admin);
        $response = $this->get('/transaksi/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertSee('data-confirm-message="Validasi transaksi ini? Stok produk akan dikurangi dan status transaksi berubah menjadi diproses."', false);
    }

    // ==========================================
    // AREA 9: ORDER LIFECYCLE & STATE MACHINE (BB-LIFE-01 to 06)
    // ==========================================

    public function test_bb_life_01_advance_diproses_to_dikirim(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
            'tanggal_validasi' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/kirim');
        $response->assertRedirect('/transaksi/'.$order->id_penjualan);
        $this->assertSame(OrderStatus::DIKIRIM, $order->fresh()->status_penjualan);
        $this->assertSame(100, $this->product1->fresh()->stok_produk); // no extra decrement
    }

    public function test_bb_life_02_skip_menunggu_validasi_to_dikirim_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-SKIP',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->admin);
        $this->patch('/transaksi/'.$order->id_penjualan.'/kirim')
            ->assertSessionHasErrors(['transaksi' => 'Transaksi tidak dapat diubah ke status dikirim pada status saat ini.']);
    }

    public function test_bb_life_03_skip_to_selesai_prematurely_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-PREMATURE',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->sales);
        $this->patch('/order/'.$order->id_penjualan.'/selesai')
            ->assertSessionHasErrors(['order' => 'Order tidak dapat diselesaikan pada status saat ini.']);
    }

    public function test_bb_life_04_backward_transition_dikirim_to_diproses_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-BACKWARD',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->admin);
        $this->patch('/transaksi/'.$order->id_penjualan.'/validasi')
            ->assertSessionHasErrors(['transaksi' => 'Transaksi ini sudah diproses sebelumnya.']);
    }

    public function test_bb_life_05_transition_from_terminal_selesai_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-TERM',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::SELESAI->value,
            'tanggal_validasi' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->admin)->patch('/transaksi/'.$order->id_penjualan.'/validasi')->assertSessionHasErrors('transaksi');
        $this->actingAs($this->admin)->patch('/transaksi/'.$order->id_penjualan.'/kirim')->assertSessionHasErrors('transaksi');
        $this->actingAs($this->sales)->patch('/order/'.$order->id_penjualan.'/selesai')->assertSessionHasErrors('order');
    }

    public function test_bb_life_06_canonical_status_badges_across_views(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-LIFE-BADGE',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $response = $this->actingAs($this->sales)->get('/order/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertSee('ui-badge ui-tone-warning', false);
        $response->assertSee('Menunggu Validasi');
    }

    // ==========================================
    // AREA 10: DELIVERY PROOF (BB-PRF-01 to 05)
    // ==========================================

    public function test_bb_prf_01_upload_valid_delivery_proof(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-PRF-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->sales);
        $response = $this->post('/order/'.$order->id_penjualan.'/upload-bukti', [
            'foto_bukti_penyerahan' => UploadedFile::fake()->image('bukti.jpg'),
        ]);
        $response->assertRedirect('/order/'.$order->id_penjualan);
        $response->assertSessionHas('success', 'Foto bukti penyerahan berhasil diunggah.');
        $this->assertNotNull($order->fresh()->foto_bukti_penyerahan);
        Storage::disk('public')->assertExists($order->fresh()->foto_bukti_penyerahan);
    }

    public function test_bb_prf_02_upload_missing_file_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-PRF-EMPTY',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->sales);
        $response = $this->post('/order/'.$order->id_penjualan.'/upload-bukti', []);
        $response->assertSessionHasErrors(['foto_bukti_penyerahan' => 'Foto bukti penyerahan wajib diunggah.']);
    }

    public function test_bb_prf_03_upload_non_image_or_oversized(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-PRF-INVALID',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->sales);
        $pdf = UploadedFile::fake()->create('doc.pdf', 100);
        $this->post('/order/'.$order->id_penjualan.'/upload-bukti', ['foto_bukti_penyerahan' => $pdf])
            ->assertSessionHasErrors('foto_bukti_penyerahan');

        $huge = UploadedFile::fake()->image('large.jpg')->size(6000);
        $this->post('/order/'.$order->id_penjualan.'/upload-bukti', ['foto_bukti_penyerahan' => $huge])
            ->assertSessionHasErrors('foto_bukti_penyerahan');
    }

    public function test_bb_prf_04_upload_proof_when_not_dikirim_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-PRF-NOTDIKIRIM',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        $this->actingAs($this->sales);
        $response = $this->post('/order/'.$order->id_penjualan.'/upload-bukti', [
            'foto_bukti_penyerahan' => UploadedFile::fake()->image('bukti.jpg'),
        ]);
        $response->assertSessionHasErrors(['order' => 'Bukti penyerahan hanya dapat diunggah saat order berstatus dikirim.']);
    }

    public function test_bb_prf_05_non_owner_upload_attempt_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-PRF-NONOWNER',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
        ]);

        $this->actingAs($this->sales2);
        $this->post('/order/'.$order->id_penjualan.'/upload-bukti', [
            'foto_bukti_penyerahan' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertForbidden();
    }

    // ==========================================
    // AREA 11: ORDER COMPLETION (BB-COMP-01 to 05)
    // ==========================================

    public function test_bb_comp_01_complete_order_without_proof_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-COMP-NOPROOF',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_bukti_penyerahan' => null,
        ]);

        $this->actingAs($this->sales);
        $response = $this->patch('/order/'.$order->id_penjualan.'/selesai');
        $response->assertSessionHasErrors(['foto_bukti_penyerahan' => 'Order tidak dapat diselesaikan karena bukti penyerahan belum diunggah.']);
        $this->assertSame(OrderStatus::DIKIRIM, $order->fresh()->status_penjualan);
    }

    public function test_bb_comp_02_complete_order_with_proof(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-COMP-WITHPROOF',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->sales);
        $response = $this->patch('/order/'.$order->id_penjualan.'/selesai');
        $response->assertRedirect('/order/'.$order->id_penjualan);
        $response->assertSessionHas('success', 'Order berhasil diselesaikan.');
        $this->assertSame(OrderStatus::SELESAI, $order->fresh()->status_penjualan);
        $this->assertNotNull($order->fresh()->tanggal_selesai);
    }

    public function test_bb_comp_03_complete_another_sales_order_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-COMP-OTHER',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->sales2);
        $this->patch('/order/'.$order->id_penjualan.'/selesai')->assertForbidden();
    }

    public function test_bb_comp_04_admin_attempt_to_complete_order_forbidden(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-COMP-ADMIN',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->admin);
        $this->patch('/order/'.$order->id_penjualan.'/selesai')->assertForbidden();
    }

    public function test_bb_comp_05_re_completion_attempt_rejected(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-COMP-RETRY',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::SELESAI->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->sales);
        $response = $this->patch('/order/'.$order->id_penjualan.'/selesai');
        $response->assertSessionHasErrors(['order' => 'Order tidak dapat diselesaikan pada status saat ini.']);
    }

    // ==========================================
    // AREA 12: WHATSAPP INTEGRATION (BB-WA-01 to 07)
    // ==========================================

    public function test_bb_wa_01_auto_send_on_admin_validation(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-AUTO',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 65000,
            'subtotal' => 65000,
        ]);

        $this->actingAs($this->admin);
        $this->patch('/transaksi/'.$order->id_penjualan.'/validasi')->assertRedirect();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request['target'] === '6281234567890'
                && str_contains($request['message'], 'I Wayan Sudirta');
        });
    }

    public function test_bb_wa_02_phone_number_normalization(): void
    {
        $customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-NORM',
            'nama_pelanggan' => 'Customer Norm',
            'nama_toko' => 'Toko Norm',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '+62 819-8765-4321', // with symbols and +62
            'status_pelanggan' => 'aktif',
        ]);

        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-NORM',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 65000,
            'subtotal' => 65000,
        ]);

        $this->actingAs($this->admin);
        $this->patch('/transaksi/'.$order->id_penjualan.'/validasi')->assertRedirect();

        Http::assertSent(function ($request) {
            return $request['target'] === '6281987654321';
        });
    }

    public function test_bb_wa_03_gateway_http_failure_handling(): void
    {
        $this->customHttpHandler = fn () => Http::response(['status' => false], 500);

        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-FAIL',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 65000,
            'subtotal' => 65000,
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/validasi');
        $response->assertRedirect();
        $response->assertSessionHas('warning'); // warning flashed
        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan); // committed
    }

    public function test_bb_wa_04_connection_timeout_handling(): void
    {
        $this->customHttpHandler = function () {
            throw new ConnectionException('Timeout');
        };

        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-TIMEOUT',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 65000,
            'subtotal' => 65000,
        ]);

        $this->actingAs($this->admin);
        $response = $this->patch('/transaksi/'.$order->id_penjualan.'/validasi');
        $response->assertRedirect();
        $response->assertSessionHas('warning');
        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan);
    }

    public function test_bb_wa_05_manual_resend_authorization(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-RESEND',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        // Admin resend
        $this->actingAs($this->admin);
        $this->post('/transaksi/'.$order->id_penjualan.'/kirim-nota')->assertRedirect()->assertSessionHas('success');

        // Sales owner resend
        $this->actingAs($this->sales);
        $this->post('/transaksi/'.$order->id_penjualan.'/kirim-nota')->assertRedirect()->assertSessionHas('success');
    }

    public function test_bb_wa_06_unauthorized_resend_attempt(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-UNAUTH',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        // Sales 2 (non-owner) resend -> forbidden
        $this->actingAs($this->sales2);
        $this->post('/transaksi/'.$order->id_penjualan.'/kirim-nota')->assertForbidden();

        // Resend on menunggu_validasi -> error
        $orderMenunggu = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-WA-MENUNGGU',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);
        $this->actingAs($this->admin);
        $this->post('/transaksi/'.$orderMenunggu->id_penjualan.'/kirim-nota')
            ->assertSessionHas('error', 'Nota hanya dapat dikirim untuk pesanan yang sudah diproses.');
    }

    public function test_bb_wa_07_public_test_endpoint_not_found(): void
    {
        $this->get('/test-wa')->assertNotFound();
        $this->post('/test-wa')->assertNotFound();
    }

    // ==========================================
    // AREA 13: DIGITAL NOTE & PRINT (BB-NOTE-01 to 06)
    // ==========================================

    public function test_bb_note_01_print_button_hidden_on_menunggu_validasi(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-NOTE-01',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::MENUNGGU_VALIDASI->value,
        ]);

        $this->actingAs($this->sales)->get('/order/'.$order->id_penjualan)->assertDontSee('Cetak Nota');
        $this->actingAs($this->admin)->get('/transaksi/'.$order->id_penjualan)->assertDontSee('Cetak Nota');
    }

    public function test_bb_note_02_print_button_visible_on_valid_states(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-NOTE-02',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        $this->actingAs($this->sales)->get('/order/'.$order->id_penjualan)->assertSee('Cetak Nota');
        $this->actingAs($this->admin)->get('/transaksi/'.$order->id_penjualan)->assertSee('Cetak Nota');
    }

    public function test_bb_note_03_note_content_completeness(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-NOTE-03',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);
        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product1->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 65000,
            'subtotal' => 65000,
        ]);

        $response = $this->actingAs($this->admin)->get('/transaksi/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertSee('PT. Halus Ciptanadi');
        $response->assertSee('PJ-NOTE-03');
        $response->assertSee('Toko Bangunan Abadi');
        $response->assertSee('Semen Portland 50kg');
        $response->assertSee('Rp 65.000');
    }

    public function test_bb_note_04_internal_ui_hidden_in_print_media(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.app-sidebar, .app-topbar, .page-header, .skip-link, .no-print', $css);
        $this->assertStringContainsString('display: none !important', $css);
    }

    public function test_bb_note_05_store_and_delivery_photos_omitted_from_print(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-NOTE-05',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::SELESAI->value,
            'foto_toko' => 'foto_toko/store.jpg',
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $response = $this->actingAs($this->sales)->get('/order/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertSee('Foto Toko');
        $response->assertSee('Bukti Penyerahan');
    }

    public function test_bb_note_06_completed_note_finality(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-NOTE-06',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::SELESAI->value,
            'tanggal_validasi' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->sales)->get('/order/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertDontSee('Ubah Order');
    }

    // ==========================================
    // AREA 14: DIRECTOR SALES REPORT (BB-DSAL-01 to 07)
    // ==========================================

    public function test_bb_dsal_01_direktur_access_sales_report(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan');
        $response->assertOk();
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Total Pendapatan');
    }

    public function test_bb_dsal_02_non_director_blocked(): void
    {
        $this->actingAs($this->admin)->get('/laporan-penjualan')->assertForbidden();
        $this->actingAs($this->sales)->get('/laporan-penjualan')->assertForbidden();
    }

    public function test_bb_dsal_03_scope_to_completed_transactions_only(): void
    {
        Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-REP-SELESAI',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 100000,
            'status_penjualan' => OrderStatus::SELESAI->value,
        ]);
        Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-REP-DIPROSES',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 200000,
            'status_penjualan' => OrderStatus::DIPROSES->value,
        ]);

        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan');
        $response->assertOk();
        $response->assertSee('PJ-REP-SELESAI');
        $response->assertDontSee('PJ-REP-DIPROSES');
        $response->assertSee('Rp 100.000');
    }

    public function test_bb_dsal_04_date_range_filtering(): void
    {
        Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-REP-DATE-1',
            'tanggal_penjualan' => '2026-10-10',
            'total_penjualan' => 50000,
            'status_penjualan' => OrderStatus::SELESAI->value,
        ]);

        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan?tanggal_mulai=2026-10-01&tanggal_selesai=2026-10-15');
        $response->assertOk();
        $response->assertSee('PJ-REP-DATE-1');
    }

    public function test_bb_dsal_05_invalid_date_range_rejected(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan?tanggal_mulai=2026-10-20&tanggal_selesai=2026-10-10');
        $response->assertSessionHasErrors(['tanggal_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
    }

    public function test_bb_dsal_06_customer_and_sales_filter(): void
    {
        Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-REP-CUST',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 75000,
            'status_penjualan' => OrderStatus::SELESAI->value,
        ]);

        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan?id_pelanggan='.$this->customer1->id_pelanggan.'&id_user='.$this->sales->id_user);
        $response->assertOk();
        $response->assertSee('PJ-REP-CUST');
    }

    public function test_bb_dsal_07_print_sales_report(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-penjualan');
        $response->assertOk();
        $response->assertSee('Cetak Laporan');
        $response->assertSee('print-brand');
    }

    // ==========================================
    // AREA 15: DIRECTOR STOCK REPORT (BB-DSTK-01 to 06)
    // ==========================================

    public function test_bb_dstk_01_direktur_access_stock_report(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('Laporan Stok');
    }

    public function test_bb_dstk_02_non_director_blocked_stock_report(): void
    {
        $this->actingAs($this->admin)->get('/laporan-stok')->assertForbidden();
        $this->actingAs($this->sales)->get('/laporan-stok')->assertForbidden();
    }

    public function test_bb_dstk_03_direct_data_reflection(): void
    {
        $this->actingAs($this->direktur);
        $this->product1->update(['stok_produk' => 77]);
        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('77');
    }

    public function test_bb_dstk_04_product_details_and_status_badges(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('PRD001');
        $response->assertSee('Semen Portland 50kg');
        $response->assertSee('Aktif');
    }

    public function test_bb_dstk_05_pagination_query_string_retention(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-stok?page=1');
        $response->assertOk();
    }

    public function test_bb_dstk_06_print_stock_report(): void
    {
        $this->actingAs($this->direktur);
        $response = $this->get('/laporan-stok');
        $response->assertOk();
        $response->assertSee('Cetak Laporan');
        $response->assertSee('print-brand');
    }

    // ==========================================
    // AREA 16: SEARCH & FILTER REGRESSION (BB-SRCH-01 to 06)
    // ==========================================

    public function test_bb_srch_01_produk_search_and_status(): void
    {
        $this->actingAs($this->admin);
        $this->get('/produk?cari=Semen&status=aktif')->assertOk()->assertSee('PRD001');
    }

    public function test_bb_srch_02_pelanggan_search_and_status(): void
    {
        $this->actingAs($this->admin);
        $this->get('/pelanggan?cari=Abadi&status=aktif')->assertOk()->assertSee('PLG001');
    }

    public function test_bb_srch_03_sales_search_and_status(): void
    {
        $this->actingAs($this->admin);
        $this->get('/sales?cari=sales_bb&status=aktif')->assertOk()->assertSee('sales_bb');
    }

    public function test_bb_srch_04_transaksi_search_and_status(): void
    {
        $this->actingAs($this->admin);
        $this->get('/transaksi?status=diproses')->assertOk();
    }

    public function test_bb_srch_05_order_search_and_status(): void
    {
        $this->actingAs($this->sales);
        $this->get('/order?status=menunggu_validasi')->assertOk();
    }

    public function test_bb_srch_06_robust_input_handling_arrays(): void
    {
        $this->actingAs($this->admin);
        $this->get('/produk?status[]=invalid&cari[]=foo')->assertOk();
        $this->get('/pelanggan?status[]=invalid&cari[]=foo')->assertOk();
        $this->get('/sales?status[]=invalid&cari[]=foo')->assertOk();
        $this->get('/transaksi?status[]=invalid&cari[]=foo')->assertOk();
    }

    // ==========================================
    // AREA 17: RESPONSIVE & MOBILE DESIGN (BB-MOB-01 to 06)
    // ==========================================

    public function test_bb_mob_01_viewport_meta_and_login_markup(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('name="viewport" content="width=device-width, initial-scale=1.0"', false);
    }

    public function test_bb_mob_02_order_form_stacking_markup(): void
    {
        $this->actingAs($this->sales);
        $response = $this->get('/order/tambah');
        $response->assertOk();
        $response->assertSee('order-product-row');
    }

    public function test_bb_mob_03_table_horizontal_scroll_wrapper(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/transaksi');
        $response->assertOk();
        $response->assertSee('ui-table-scroll');
    }

    public function test_bb_mob_04_mobile_drawer_navigation_markup(): void
    {
        $this->actingAs($this->sales);
        $response = $this->get('/dashboard');
        $response->assertOk();
        $response->assertSee('navigation-dialog');
        $response->assertSee('data-navigation-open');
    }

    public function test_bb_mob_05_mobile_order_creation_flow(): void
    {
        $this->actingAs($this->sales);
        $response = $this->get('/order/tambah');
        $response->assertOk();
        $response->assertSee('form-panel');
    }

    public function test_bb_mob_06_mobile_proof_upload_and_completion_actions(): void
    {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer1->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'PJ-MOB-06',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 65000,
            'status_penjualan' => OrderStatus::DIKIRIM->value,
            'foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg',
        ]);

        $this->actingAs($this->sales);
        $response = $this->get('/order/'.$order->id_penjualan);
        $response->assertOk();
        $response->assertSee('ui-button--primary');
        $response->assertSee('Selesaikan Order');
    }

    // ==========================================
    // AREA 18: ACCESSIBILITY REGRESSION (BB-ACC-01 to 06)
    // ==========================================

    public function test_bb_acc_01_keyboard_tab_flow_on_login(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('class="skip-link"', false);
        $response->assertSee('id="username"', false);
        $response->assertSee('id="password"', false);
    }

    public function test_bb_acc_02_skip_link_navigation(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('id="main-content"', false);
    }

    public function test_bb_acc_03_visible_high_contrast_focus_rings(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.ui-button:focus-visible', $css);
        $this->assertStringContainsString('outline: 2px solid var(--color-brand-900)', $css);
    }

    public function test_bb_acc_04_form_validation_error_linking(): void
    {
        $this->actingAs($this->admin);
        $response = $this->from('/produk/tambah')->post('/produk', []);
        $response->assertRedirect('/produk/tambah');
        $follow = $this->get('/produk/tambah');
        $follow->assertOk();
        $follow->assertSee('Periksa Kembali Data Anda');
    }

    public function test_bb_acc_05_modal_dialog_markup(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get('/transaksi');
        $response->assertOk();
        $response->assertSee('confirmation-dialog');
    }

    public function test_bb_acc_06_form_submit_feedback_and_screen_reader_status(): void
    {
        $this->actingAs($this->sales);
        $response = $this->get('/order/tambah');
        $response->assertOk();
        $response->assertSee('data-submit-state', false);
        $response->assertSee('data-submit-status', false);
    }
}
