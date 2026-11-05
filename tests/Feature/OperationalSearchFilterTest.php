<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
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

class OperationalSearchFilterTest extends TestCase
{
    private User $admin;

    private User $sales;

    private User $otherSales;

    private Pelanggan $customerActive;

    private Pelanggan $customerInactive;

    private Produk $productActive;

    private Produk $productInactive;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'search_filter_testing',
            'database.connections' => [
                'search_filter_testing' => [
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

        $this->admin = $this->createUser('admin', 'admin_qa');
        $this->sales = $this->createUser('sales', 'sales_qa');
        $this->otherSales = $this->createUser('sales', 'sales_other');

        $this->customerActive = Pelanggan::create([
            'kode_pelanggan' => 'PLG-01',
            'nama_pelanggan' => 'Wayan Sudiarta',
            'nama_toko' => 'Toko Sudiarta',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567891',
            'status_pelanggan' => 'aktif',
        ]);

        $this->customerInactive = Pelanggan::create([
            'kode_pelanggan' => 'PLG-02',
            'nama_pelanggan' => 'Made Arjana',
            'nama_toko' => 'Warung Arjana',
            'alamat_pelanggan' => 'Kuta',
            'no_wa' => '081234567892',
            'status_pelanggan' => 'nonaktif',
        ]);

        $this->productActive = Produk::create([
            'kode_produk' => 'PRD-01',
            'nama_produk' => 'Beras Bali 10kg',
            'kategori_produk' => 'Sembako',
            'harga_produk' => 150000.00,
            'stok_produk' => 100,
            'satuan_produk' => 'sak',
            'status_produk' => 'aktif',
        ]);

        $this->productInactive = Produk::create([
            'kode_produk' => 'PRD-02',
            'nama_produk' => 'Beras Merah 5kg',
            'kategori_produk' => 'Sembako',
            'harga_produk' => 95000.00,
            'stok_produk' => 0,
            'satuan_produk' => 'sak',
            'status_produk' => 'nonaktif',
        ]);
    }

    private function createUser(string $role, string $username, string $status = 'aktif'): User
    {
        return User::create([
            'nama_user' => ucfirst($role).' '.$username,
            'username' => $username,
            'password' => Hash::make('password123'),
            'role' => $role,
            'no_telp' => '081234567800',
            'alamat' => 'Denpasar',
            'status_user' => $status,
        ]);
    }

    private function createOrder(User $user, Pelanggan $customer, OrderStatus $status, string $nomor = 'PJ-20261001-0001'): Penjualan
    {
        return Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $user->id_user,
            'nomor_penjualan' => $nomor,
            'tanggal_penjualan' => '2026-10-01',
            'total_penjualan' => 150000.00,
            'status_penjualan' => $status->value,
        ]);
    }

    public function test_transaksi_invalid_status_query_is_ignored_and_renders_canonical_options_only(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'PJ-001');

        $response = $this->actingAs($this->admin)->get(route('transaksi.index', ['status' => 'invalid_status_xyz']));

        $response->assertOk();
        $response->assertSee('PJ-001');
        $response->assertDontSee('value="invalid_status_xyz"', false);
        $response->assertSee('value="menunggu_validasi"', false);
        $response->assertSee('value="diproses"', false);
        $response->assertSee('value="dikirim"', false);
        $response->assertSee('value="selesai"', false);
    }

    public function test_transaksi_array_status_query_is_safely_ignored(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'PJ-001');

        $response = $this->actingAs($this->admin)->get('/transaksi?status[]=menunggu_validasi');

        $response->assertOk();
        $response->assertSee('PJ-001');
    }

    public function test_order_invalid_status_query_is_ignored_and_renders_canonical_options_only(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'ORD-001');

        $response = $this->actingAs($this->sales)->get(route('order.index', ['status' => 'unknown_status']));

        $response->assertOk();
        $response->assertSee('ORD-001');
        $response->assertDontSee('value="unknown_status"', false);
        $response->assertSee('value="menunggu_validasi"', false);
    }

    public function test_order_array_status_query_is_safely_ignored(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'ORD-001');

        $response = $this->actingAs($this->sales)->get('/order?status[]=diproses');

        $response->assertOk();
        $response->assertSee('ORD-001');
    }

    public function test_master_data_invalid_status_query_is_ignored(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('produk.index', ['status' => 'foo']))
            ->assertOk()
            ->assertSee('PRD-01')
            ->assertSee('PRD-02');

        $this->get(route('pelanggan.index', ['status' => 'bar']))
            ->assertOk()
            ->assertSee('PLG-01')
            ->assertSee('PLG-02');

        $this->get(route('sales.index', ['status' => 'baz']))
            ->assertOk()
            ->assertSee('sales_qa');
    }

    public function test_master_data_array_status_query_is_safely_ignored(): void
    {
        $this->actingAs($this->admin);

        $this->get('/produk?status[]=aktif')->assertOk()->assertSee('PRD-01');
        $this->get('/pelanggan?status[]=aktif')->assertOk()->assertSee('PLG-01');
        $this->get('/sales?status[]=aktif')->assertOk()->assertSee('sales_qa');
    }

    public function test_array_search_query_is_safely_ignored(): void
    {
        $this->actingAs($this->admin);

        $this->get('/produk?cari[]=query')->assertOk()->assertSee('PRD-01');
        $this->get('/pelanggan?cari[]=query')->assertOk()->assertSee('PLG-01');
    }

    public function test_produk_search_returns_matching_records(): void
    {
        $response = $this->actingAs($this->admin)->get(route('produk.index', ['cari' => 'Bali']));

        $response->assertOk();
        $response->assertSee('Beras Bali 10kg');
        $response->assertDontSee('Beras Merah 5kg');
    }

    public function test_produk_status_filter_filters_active_and_inactive(): void
    {
        $activeResponse = $this->actingAs($this->admin)->get(route('produk.index', ['status' => 'aktif']));
        $activeResponse->assertOk();
        $activeResponse->assertSee('Beras Bali 10kg');
        $activeResponse->assertDontSee('Beras Merah 5kg');

        $inactiveResponse = $this->actingAs($this->admin)->get(route('produk.index', ['status' => 'nonaktif']));
        $inactiveResponse->assertOk();
        $inactiveResponse->assertSee('Beras Merah 5kg');
        $inactiveResponse->assertDontSee('Beras Bali 10kg');
    }

    public function test_produk_search_and_status_combined_avoids_false_positives(): void
    {
        // Both products have 'Beras' in name, but one is aktif and one is nonaktif
        $response = $this->actingAs($this->admin)->get(route('produk.index', [
            'cari' => 'Beras',
            'status' => 'aktif',
        ]));

        $response->assertOk();
        $response->assertSee('Beras Bali 10kg');
        $response->assertDontSee('Beras Merah 5kg');
    }

    public function test_produk_pagination_retains_search_and_status_query(): void
    {
        for ($i = 3; $i <= 15; $i++) {
            Produk::create([
                'kode_produk' => 'PRD-'.sprintf('%02d', $i),
                'nama_produk' => 'Beras Organik '.$i,
                'kategori_produk' => 'Sembako',
                'harga_produk' => 120000.00,
                'stok_produk' => 10,
                'satuan_produk' => 'sak',
                'status_produk' => 'aktif',
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('produk.index', [
            'cari' => 'Beras',
            'status' => 'aktif',
        ]));

        $response->assertOk();
        $response->assertSee('page=2', false);
        $response->assertSee('cari=Beras', false);
        $response->assertSee('status=aktif', false);
    }

    public function test_produk_empty_state_and_reset_link(): void
    {
        $response = $this->actingAs($this->admin)->get(route('produk.index', ['cari' => 'xyz_tidak_ada']));

        $response->assertOk();
        $response->assertSee('Tidak Ada Hasil Pencarian');
        $response->assertSee('Atur Ulang Pencarian');
        $response->assertSee('href="'.route('produk.index').'"', false);
    }

    public function test_pelanggan_search_and_status_filtering(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pelanggan.index', [
            'cari' => 'Sudiarta',
            'status' => 'aktif',
        ]));

        $response->assertOk();
        $response->assertSee('Wayan Sudiarta');
        $response->assertDontSee('Made Arjana');

        // Sales user can search and filter without viewing edit or deactivate buttons
        $salesResponse = $this->actingAs($this->sales)->get(route('pelanggan.index', [
            'status' => 'aktif',
        ]));

        $salesResponse->assertOk();
        $salesResponse->assertSee('Wayan Sudiarta');
        $salesResponse->assertDontSee(route('pelanggan.edit', $this->customerActive));
        $salesResponse->assertDontSee(route('pelanggan.nonaktifkan', $this->customerActive));
    }

    public function test_sales_search_and_status_filtering(): void
    {
        $inactiveSales = $this->createUser('sales', 'sales_pensiun', 'nonaktif');

        $activeResponse = $this->actingAs($this->admin)->get(route('sales.index', ['status' => 'aktif']));
        $activeResponse->assertOk();
        $activeResponse->assertSee('sales_qa');
        $activeResponse->assertDontSee('sales_pensiun');

        $inactiveResponse = $this->actingAs($this->admin)->get(route('sales.index', ['status' => 'nonaktif']));
        $inactiveResponse->assertOk();
        $inactiveResponse->assertSee('sales_pensiun');
        $inactiveResponse->assertDontSee('sales_qa');
    }

    public function test_sales_order_list_supports_canonical_status_filtering(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'ORD-MV');
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::DIPROSES, 'ORD-DP');
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::SELESAI, 'ORD-SL');

        $response = $this->actingAs($this->sales)->get(route('order.index', ['status' => OrderStatus::DIPROSES->value]));

        $response->assertOk();
        $response->assertSee('ORD-DP');
        $response->assertDontSee('ORD-MV');
        $response->assertDontSee('ORD-SL');
    }

    public function test_admin_transaksi_search_and_canonical_status_filtering(): void
    {
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::MENUNGGU_VALIDASI, 'TRX-MV');
        $this->createOrder($this->sales, $this->customerActive, OrderStatus::DIKIRIM, 'TRX-DK');

        $response = $this->actingAs($this->admin)->get(route('transaksi.index', [
            'cari' => 'TRX',
            'status' => OrderStatus::DIKIRIM->value,
        ]));

        $response->assertOk();
        $response->assertSee('TRX-DK');
        $response->assertDontSee('TRX-MV');
    }

    public function test_laporan_stok_pagination_retains_query_string(): void
    {
        $director = $this->createUser('direktur', 'direktur_qa');

        for ($i = 3; $i <= 20; $i++) {
            Produk::create([
                'kode_produk' => 'PRD-'.sprintf('%02d', $i),
                'nama_produk' => 'Produk Stok '.$i,
                'kategori_produk' => 'Kategori',
                'harga_produk' => 50000.00,
                'stok_produk' => 20,
                'satuan_produk' => 'pcs',
                'status_produk' => 'aktif',
            ]);
        }

        $response = $this->actingAs($director)->get(route('laporan.stok', ['extra' => 'keep']));

        $response->assertOk();
        $response->assertSee('page=2', false);
        $response->assertSee('extra=keep', false);
    }

    public function test_reset_button_clears_all_filters(): void
    {
        // When filtered, reset button links to clean index route
        $response = $this->actingAs($this->admin)->get(route('produk.index', ['status' => 'aktif']));
        $response->assertOk();
        $response->assertSee('href="'.route('produk.index').'"', false);
        $response->assertSee('Atur Ulang');
    }
}
