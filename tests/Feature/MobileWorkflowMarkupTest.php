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

class MobileWorkflowMarkupTest extends TestCase
{
    private User $sales;

    private Pelanggan $customer;

    private Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mobile_testing',
            'database.connections' => [
                'mobile_testing' => [
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

        $this->sales = User::create([
            'nama_user' => 'Sales Tester',
            'username' => 'sales_tester',
            'password' => Hash::make('password123'),
            'role' => 'sales',
            'no_telp' => '081234567891',
            'alamat' => 'Denpasar Selatan',
            'status_user' => 'aktif',
        ]);

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'PLG-M01',
            'nama_pelanggan' => 'Ketut Sujana',
            'nama_toko' => 'Toko Sujana',
            'alamat_pelanggan' => 'Jl. Teuku Umar No. 10',
            'no_wa' => '081234567892',
            'status_pelanggan' => 'aktif',
        ]);

        $this->product = Produk::create([
            'kode_produk' => 'PRD-M01',
            'nama_produk' => 'Kopi Bali 250g',
            'kategori_produk' => 'Minuman',
            'harga_produk' => 30000.00,
            'stok_produk' => 40,
            'satuan_produk' => 'bungkus',
            'status_produk' => 'aktif',
        ]);
    }

    public function test_viewport_meta_tag_present_on_all_workflow_pages(): void
    {
        // 1. Guest login page
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0">', false);
        $response->assertSee('<meta name="theme-color" content="#6F0D0D">', false);

        // 2. Authenticated pages in Sales workflow
        $this->actingAs($this->sales);

        $order = Penjualan::create([
            'id_pelanggan' => $this->customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-VP-01',
            'tanggal_penjualan' => '2026-11-05',
            'total_penjualan' => 60000.00,
            'status_penjualan' => 'menunggu_validasi',
            'foto_toko' => 'foto_toko/test.jpg',
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product->id_produk,
            'jumlah_produk' => 2,
            'harga_satuan' => 30000.00,
            'subtotal' => 60000.00,
        ]);

        $routes = [
            '/dashboard',
            '/pelanggan',
            '/pelanggan/tambah',
            '/order',
            '/order/tambah',
            "/order/{$order->id_penjualan}",
            "/order/{$order->id_penjualan}/edit",
        ];

        foreach ($routes as $route) {
            $resp = $this->get($route);
            $resp->assertOk();
            $resp->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0">', false);
            $resp->assertSee('<meta name="theme-color" content="#6F0D0D">', false);
        }
    }

    public function test_mobile_navigation_drawer_markup_in_app_layout(): void
    {
        $this->actingAs($this->sales);

        $response = $this->get('/order');
        $response->assertOk();

        // Native dialog element for mobile drawer
        $response->assertSee('<dialog id="navigation-dialog" class="navigation-dialog" aria-label="Menu navigasi"></dialog>', false);

        // Topbar toggle button
        $response->assertSee('data-navigation-open', false);
        $response->assertSee('aria-controls="navigation-dialog"', false);
        $response->assertSee('aria-label="Buka menu navigasi"', false);

        // Sidebar close button
        $response->assertSee('data-navigation-close', false);
        $response->assertSee('aria-label="Tutup menu navigasi"', false);

        // Navigation links and logout form
        $response->assertSee('Order Penjualan');
        $response->assertSee('Data Pelanggan');
        $response->assertSee('sidebar-logout', false);
    }

    public function test_customer_views_mobile_markup_contracts(): void
    {
        $this->actingAs($this->sales);

        // List view: scrollable table region
        $listResponse = $this->get('/pelanggan');
        $listResponse->assertOk();
        $listResponse->assertSee('class="ui-table-scroll"', false);
        $listResponse->assertSee('role="region"', false);

        // Create view: telephone keypad attributes and responsive form grid
        $createResponse = $this->get('/pelanggan/tambah');
        $createResponse->assertOk();
        $createResponse->assertSee('form-grid', false);
        $createResponse->assertSee('type="tel"', false);
        $createResponse->assertSee('autocomplete="tel"', false);
    }

    public function test_order_creation_mobile_markup_and_camera_input(): void
    {
        $this->actingAs($this->sales);

        $response = $this->get('/order/tambah');
        $response->assertOk();

        // Foto toko requires camera/image upload and is mandatory on create
        $response->assertSee('name="foto_toko"', false);
        $response->assertSee('type="file"', false);
        $response->assertSee('accept="image/jpeg,image/png,image/jpg,image/webp"', false);
        $response->assertSee('required', false);

        // Numeric keypad for quantity
        $response->assertSee('inputmode="numeric"', false);

        // Dynamic product row buttons and live total container
        $response->assertSee('data-add-product', false);
        $response->assertSee('data-remove-product', false);
        $response->assertSee('data-order-total', false);
    }

    public function test_order_edit_mobile_markup_and_optional_photo(): void
    {
        $this->actingAs($this->sales);

        $order = Penjualan::create([
            'id_pelanggan' => $this->customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-ED-01',
            'tanggal_penjualan' => '2026-11-05',
            'total_penjualan' => 30000.00,
            'status_penjualan' => 'menunggu_validasi',
            'foto_toko' => 'foto_toko/existing_sample.jpg',
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 30000.00,
            'subtotal' => 30000.00,
        ]);

        $response = $this->get("/order/{$order->id_penjualan}/edit");
        $response->assertOk();

        // Existing photo preview and full size link
        $response->assertSee('alt="Foto Toko Saat Ini"', false);
        $response->assertSee('Lihat Foto Ukuran Penuh');

        // Foto toko input accepts valid image MIME types but is optional on edit
        $response->assertSee('name="foto_toko"', false);
        $response->assertSee('accept="image/jpeg,image/png,image/jpg,image/webp"', false);

        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $fileInputs = $xpath->query('//input[@name="foto_toko" and @type="file"]');
        $this->assertSame(1, $fileInputs->length);
        $this->assertFalse($fileInputs->item(0)->hasAttribute('required'));

        // Numeric keypad retained on edit
        $response->assertSee('inputmode="numeric"', false);
    }

    public function test_delivery_proof_upload_and_order_completion_mobile_markup(): void
    {
        $this->actingAs($this->sales);

        $order = Penjualan::create([
            'id_pelanggan' => $this->customer->id_pelanggan,
            'id_user' => $this->sales->id_user,
            'nomor_penjualan' => 'ORD-DLV-01',
            'tanggal_penjualan' => '2026-11-05',
            'tanggal_validasi' => '2026-11-05',
            'total_penjualan' => 30000.00,
            'status_penjualan' => 'dikirim',
            'foto_toko' => 'foto_toko/sample.jpg',
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->id_penjualan,
            'id_produk' => $this->product->id_produk,
            'jumlah_produk' => 1,
            'harga_satuan' => 30000.00,
            'subtotal' => 30000.00,
        ]);

        // State A: dikirim WITHOUT delivery proof uploaded
        $responseNoProof = $this->get("/order/{$order->id_penjualan}");
        $responseNoProof->assertOk();
        $responseNoProof->assertSee('Unggah Bukti Penyerahan');
        $responseNoProof->assertSee('name="foto_bukti_penyerahan"', false);
        $responseNoProof->assertSee('accept="image/jpeg,image/png,image/webp"', false);
        $responseNoProof->assertDontSee('Selesaikan Order');

        // State B: dikirim WITH delivery proof uploaded
        $order->update(['foto_bukti_penyerahan' => 'bukti_penyerahan/proof.jpg']);

        $responseWithProof = $this->get("/order/{$order->id_penjualan}");
        $responseWithProof->assertOk();
        $responseWithProof->assertSee('Selesaikan Order');
        $responseWithProof->assertSee('data-confirm-message', false);
        $responseWithProof->assertSee('data-confirm-label="Selesaikan Order"', false);

        // Layout confirmation dialog
        $responseWithProof->assertSee('<dialog id="confirmation-dialog"', false);
        $responseWithProof->assertSee('data-confirmation-message', false);
        $responseWithProof->assertSee('data-dialog-close', false);
        $responseWithProof->assertSee('data-confirmation-accept', false);

        // Responsive proof image render
        $responseWithProof->assertSee('alt="Foto Bukti Penyerahan ORD-DLV-01"', false);
        $responseWithProof->assertSee('max-w-full', false);
    }

    public function test_login_page_mobile_prevent_zoom_css_rule(): void
    {
        $response = $this->get('/login');
        $response->assertOk();

        // Confirms the iOS Safari auto-zoom prevention rule is in place for <= 560px
        $response->assertSee('@media (max-width: 560px)', false);
        $response->assertSee('.hc-auth-input { font-size: 16px; }', false);
    }
}
