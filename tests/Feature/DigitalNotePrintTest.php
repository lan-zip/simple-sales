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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalNotePrintTest extends TestCase
{
    private User $admin;

    private User $salesA;

    private User $salesB;

    private Produk $product;

    private Pelanggan $customer;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'note_testing',
            'database.connections' => [
                'note_testing' => [
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

        $this->admin = User::create([
            'nama_user' => 'Admin Utama',
            'username' => 'admin_note',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_telp' => '081234567890',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);

        $this->salesA = User::create([
            'nama_user' => 'Budi Santoso',
            'username' => 'sales_budi',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'no_telp' => '081234567891',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);

        $this->salesB = User::create([
            'nama_user' => 'Dewi Lestari',
            'username' => 'sales_dewi',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'no_telp' => '081234567892',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);

        $this->product = Produk::create([
            'kode_produk' => 'PRD-SEMEN',
            'nama_produk' => 'Semen Portland 50kg',
            'kategori_produk' => 'Material',
            'harga_produk' => 65000,
            'stok_produk' => 100,
            'satuan_produk' => 'sak',
            'status_produk' => 'aktif',
        ]);

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-001',
            'nama_pelanggan' => 'I Wayan Sudra',
            'nama_toko' => 'Toko Bangunan Sudra',
            'alamat_pelanggan' => 'Jl. Bypass Ngurah Rai No. 88',
            'no_wa' => '081298765432',
            'status_pelanggan' => 'aktif',
        ]);
    }

    private function createOrder(
        string|OrderStatus $status,
        ?User $user = null,
        ?string $storePhoto = null,
        ?string $deliveryProof = null
    ): Penjualan {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer->getKey(),
            'id_user' => ($user ?? $this->salesA)->getKey(),
            'nomor_penjualan' => 'PJ-20261104-001',
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 130000,
            'status_penjualan' => $status instanceof OrderStatus ? $status->value : $status,
            'tanggal_validasi' => in_array($status, [OrderStatus::DIPROSES, OrderStatus::DIKIRIM, OrderStatus::SELESAI, 'diproses', 'dikirim', 'selesai']) ? now()->toDateString() : null,
            'tanggal_selesai' => in_array($status, [OrderStatus::SELESAI, 'selesai']) ? now()->toDateString() : null,
            'foto_toko' => $storePhoto,
            'foto_bukti_penyerahan' => $deliveryProof,
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->getKey(),
            'id_produk' => $this->product->getKey(),
            'jumlah_produk' => 2,
            'harga_satuan' => 65000,
            'subtotal' => 130000,
        ]);

        return $order;
    }

    public function test_print_button_is_not_displayed_when_status_is_menunggu_validasi_for_sales_and_admin(): void
    {
        $order = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);

        // Sales View
        $this->actingAs($this->salesA);
        $responseSales = $this->get(route('order.show', $order));
        $responseSales->assertOk()
            ->assertDontSee('Cetak Nota')
            ->assertSee('Ubah Order');

        // Admin View
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('transaksi.show', $order));
        $responseAdmin->assertOk()
            ->assertDontSee('Cetak Nota')
            ->assertSee('Validasi Transaksi');
    }

    public function test_print_button_is_displayed_when_status_is_diproses_for_sales_and_admin(): void
    {
        $order = $this->createOrder(OrderStatus::DIPROSES);

        // Sales View
        $this->actingAs($this->salesA);
        $responseSales = $this->get(route('order.show', $order));
        $responseSales->assertOk()
            ->assertSee('Cetak Nota')
            ->assertDontSee('Ubah Order');

        // Admin View
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('transaksi.show', $order));
        $responseAdmin->assertOk()
            ->assertSee('Cetak Nota')
            ->assertSee('Kirim Pesanan');
    }

    public function test_print_button_is_displayed_when_status_is_dikirim_for_sales_and_admin(): void
    {
        $order = $this->createOrder(OrderStatus::DIKIRIM);

        // Sales View
        $this->actingAs($this->salesA);
        $responseSales = $this->get(route('order.show', $order));
        $responseSales->assertOk()
            ->assertSee('Cetak Nota');

        // Admin View
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('transaksi.show', $order));
        $responseAdmin->assertOk()
            ->assertSee('Cetak Nota');
    }

    public function test_print_button_is_displayed_when_status_is_selesai_for_sales_and_admin(): void
    {
        $order = $this->createOrder(OrderStatus::SELESAI, null, 'foto_toko/dummy.jpg', 'bukti_penyerahan/dummy.jpg');

        // Sales View
        $this->actingAs($this->salesA);
        $responseSales = $this->get(route('order.show', $order));
        $responseSales->assertOk()
            ->assertSee('Cetak Nota');

        // Admin View
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('transaksi.show', $order));
        $responseAdmin->assertOk()
            ->assertSee('Cetak Nota');
    }

    public function test_all_required_note_fields_are_present_in_detail_view(): void
    {
        $order = $this->createOrder(OrderStatus::DIPROSES);

        // Check Sales View
        $this->actingAs($this->salesA);
        $response = $this->get(route('order.show', $order));

        $response->assertOk()
            ->assertSee('PT. Halus Ciptanadi')
            ->assertSee('Pencatatan Penjualan · Nota Order')
            ->assertSee('PJ-20261104-001')
            ->assertSee('I Wayan Sudra')
            ->assertSee('Toko Bangunan Sudra')
            ->assertSee('081298765432')
            ->assertSee('Jl. Bypass Ngurah Rai No. 88')
            ->assertSee('Budi Santoso')
            ->assertSee('Semen Portland 50kg')
            ->assertSee('PRD-SEMEN')
            ->assertSee('2 sak')
            ->assertSee('65.000')
            ->assertSee('130.000')
            ->assertSee('Total Penjualan');

        // Check Admin View
        $this->actingAs($this->admin);
        $responseAdmin = $this->get(route('transaksi.show', $order));

        $responseAdmin->assertOk()
            ->assertSee('PT. Halus Ciptanadi')
            ->assertSee('PJ-20261104-001')
            ->assertSee('I Wayan Sudra')
            ->assertSee('Budi Santoso')
            ->assertSee('130.000');
    }

    public function test_sales_representative_is_displayed_on_both_sales_and_admin_views(): void
    {
        $order = $this->createOrder(OrderStatus::DIPROSES, $this->salesA);

        // Sales View
        $this->actingAs($this->salesA);
        $this->get(route('order.show', $order))
            ->assertOk()
            ->assertSee('Sales')
            ->assertSee('Budi Santoso');

        // Admin View
        $this->actingAs($this->admin);
        $this->get(route('transaksi.show', $order))
            ->assertOk()
            ->assertSee('Sales')
            ->assertSee('Budi Santoso');
    }

    public function test_store_photo_and_delivery_proof_have_no_print_class(): void
    {
        $order = $this->createOrder(OrderStatus::SELESAI, null, 'foto_toko/test.jpg', 'bukti_penyerahan/test.jpg');

        $this->actingAs($this->salesA);
        $content = $this->get(route('order.show', $order))->getContent();

        // Foto Toko card must have no-print
        $this->assertMatchesRegularExpression('/<section[^>]*class="[^"]*ui-card[^"]*no-print[^"]*"[^>]*>[\s\S]*?Foto Toko/i', $content);

        // Bukti Penyerahan card must have no-print
        $this->assertMatchesRegularExpression('/<section[^>]*class="[^"]*ui-card[^"]*no-print[^"]*"[^>]*>[\s\S]*?Bukti Penyerahan/i', $content);
    }

    public function test_page_header_and_upload_form_have_no_print_class(): void
    {
        $order = $this->createOrder(OrderStatus::DIKIRIM);

        $this->actingAs($this->salesA);
        $content = $this->get(route('order.show', $order))->getContent();

        // Page header has no-print
        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*page-header[^"]*no-print[^"]*"[^>]*>/i', $content);

        // Upload form container has no-print
        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*no-print[^"]*"[^>]*>[\s\S]*?Unggah Bukti Penyerahan/i', $content);
    }

    public function test_completed_order_cannot_be_edited_via_form_or_endpoint(): void
    {
        $order = $this->createOrder(OrderStatus::SELESAI);

        $this->actingAs($this->salesA);

        // GET edit form should be forbidden
        $this->get(route('order.edit', $order))->assertForbidden();

        // PUT update endpoint should be forbidden
        $this->put(route('order.update', $order), [
            'id_pelanggan' => $this->customer->getKey(),
            'produk' => [['id_produk' => $this->product->getKey(), 'jumlah' => 1]],
        ])->assertForbidden();
    }

    public function test_sales_cannot_view_or_print_another_sales_order(): void
    {
        $orderA = $this->createOrder(OrderStatus::DIPROSES, $this->salesA);

        // Sales B cannot view or print Sales A's order
        $this->actingAs($this->salesB);
        $this->get(route('order.show', $orderA))->assertForbidden();
    }
}
