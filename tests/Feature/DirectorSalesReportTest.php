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

class DirectorSalesReportTest extends TestCase
{
    private User $director;

    private User $admin;

    private User $salesA;

    private User $salesB;

    private Pelanggan $customerA;

    private Pelanggan $customerB;

    private Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'director_testing',
            'database.connections' => [
                'director_testing' => [
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
        $this->salesA = $this->createUser('sales', 'sales_a');
        $this->salesB = $this->createUser('sales', 'sales_b');

        $this->customerA = Pelanggan::create([
            'kode_pelanggan' => 'PLG-01',
            'nama_pelanggan' => 'I Wayan Sudiarta',
            'nama_toko' => 'Toko Sudiarta',
            'alamat_pelanggan' => 'Jl. Gatot Subroto No. 1',
            'no_wa' => '081234567891',
            'status_pelanggan' => 'aktif',
        ]);

        $this->customerB = Pelanggan::create([
            'kode_pelanggan' => 'PLG-02',
            'nama_pelanggan' => 'Made Arjana',
            'nama_toko' => 'Warung Arjana',
            'alamat_pelanggan' => 'Jl. Hayam Wuruk No. 2',
            'no_wa' => '081234567892',
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

    private function createOrder(
        Pelanggan $customer,
        User $sales,
        OrderStatus|string $status,
        string $number,
        string $date,
        float $total = 150000.00
    ): Penjualan {
        $statusValue = $status instanceof OrderStatus ? $status->value : $status;

        $order = Penjualan::create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_user' => $sales->id_user,
            'nomor_penjualan' => $number,
            'tanggal_penjualan' => $date,
            'total_penjualan' => $total,
            'status_penjualan' => $statusValue,
            'foto_toko' => 'foto_toko/sample.jpg',
            'foto_bukti_penyerahan' => $statusValue === OrderStatus::SELESAI->value ? 'bukti/sample.jpg' : null,
            'tanggal_validasi' => in_array($statusValue, ['diproses', 'dikirim', 'selesai'], true) ? $date : null,
            'tanggal_selesai' => $statusValue === OrderStatus::SELESAI->value ? $date : null,
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => $total,
            'subtotal' => $total,
        ]);

        return $order;
    }

    public function test_guest_cannot_access_director_sales_report(): void
    {
        $response = $this->get('/laporan-penjualan');
        $response->assertRedirect('/login');
    }

    public function test_sales_user_cannot_access_director_sales_report(): void
    {
        $this->actingAs($this->salesA);

        $response = $this->get('/laporan-penjualan');
        $response->assertForbidden();

        $order = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-SEL-01', '2026-10-15');
        $detailResponse = $this->get("/laporan-penjualan/{$order->id_penjualan}");
        $detailResponse->assertForbidden();
    }

    public function test_admin_user_cannot_access_director_sales_report(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/laporan-penjualan');
        $response->assertForbidden();

        $order = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-SEL-02', '2026-10-15');
        $detailResponse = $this->get("/laporan-penjualan/{$order->id_penjualan}");
        $detailResponse->assertForbidden();
    }

    public function test_director_can_access_sales_report_page(): void
    {
        $this->actingAs($this->director);

        $response = $this->get('/laporan-penjualan');
        $response->assertOk();
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Total Pendapatan');
        $response->assertSee('Total Transaksi Selesai');
        $response->assertSee('Filter Laporan');
        $response->assertSee('name="tanggal_mulai"', false);
        $response->assertSee('name="tanggal_selesai"', false);
        $response->assertSee('name="id_pelanggan"', false);
        $response->assertSee('name="id_user"', false);

        // Sidebar active link assertion
        $response->assertSee('href="http://localhost/laporan-penjualan" class="sidebar-item is-active"', false);
    }

    public function test_sales_report_dataset_strictly_includes_completed_sales_only(): void
    {
        $this->actingAs($this->director);

        $orderWaiting = $this->createOrder($this->customerA, $this->salesA, OrderStatus::MENUNGGU_VALIDASI, 'ORD-WAIT-99', '2026-10-10', 100000.00);
        $orderProcessing = $this->createOrder($this->customerA, $this->salesA, OrderStatus::DIPROSES, 'ORD-PROC-99', '2026-10-11', 200000.00);
        $orderDelivering = $this->createOrder($this->customerA, $this->salesA, OrderStatus::DIKIRIM, 'ORD-DELV-99', '2026-10-12', 300000.00);
        $orderCompleted = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-DONE-99', '2026-10-13', 500000.00);

        $response = $this->get('/laporan-penjualan');
        $response->assertOk();

        // Completed transaction must be visible
        $response->assertSee('ORD-DONE-99');
        $response->assertSee('Rp 500.000');

        // Uncompleted lifecycle orders must be excluded per BR-10
        $response->assertDontSee('ORD-WAIT-99');
        $response->assertDontSee('ORD-PROC-99');
        $response->assertDontSee('ORD-DELV-99');

        // Authoritative metrics reflect strictly the completed sale (500,000, not 1,100,000)
        $response->assertSee('Rp 500.000');
        $response->assertDontSee('Rp 1.100.000');
    }

    public function test_sales_report_is_strictly_read_only(): void
    {
        $this->actingAs($this->director);

        $order = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-RO-01', '2026-10-15');

        $response = $this->get('/laporan-penjualan');
        $response->assertOk();

        // No operational mutation forms
        $response->assertDontSee('action="http://localhost/transaksi/', false);
        $response->assertDontSee('action="http://localhost/order/', false);
        $response->assertDontSee('Validasi Transaksi');
        $response->assertDontSee('Kirim Pesanan');
        $response->assertDontSee('Selesaikan Order');
        $response->assertDontSee('Kirim Ulang WhatsApp');
    }

    public function test_sales_report_table_does_not_contain_ambiguous_footer_total(): void
    {
        $this->actingAs($this->director);

        $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-NOFOOT-01', '2026-10-15');

        $response = $this->get('/laporan-penjualan');
        $response->assertOk();

        // Asserts no <tfoot> tag in the table to prevent total ambiguity
        $response->assertDontSee('<tfoot>', false);
        $response->assertDontSee('</tfoot>', false);
    }

    public function test_filter_dropdown_options_include_only_actors_with_completed_sales(): void
    {
        $this->actingAs($this->director);

        // Customer A & Sales A have a completed order
        $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-OPT-01', '2026-10-15');

        // Customer B & Sales B have only a pending order (not completed)
        $this->createOrder($this->customerB, $this->salesB, OrderStatus::MENUNGGU_VALIDASI, 'ORD-OPT-02', '2026-10-15');

        $response = $this->get('/laporan-penjualan');
        $response->assertOk();

        // Customer A is included in options, Customer B is excluded
        $response->assertSee('value="'.$this->customerA->id_pelanggan.'"', false);
        $response->assertDontSee('value="'.$this->customerB->id_pelanggan.'"', false);

        // Sales A is included in options, Sales B is excluded
        $response->assertSee('value="'.$this->salesA->id_user.'"', false);
        $response->assertDontSee('value="'.$this->salesB->id_user.'"', false);
    }

    public function test_filter_by_date_range(): void
    {
        $this->actingAs($this->director);

        $orderEarly = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-DATE-01', '2026-10-01', 100000.00);
        $orderTarget = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-DATE-02', '2026-10-15', 250000.00);
        $orderLate = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-DATE-03', '2026-11-01', 300000.00);

        $response = $this->get('/laporan-penjualan?tanggal_mulai=2026-10-10&tanggal_selesai=2026-10-20');
        $response->assertOk();

        $response->assertSee('ORD-DATE-02');
        $response->assertDontSee('ORD-DATE-01');
        $response->assertDontSee('ORD-DATE-03');

        // Metric cards reflect exactly the filtered order (Rp 250.000, 1 transaction)
        $response->assertSee('Rp 250.000');
        $response->assertSee('1');
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->director);

        $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-INV-01', '2026-10-15', 200000.00);

        // tanggal_selesai is earlier than tanggal_mulai
        $response = $this->from('/laporan-penjualan')->get('/laporan-penjualan?tanggal_mulai=2026-10-20&tanggal_selesai=2026-10-10');

        $response->assertRedirect('/laporan-penjualan');
        $response->assertSessionHasErrors(['tanggal_selesai']);
    }

    public function test_filter_by_customer(): void
    {
        $this->actingAs($this->director);

        $orderA = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-CUST-01', '2026-10-15', 150000.00);
        $orderB = $this->createOrder($this->customerB, $this->salesA, OrderStatus::SELESAI, 'ORD-CUST-02', '2026-10-15', 350000.00);

        $response = $this->get("/laporan-penjualan?id_pelanggan={$this->customerA->id_pelanggan}");
        $response->assertOk();

        $response->assertSee('ORD-CUST-01');
        $response->assertDontSee('ORD-CUST-02');
        $response->assertSee('Rp 150.000');
    }

    public function test_filter_by_sales_representative(): void
    {
        $this->actingAs($this->director);

        $orderA = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-SLS-01', '2026-10-15', 180000.00);
        $orderB = $this->createOrder($this->customerA, $this->salesB, OrderStatus::SELESAI, 'ORD-SLS-02', '2026-10-15', 220000.00);

        $response = $this->get("/laporan-penjualan?id_user={$this->salesB->id_user}");
        $response->assertOk();

        $response->assertSee('ORD-SLS-02');
        $response->assertDontSee('ORD-SLS-01');
        $response->assertSee('Rp 220.000');
    }

    public function test_filter_combination_and_reset(): void
    {
        $this->actingAs($this->director);

        $order1 = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-COMB-01', '2026-10-15', 100000.00);
        $order2 = $this->createOrder($this->customerA, $this->salesB, OrderStatus::SELESAI, 'ORD-COMB-02', '2026-10-15', 200000.00);

        $response = $this->get("/laporan-penjualan?tanggal_mulai=2026-10-01&tanggal_selesai=2026-10-31&id_pelanggan={$this->customerA->id_pelanggan}&id_user={$this->salesA->id_user}");
        $response->assertOk();

        $response->assertSee('ORD-COMB-01');
        $response->assertDontSee('ORD-COMB-02');
        $response->assertSee('Atur Ulang');
        $response->assertSee('href="http://localhost/laporan-penjualan"', false);
    }

    public function test_director_can_view_read_only_transaction_detail(): void
    {
        $this->actingAs($this->director);

        $order = $this->createOrder($this->customerA, $this->salesA, OrderStatus::SELESAI, 'ORD-VIEW-01', '2026-10-15', 150000.00);

        $response = $this->get("/laporan-penjualan/{$order->id_penjualan}");
        $response->assertOk();

        // Asserts page title and metadata
        $response->assertSee('Detail Transaksi Penjualan');
        $response->assertSee('ORD-VIEW-01');
        $response->assertSee('I Wayan Sudiarta');
        $response->assertSee('Beras Bali 10kg');

        // Asserts read-only back button points to /laporan-penjualan
        $response->assertSee('href="http://localhost/laporan-penjualan"', false);
        $response->assertSee('Kembali ke Laporan');

        // Asserts Cetak Nota is available for completed order
        $response->assertSee('Cetak Nota');

        // Asserts no mutation actions
        $response->assertDontSee('Validasi Transaksi');
        $response->assertDontSee('Selesaikan Order');
    }

    public function test_director_cannot_view_uncompleted_transaction_detail(): void
    {
        $this->actingAs($this->director);

        $uncompletedOrder = $this->createOrder($this->customerA, $this->salesA, OrderStatus::DIKIRIM, 'ORD-INCOMP-01', '2026-10-15');

        $response = $this->get("/laporan-penjualan/{$uncompletedOrder->id_penjualan}");
        $response->assertNotFound();
    }

    public function test_pagination_preserves_active_filter_query_strings(): void
    {
        $this->actingAs($this->director);

        // Seed 16 completed orders to trigger pagination (15 per page)
        for ($i = 1; $i <= 16; $i++) {
            $this->createOrder(
                $this->customerA,
                $this->salesA,
                OrderStatus::SELESAI,
                sprintf('ORD-PAG-%02d', $i),
                '2026-10-15',
                10000.00
            );
        }

        $response = $this->get('/laporan-penjualan?tanggal_mulai=2026-10-01');
        $response->assertOk();

        // Pagination links should retain tanggal_mulai query parameter
        $response->assertSee('tanggal_mulai=2026-10-01', false);
    }
}
