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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
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
            'database.default' => 'lifecycle_testing',
            'database.connections' => [
                'lifecycle_testing' => [
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

        $this->admin = $this->createUser('admin');
        $this->salesA = $this->createUser('sales');
        $this->salesB = $this->createUser('sales');

        $this->product = Produk::create([
            'kode_produk' => 'PRD-01',
            'nama_produk' => 'Produk Tes',
            'kategori_produk' => 'Kategori Tes',
            'harga_produk' => 10000,
            'stok_produk' => 25,
            'satuan_produk' => 'pcs',
            'status_produk' => 'aktif',
        ]);

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-01',
            'nama_pelanggan' => 'Customer Tes',
            'nama_toko' => 'Toko Tes',
            'alamat_pelanggan' => 'Alamat Tes',
            'no_wa' => '081234567890',
            'status_pelanggan' => 'aktif',
        ]);
    }

    private function createUser(string $role): User
    {
        return User::create([
            'nama_user' => ucfirst($role).' '.uniqid(),
            'username' => $role.'_'.uniqid(),
            'password' => bcrypt('password'),
            'role' => $role,
            'no_telp' => '081234567890',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);
    }

    private function createOrder(
        string|OrderStatus $status = OrderStatus::MENUNGGU_VALIDASI,
        ?User $user = null,
        ?string $proof = null
    ): Penjualan {
        $order = Penjualan::create([
            'id_pelanggan' => $this->customer->getKey(),
            'id_user' => ($user ?? $this->salesA)->getKey(),
            'nomor_penjualan' => 'PJ-TEST-'.uniqid(),
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 20000,
            'status_penjualan' => $status instanceof OrderStatus ? $status->value : $status,
            'foto_bukti_penyerahan' => $proof,
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->getKey(),
            'id_produk' => $this->product->getKey(),
            'jumlah_produk' => 2,
            'harga_satuan' => 10000,
            'subtotal' => 20000,
        ]);

        return $order;
    }

    public function test_complete_valid_lifecycle_transitions_end_to_end(): void
    {
        // 1. Sales creates order -> starts at menunggu_validasi
        $this->actingAs($this->salesA);
        $orderData = [
            'id_pelanggan' => $this->customer->getKey(),
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 2],
            ],
        ];

        $response = $this->post(route('order.store'), $orderData);
        $response->assertRedirect(route('order.index'));

        $order = Penjualan::firstOrFail();
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order->status_penjualan);
        $this->assertSame(25, $this->product->fresh()->stok_produk); // Stock not decremented yet

        // 2. Admin validates -> diproses (stock decremented once, tanggal_validasi set)
        $this->actingAs($this->admin);
        $validateResponse = $this->patch(route('transaksi.validasi', $order));
        $validateResponse->assertRedirect(route('transaksi.show', $order));

        $order = $order->fresh();
        $this->assertSame(OrderStatus::DIPROSES, $order->status_penjualan);
        $this->assertSame(23, $this->product->fresh()->stok_produk);
        $this->assertNotNull($order->tanggal_validasi);

        // 3. Admin moves diproses -> dikirim (stock remains 23, no extra decrement)
        $shipResponse = $this->patch(route('transaksi.kirim', $order));
        $shipResponse->assertRedirect(route('transaksi.show', $order));

        $order = $order->fresh();
        $this->assertSame(OrderStatus::DIKIRIM, $order->status_penjualan);
        $this->assertSame(23, $this->product->fresh()->stok_produk);

        // 4. Sales uploads delivery proof
        $this->actingAs($this->salesA);
        $file = UploadedFile::fake()->image('bukti_penyerahan.jpg', 600, 600);
        $uploadResponse = $this->post(route('order.uploadBukti', $order), [
            'foto_bukti_penyerahan' => $file,
        ]);
        $uploadResponse->assertRedirect(route('order.show', $order));

        $order = $order->fresh();
        $this->assertNotNull($order->foto_bukti_penyerahan);
        Storage::disk('public')->assertExists($order->foto_bukti_penyerahan);

        // 5. Sales completes order -> selesai (tanggal_selesai set)
        $completeResponse = $this->patch(route('order.selesai', $order));
        $completeResponse->assertRedirect(route('order.show', $order));

        $order = $order->fresh();
        $this->assertSame(OrderStatus::SELESAI, $order->status_penjualan);
        $this->assertNotNull($order->tanggal_selesai);
        $this->assertSame(23, $this->product->fresh()->stok_produk); // Stock still untouched
    }

    public function test_completion_fails_without_delivery_proof(): void
    {
        $order = $this->createOrder(OrderStatus::DIKIRIM);
        $this->assertNull($order->foto_bukti_penyerahan);

        $this->actingAs($this->salesA);
        $response = $this->patch(route('order.selesai', $order));
        $response->assertSessionHasErrors('foto_bukti_penyerahan');

        $this->assertSame(OrderStatus::DIKIRIM, $order->fresh()->status_penjualan);
        $this->assertNull($order->fresh()->tanggal_selesai);
    }

    public function test_invalid_state_transitions_are_rejected(): void
    {
        // 1. menunggu_validasi -> dikirim (cannot skip diproses)
        $order1 = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);
        $this->actingAs($this->admin);
        $this->patch(route('transaksi.kirim', $order1))->assertSessionHasErrors('transaksi');
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order1->fresh()->status_penjualan);

        // 2. menunggu_validasi -> selesai (cannot skip to selesai)
        $this->actingAs($this->salesA);
        $this->patch(route('order.selesai', $order1))->assertSessionHasErrors('order');
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order1->fresh()->status_penjualan);

        // 3. diproses -> selesai (cannot skip dikirim or complete from diproses)
        $order2 = $this->createOrder(OrderStatus::DIPROSES);
        $this->patch(route('order.selesai', $order2))->assertSessionHasErrors('order');
        $this->assertSame(OrderStatus::DIPROSES, $order2->fresh()->status_penjualan);

        // 4. selesai -> any state (cannot transition once selesai)
        $order3 = $this->createOrder(OrderStatus::SELESAI, null, 'bukti.jpg');
        $this->actingAs($this->admin);
        $this->patch(route('transaksi.validasi', $order3))->assertSessionHasErrors('transaksi');
        $this->patch(route('transaksi.kirim', $order3))->assertSessionHasErrors('transaksi');

        $this->actingAs($this->salesA);
        $this->patch(route('order.selesai', $order3))->assertSessionHasErrors('order');

        // 5. Backward transition: dikirim -> diproses (re-validating a dikirim order fails)
        $order4 = $this->createOrder(OrderStatus::DIKIRIM);
        $this->actingAs($this->admin);
        $this->patch(route('transaksi.validasi', $order4))->assertSessionHasErrors('transaksi');
        $this->assertSame(OrderStatus::DIKIRIM, $order4->fresh()->status_penjualan);
    }

    public function test_role_authorization_enforcement(): void
    {
        $order = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);

        // Sales cannot validate
        $this->actingAs($this->salesA);
        $this->patch(route('transaksi.validasi', $order))->assertForbidden();

        // Sales cannot change diproses -> dikirim
        $orderDiproses = $this->createOrder(OrderStatus::DIPROSES);
        $this->patch(route('transaksi.kirim', $orderDiproses))->assertForbidden();

        // Admin cannot complete order (only Sales)
        $orderDikirim = $this->createOrder(OrderStatus::DIKIRIM, null, 'bukti.jpg');
        $this->actingAs($this->admin);
        $this->patch(route('order.selesai', $orderDikirim))->assertForbidden();

        // Admin cannot upload delivery proof on Sales order
        $file = UploadedFile::fake()->image('bukti.jpg');
        $this->post(route('order.uploadBukti', $orderDikirim), ['foto_bukti_penyerahan' => $file])
            ->assertForbidden();
    }

    public function test_sales_cannot_modify_another_sales_orders(): void
    {
        // Order belongs to Sales A
        $orderA = $this->createOrder(OrderStatus::DIKIRIM, $this->salesA, 'bukti_a.jpg');

        // Sales B attempts to upload proof to Order A -> 403
        $this->actingAs($this->salesB);
        $file = UploadedFile::fake()->image('bukti_b.jpg');
        $this->post(route('order.uploadBukti', $orderA), ['foto_bukti_penyerahan' => $file])
            ->assertForbidden();

        // Sales B attempts to complete Order A -> 403
        $this->patch(route('order.selesai', $orderA))->assertForbidden();
        $this->assertSame(OrderStatus::DIKIRIM, $orderA->fresh()->status_penjualan);

        // Sales B attempts to edit Order A while menunggu_validasi -> 403
        $orderAMenunggu = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI, $this->salesA);
        $this->get(route('order.edit', $orderAMenunggu))->assertForbidden();
        $this->put(route('order.update', $orderAMenunggu), [
            'id_pelanggan' => $this->customer->getKey(),
            'produk' => [['id_produk' => $this->product->getKey(), 'jumlah' => 1]],
        ])->assertForbidden();
    }

    public function test_delivery_proof_requires_valid_image(): void
    {
        $order = $this->createOrder(OrderStatus::DIKIRIM);
        $this->actingAs($this->salesA);

        // Empty file
        $this->post(route('order.uploadBukti', $order), [])
            ->assertSessionHasErrors('foto_bukti_penyerahan');

        // Non-image file (e.g. PDF/TXT)
        $txtFile = UploadedFile::fake()->create('document.txt', 100);
        $this->post(route('order.uploadBukti', $order), ['foto_bukti_penyerahan' => $txtFile])
            ->assertSessionHasErrors('foto_bukti_penyerahan');

        // File too large (> 5MB)
        $largeFile = UploadedFile::fake()->image('large.jpg')->size(6000);
        $this->post(route('order.uploadBukti', $order), ['foto_bukti_penyerahan' => $largeFile])
            ->assertSessionHasErrors('foto_bukti_penyerahan');

        // Uploading proof when not dikirim fails
        $orderMenunggu = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);
        $validImage = UploadedFile::fake()->image('proof.jpg');
        $this->post(route('order.uploadBukti', $orderMenunggu), ['foto_bukti_penyerahan' => $validImage])
            ->assertSessionHasErrors('order');
    }
}
