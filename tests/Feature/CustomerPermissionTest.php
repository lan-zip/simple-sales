<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPermissionTest extends TestCase
{
    private User $admin;

    private User $sales;

    private User $direktur;

    private Pelanggan $customer;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'customer_perm_testing',
            'database.connections' => [
                'customer_perm_testing' => [
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

        $this->admin = $this->createUser('admin', 'Admin Customer');
        $this->sales = $this->createUser('sales', 'Sales Customer');
        $this->direktur = $this->createUser('direktur', 'Direktur Customer');

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-001',
            'nama_pelanggan' => 'I Putu Agus',
            'nama_toko' => 'Toko Bangunan Agus',
            'alamat_pelanggan' => 'Jl. Danau Tamblingan No. 12',
            'no_wa' => '081234567890',
            'status_pelanggan' => 'aktif',
        ]);
    }

    private function createUser(string $role, string $name): User
    {
        return User::create([
            'nama_user' => $name,
            'username' => $role.'_'.uniqid(),
            'password' => bcrypt('password'),
            'role' => $role,
            'no_telp' => '081234567890',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ]);
    }

    public function test_admin_has_full_crud_access_to_customer_endpoints(): void
    {
        $this->actingAs($this->admin);

        // 1. Index / List
        $response = $this->get(route('pelanggan.index'));
        $response->assertOk()
            ->assertSee('Data Pelanggan')
            ->assertSee('Tambah Pelanggan')
            ->assertSee('Ubah')
            ->assertSee('Nonaktifkan');

        // 2. Create form
        $this->get(route('pelanggan.create'))->assertOk();

        // 3. Store new customer
        $storeData = [
            'kode_pelanggan' => 'CUST-ADM-01',
            'nama_pelanggan' => 'Customer Baru Admin',
            'nama_toko' => 'Toko Admin',
            'alamat_pelanggan' => 'Jl. Hayam Wuruk No. 5',
            'no_wa' => '081299998888',
        ];

        $storeResponse = $this->post(route('pelanggan.store'), $storeData);
        $storeResponse->assertRedirect(route('pelanggan.index'))
            ->assertSessionHas('success');

        $created = Pelanggan::where('kode_pelanggan', 'CUST-ADM-01')->firstOrFail();
        $this->assertSame('aktif', $created->status_pelanggan);

        // 4. Edit form
        $this->get(route('pelanggan.edit', $created))->assertOk();

        // 5. Update customer
        $updateData = array_merge($storeData, ['nama_toko' => 'Toko Admin Diperbarui']);
        $updateResponse = $this->put(route('pelanggan.update', $created), $updateData);
        $updateResponse->assertRedirect(route('pelanggan.index'))
            ->assertSessionHas('success');

        $this->assertSame('Toko Admin Diperbarui', $created->fresh()->nama_toko);

        // 6. Deactivate customer
        $deactivateResponse = $this->patch(route('pelanggan.nonaktifkan', $created));
        $deactivateResponse->assertRedirect(route('pelanggan.index'))
            ->assertSessionHas('success');

        $this->assertSame('nonaktif', $created->fresh()->status_pelanggan);
    }

    public function test_sales_can_view_and_create_customers(): void
    {
        $this->actingAs($this->sales);

        // 1. Index / List
        $response = $this->get(route('pelanggan.index'));
        $response->assertOk()
            ->assertSee('Data Pelanggan')
            ->assertSee('Tambah Pelanggan');

        // 2. Create form
        $this->get(route('pelanggan.create'))->assertOk();

        // 3. Store customer
        $storeData = [
            'kode_pelanggan' => 'CUST-SLS-01',
            'nama_pelanggan' => 'Customer Baru Sales',
            'nama_toko' => 'Toko Lapangan Sales',
            'alamat_pelanggan' => 'Jl. Teuku Umar No. 8',
            'no_wa' => '081377776666',
        ];

        $storeResponse = $this->post(route('pelanggan.store'), $storeData);
        $storeResponse->assertRedirect(route('pelanggan.index'))
            ->assertSessionHas('success');

        $created = Pelanggan::where('kode_pelanggan', 'CUST-SLS-01')->firstOrFail();
        $this->assertSame('aktif', $created->status_pelanggan);
    }

    public function test_sales_is_forbidden_from_editing_and_deactivating_customers(): void
    {
        $this->actingAs($this->sales);

        // 1. Direct GET edit form -> 403 Forbidden
        $this->get(route('pelanggan.edit', $this->customer))
            ->assertForbidden();

        // 2. Direct PUT update endpoint -> 403 Forbidden and record unmodified
        $updateData = [
            'kode_pelanggan' => $this->customer->kode_pelanggan,
            'nama_pelanggan' => 'Nama Hendak Diubah',
            'nama_toko' => 'Toko Hendak Diubah',
            'alamat_pelanggan' => 'Alamat Baru',
            'no_wa' => '081299999999',
        ];

        $this->put(route('pelanggan.update', $this->customer), $updateData)
            ->assertForbidden();

        $this->assertSame('I Putu Agus', $this->customer->fresh()->nama_pelanggan);
        $this->assertSame('Toko Bangunan Agus', $this->customer->fresh()->nama_toko);

        // 3. Direct PATCH nonaktifkan endpoint -> 403 Forbidden and status unchanged
        $this->patch(route('pelanggan.nonaktifkan', $this->customer))
            ->assertForbidden();

        $this->assertSame('aktif', $this->customer->fresh()->status_pelanggan);

        // 4. In UI table, Sales does NOT see 'Ubah' or 'Nonaktifkan' action buttons
        $indexResponse = $this->get(route('pelanggan.index'));
        $indexResponse->assertOk()
            ->assertDontSee('Ubah Pelanggan')
            ->assertDontSee('Nonaktifkan Pelanggan')
            ->assertSee('—');
    }

    public function test_direktur_is_forbidden_from_all_customer_endpoints(): void
    {
        $this->actingAs($this->direktur);

        // 1. List / Index
        $this->get(route('pelanggan.index'))->assertForbidden();

        // 2. Create form
        $this->get(route('pelanggan.create'))->assertForbidden();

        // 3. Store
        $this->post(route('pelanggan.store'), [
            'kode_pelanggan' => 'CUST-DIR-01',
            'nama_pelanggan' => 'Customer Direktur',
            'nama_toko' => 'Toko Direktur',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567890',
        ])->assertForbidden();

        $this->assertDatabaseMissing('pelanggan', ['kode_pelanggan' => 'CUST-DIR-01']);

        // 4. Edit form
        $this->get(route('pelanggan.edit', $this->customer))->assertForbidden();

        // 5. Update
        $this->put(route('pelanggan.update', $this->customer), [
            'kode_pelanggan' => $this->customer->kode_pelanggan,
            'nama_pelanggan' => 'Nama Direktur',
            'nama_toko' => 'Toko Direktur',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567890',
        ])->assertForbidden();

        // 6. Deactivate
        $this->patch(route('pelanggan.nonaktifkan', $this->customer))->assertForbidden();
        $this->assertSame('aktif', $this->customer->fresh()->status_pelanggan);

        // 7. Navigation: Direktur does not see "Data Pelanggan" in sidebar or dashboard
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('pelanggan.index').'"', false);
    }

    public function test_unauthenticated_guests_are_redirected_to_login_for_all_customer_endpoints(): void
    {
        $this->get(route('pelanggan.index'))->assertRedirect(route('login'));
        $this->get(route('pelanggan.create'))->assertRedirect(route('login'));
        $this->post(route('pelanggan.store'), [
            'kode_pelanggan' => 'CUST-GST-01',
            'nama_pelanggan' => 'Customer Guest',
            'nama_toko' => 'Toko Guest',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567890',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('pelanggan', ['kode_pelanggan' => 'CUST-GST-01']);

        $this->get(route('pelanggan.edit', $this->customer))->assertRedirect(route('login'));
        $this->put(route('pelanggan.update', $this->customer), [
            'kode_pelanggan' => $this->customer->kode_pelanggan,
            'nama_pelanggan' => 'Nama Guest',
            'nama_toko' => 'Toko Guest',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081234567890',
        ])->assertRedirect(route('login'));

        $this->patch(route('pelanggan.nonaktifkan', $this->customer))->assertRedirect(route('login'));
        $this->assertSame('aktif', $this->customer->fresh()->status_pelanggan);
    }

    public function test_order_creation_preserves_active_customers_and_excludes_deactivated_customers(): void
    {
        $inactiveCustomer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-INACTIVE',
            'nama_pelanggan' => 'Customer Nonaktif',
            'nama_toko' => 'Toko Nonaktif',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '081200001111',
            'status_pelanggan' => 'nonaktif',
        ]);

        $this->actingAs($this->sales);

        $response = $this->get(route('order.create'));
        $response->assertOk()
            ->assertSee($this->customer->nama_pelanggan)
            ->assertSee($this->customer->nama_toko)
            ->assertDontSee($inactiveCustomer->nama_pelanggan);
    }
}
