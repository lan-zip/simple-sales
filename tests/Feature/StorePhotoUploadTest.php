<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
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

class StorePhotoUploadTest extends TestCase
{
    private User $admin;

    private User $sales;

    private Produk $product;

    private Pelanggan $customer;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'photo_testing',
            'database.connections' => [
                'photo_testing' => [
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
            'nama_user' => 'Admin QA',
            'username' => 'admin_qa',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'no_telp' => '081234567890',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);

        $this->sales = User::create([
            'nama_user' => 'Sales QA',
            'username' => 'sales_qa',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'no_telp' => '081234567891',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);

        $this->product = Produk::create([
            'kode_produk' => 'PRD-01',
            'nama_produk' => 'Produk QA',
            'kategori_produk' => 'Kategori QA',
            'harga_produk' => 15000,
            'stok_produk' => 20,
            'satuan_produk' => 'pcs',
            'status_produk' => 'aktif',
        ]);

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-01',
            'nama_pelanggan' => 'Pelanggan QA',
            'nama_toko' => 'Toko Makmur',
            'alamat_pelanggan' => 'Jl. Gatot Subroto No. 10',
            'no_wa' => '081234567892',
            'status_pelanggan' => 'aktif',
        ]);
    }

    private function validOrderData(?UploadedFile $file = null): array
    {
        return [
            'id_pelanggan' => $this->customer->getKey(),
            'foto_toko' => $file ?? UploadedFile::fake()->image('toko.jpg', 640, 480),
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 2],
            ],
        ];
    }

    public function test_valid_photo_successfully_creates_order_and_persists_path(): void
    {
        $this->actingAs($this->sales);

        $file = UploadedFile::fake()->image('store_front.jpg', 800, 600);
        $response = $this->post(route('order.store'), $this->validOrderData($file));

        $response->assertRedirect(route('order.index'));
        $order = Penjualan::firstOrFail();

        $this->assertNotNull($order->foto_toko);
        $this->assertStringStartsWith('foto_toko/', $order->foto_toko);
        $this->assertSame(OrderStatus::MENUNGGU_VALIDASI, $order->status_penjualan);
        Storage::disk('public')->assertExists($order->foto_toko);
    }

    public function test_order_creation_without_photo_is_rejected(): void
    {
        $this->actingAs($this->sales);

        $data = $this->validOrderData();
        unset($data['foto_toko']);

        $response = $this->post(route('order.store'), $data);
        $response->assertSessionHasErrors('foto_toko');
        $this->assertDatabaseCount('penjualan', 0);
    }

    public function test_order_creation_with_invalid_file_type_is_rejected(): void
    {
        $this->actingAs($this->sales);

        $textFile = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');
        $data = $this->validOrderData($textFile);

        $response = $this->post(route('order.store'), $data);
        $response->assertSessionHasErrors('foto_toko');
        $this->assertDatabaseCount('penjualan', 0);
    }

    public function test_order_creation_with_oversized_photo_is_rejected(): void
    {
        $this->actingAs($this->sales);

        $oversizedFile = UploadedFile::fake()->image('giant_photo.jpg')->size(6000); // 6MB > 5MB limit
        $data = $this->validOrderData($oversizedFile);

        $response = $this->post(route('order.store'), $data);
        $response->assertSessionHasErrors('foto_toko');
        $this->assertDatabaseCount('penjualan', 0);
    }

    public function test_failed_order_creation_does_not_leave_orphan_file(): void
    {
        $this->actingAs($this->sales);

        $file = UploadedFile::fake()->image('will_fail.jpg');
        // Excessive product quantity to trigger ValidationException inside DB transaction
        $data = [
            'id_pelanggan' => $this->customer->getKey(),
            'foto_toko' => $file,
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 9999], // Exceeds stock (20)
            ],
        ];

        $response = $this->post(route('order.store'), $data);
        $response->assertSessionHasErrors('produk');

        $this->assertDatabaseCount('penjualan', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles('foto_toko'));
    }

    public function test_order_edit_keeps_existing_photo_when_no_new_photo_is_uploaded(): void
    {
        $this->actingAs($this->sales);

        $file = UploadedFile::fake()->image('original.jpg');
        $this->post(route('order.store'), $this->validOrderData($file));
        $order = Penjualan::firstOrFail();
        $originalPath = $order->foto_toko;

        // Edit without uploading new file
        $updateData = [
            'id_pelanggan' => $this->customer->getKey(),
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 3],
            ],
        ];

        $response = $this->put(route('order.update', $order), $updateData);
        $response->assertRedirect(route('order.index'));

        $order->refresh();
        $this->assertSame($originalPath, $order->foto_toko);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_order_edit_replaces_old_photo_and_deletes_it_from_disk(): void
    {
        $this->actingAs($this->sales);

        $originalFile = UploadedFile::fake()->image('original.jpg');
        $this->post(route('order.store'), $this->validOrderData($originalFile));
        $order = Penjualan::firstOrFail();
        $oldPath = $order->foto_toko;
        Storage::disk('public')->assertExists($oldPath);

        // Edit with new photo
        $newFile = UploadedFile::fake()->image('replacement.jpg');
        $updateData = [
            'id_pelanggan' => $this->customer->getKey(),
            'foto_toko' => $newFile,
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 1],
            ],
        ];

        $response = $this->put(route('order.update', $order), $updateData);
        $response->assertRedirect(route('order.index'));

        $order->refresh();
        $this->assertNotSame($oldPath, $order->foto_toko);
        Storage::disk('public')->assertExists($order->foto_toko);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_failed_order_edit_preserves_old_photo_and_cleans_up_new_file(): void
    {
        $this->actingAs($this->sales);

        $originalFile = UploadedFile::fake()->image('keep_me.jpg');
        $this->post(route('order.store'), $this->validOrderData($originalFile));
        $order = Penjualan::firstOrFail();
        $oldPath = $order->foto_toko;

        // Attempt update with new photo but invalid stock quantity to trigger failure
        $newFile = UploadedFile::fake()->image('new_candidate.jpg');
        $updateData = [
            'id_pelanggan' => $this->customer->getKey(),
            'foto_toko' => $newFile,
            'produk' => [
                ['id_produk' => $this->product->getKey(), 'jumlah' => 9999], // Exceeds stock
            ],
        ];

        $response = $this->put(route('order.update', $order), $updateData);
        $response->assertSessionHasErrors('produk');

        $order->refresh();
        $this->assertSame($oldPath, $order->foto_toko);
        Storage::disk('public')->assertExists($oldPath);
        // Ensure the candidate file was deleted, leaving only the old photo
        $this->assertCount(1, Storage::disk('public')->allFiles('foto_toko'));
    }

    public function test_photo_is_displayed_in_sales_order_detail(): void
    {
        $this->actingAs($this->sales);

        $file = UploadedFile::fake()->image('store_view.jpg');
        $this->post(route('order.store'), $this->validOrderData($file));
        $order = Penjualan::firstOrFail();

        $response = $this->get(route('order.show', $order));
        $response->assertOk()
            ->assertSee('Foto Toko')
            ->assertSee(asset('storage/'.$order->foto_toko), false);
    }

    public function test_photo_is_displayed_in_admin_transaction_detail(): void
    {
        $this->actingAs($this->sales);

        $file = UploadedFile::fake()->image('admin_view.jpg');
        $this->post(route('order.store'), $this->validOrderData($file));
        $order = Penjualan::firstOrFail();

        $this->actingAs($this->admin);
        $response = $this->get(route('transaksi.show', $order));
        $response->assertOk()
            ->assertSee('Foto Toko')
            ->assertSee(asset('storage/'.$order->foto_toko), false);
    }
}
