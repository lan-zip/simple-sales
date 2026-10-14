<?php

namespace Tests\Feature;

use App\Models\{User, Produk, Pelanggan, Penjualan, DetailPenjualan};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Http, Schema};
use Tests\TestCase;

/** All writes are confined to a dedicated SQLite :memory: connection. */
class RedesignUiTest extends TestCase
{
    private User $admin;
    private User $sales;
    private Produk $product;
    private Pelanggan $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'ui_testing', 'database.connections' => [
            'ui_testing' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
        ], 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getConfig('database'));
        Http::preventStrayRequests();
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        // Explicit test schema; never run production migrations or seeders.
        Schema::create('user', function (Blueprint $t) {
            $t->id('id_user');
            foreach (['nama_user','username','password','role','no_telp','alamat','status_user'] as $column) $t->string($column);
            $t->unique('username');
        });
        Schema::create('produk', function (Blueprint $t) {
            $t->id('id_produk');
            foreach (['kode_produk','nama_produk','kategori_produk','satuan_produk','status_produk'] as $column) $t->string($column);
            $t->decimal('harga_produk',12,2);
            $t->integer('stok_produk');
        });
        Schema::create('pelanggan', function (Blueprint $t) {
            $t->id('id_pelanggan');
            foreach (['kode_pelanggan','nama_pelanggan','nama_toko','alamat_pelanggan','no_wa','status_pelanggan'] as $column) $t->string($column);
        });
        Schema::create('penjualan', function (Blueprint $t) {
            $t->id('id_penjualan');
            $t->unsignedBigInteger('id_pelanggan');
            $t->unsignedBigInteger('id_user');
            $t->string('nomor_penjualan');
            $t->string('no_wa')->nullable();
            $t->date('tanggal_penjualan');
            $t->decimal('total_penjualan',12,2);
            $t->string('status_penjualan');
            foreach (['tanggal_validasi','tanggal_selesai'] as $column) $t->date($column)->nullable();
            foreach (['bukti_penyerahan','foto_toko','foto_bukti_penyerahan','status_wa'] as $column) $t->string($column)->nullable();
        });
        Schema::create('detail_penjualan', function (Blueprint $t) {
            $t->id('id_detail_penjualan');
            $t->unsignedBigInteger('id_produk');
            $t->unsignedBigInteger('id_penjualan');
            $t->integer('jumlah_produk');
            $t->decimal('harga_satuan',12,2);
            $t->decimal('subtotal',12,2);
        });
        $this->admin = $this->user('admin');
        $this->sales = $this->user('sales');
        $this->product = Produk::create(['kode_produk'=>'QA-P','nama_produk'=>'Produk QA','kategori_produk'=>'QA','harga_produk'=>12500.25,'stok_produk'=>20,'satuan_produk'=>'pcs','status_produk'=>'aktif']);
        $this->customer = Pelanggan::create(['kode_pelanggan'=>'QA-C','nama_pelanggan'=>'Pelanggan QA','nama_toko'=>'Toko QA','alamat_pelanggan'=>'Alamat QA','no_wa'=>'0800000000','status_pelanggan'=>'aktif']);
    }

    private function user(string $role): User
    {
        return User::create(['nama_user'=>'QA '.$role,'username'=>$role.uniqid(),'password'=>bcrypt('qa-password'),'role'=>$role,'no_telp'=>'0800000000','alamat'=>'QA','status_user'=>'aktif']);
    }

    private function order(string $status = 'menunggu_validasi', string $total = '25000.50', ?User $owner = null): Penjualan
    {
        return Penjualan::create(['id_pelanggan'=>$this->customer->getKey(),'id_user'=>($owner ?? $this->sales)->getKey(),'nomor_penjualan'=>'QA-'.uniqid(),'tanggal_penjualan'=>'2026-10-11','total_penjualan'=>$total,'status_penjualan'=>$status]);
    }

    public function test_admin_revenue_excludes_other_statuses_and_latest_orders_are_bounded(): void
    {
        $this->order('selesai','12500.25');
        $this->order('selesai','5000.50');
        foreach (['menunggu_validasi','tervalidasi','diproses','dikirim','menunggu_konfirmasi'] as $status) $this->order($status,'999999');
        $expected = Penjualan::orderByDesc('id_penjualan')->limit(5)->pluck('id_penjualan')->all();
        DB::enableQueryLog();
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertOk()->assertViewHas('totalPendapatan', fn ($value) => (float)$value === 17500.75);
        $response->assertViewHas('orderTerbaru', fn ($orders) => $orders->count() === 5 && $orders->pluck('id_penjualan')->all() === $expected && $orders->every(fn ($o) => $o->relationLoaded('pelanggan') && $o->relationLoaded('user')));
        $this->assertLessThanOrEqual(8,count(DB::getQueryLog()),'Dashboard must not query relationships per row');
        Http::assertNothingSent();
    }

    public function test_roles_see_only_their_navigation_and_sales_scope(): void
    {
        $this->order();
        $this->order('selesai','100',$this->user('sales'));
        $this->actingAs($this->sales)->get('/dashboard')->assertOk()->assertViewHas('totalOrder',1)->assertViewHas('orderSelesai',0)->assertDontSee('href="'.route('sales.index').'"',false);
        $this->get('/produk')->assertForbidden();
        $this->get('/transaksi')->assertForbidden();
        $this->actingAs($this->user('direktur'))->get('/dashboard')->assertOk()->assertViewHas('totalPenjualan',100)->assertDontSee('href="'.route('order.index').'"',false);
        $this->get('/pelanggan')->assertForbidden();
    }

    public function test_all_business_pages_and_empty_search_render_through_real_routes(): void
    {
        $order = $this->order();
        DetailPenjualan::create(['id_penjualan'=>$order->getKey(),'id_produk'=>$this->product->getKey(),'jumlah_produk'=>2,'harga_satuan'=>12500.25,'subtotal'=>25000.50]);
        $this->actingAs($this->admin);
        foreach (['sales','produk','pelanggan'] as $module) {
            $id = match($module) {'sales'=>$this->sales->getKey(),'produk'=>$this->product->getKey(),default=>$this->customer->getKey()};
            foreach (["/$module","/$module/tambah","/$module/$id/edit","/$module?cari=NO_MATCH"] as $url) $this->get($url)->assertOk();
        }
        $this->get('/transaksi?status=menunggu_validasi&cari=QA')->assertOk()->assertSee('name="status"',false);
        $this->get('/transaksi/'.$order->getKey())->assertOk()->assertSee('Validasi Transaksi');
        $this->actingAs($this->sales);
        foreach (['/order','/order/tambah','/order/'.$order->getKey(),'/order/'.$order->getKey().'/edit'] as $url) $this->get($url)->assertOk();
        $this->get('/order/'.$order->getKey().'/edit')->assertSee('name="produk[0][id_produk]"',false)->assertSee('value="2"',false);
        Http::assertNothingSent();
    }

    public function test_master_forms_preserve_create_update_and_status_actions(): void
    {
        $this->actingAs($this->admin);
        $data = ['kode_produk'=>'QA-P2','nama_produk'=>'Produk Baru','kategori_produk'=>'QA','harga_produk'=>'2500.50','stok_produk'=>30,'satuan_produk'=>'box'];
        $this->post('/produk',$data)->assertRedirect(route('produk.index'));
        $product = Produk::where('kode_produk','QA-P2')->firstOrFail();
        $this->put('/produk/'.$product->getKey(),array_merge($data,['harga_produk'=>'2600.25']))->assertRedirect(route('produk.index'));
        $this->assertEquals(2600.25,$product->fresh()->harga_produk);
        $this->patch('/produk/'.$product->getKey().'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif',$product->fresh()->status_produk);
        $data = ['nama_user'=>'Sales Baru','username'=>'qa-new','password'=>'safe-password','no_telp'=>'0800000000','alamat'=>'Alamat'];
        $this->post('/sales',$data)->assertRedirect(route('sales.index'));
        $sales = User::where('username','qa-new')->firstOrFail();
        $password = $sales->password;
        $this->put('/sales/'.$sales->getKey(),array_merge($data,['password'=>'','nama_user'=>'Sales Diubah']))->assertRedirect();
        $this->assertSame($password,$sales->fresh()->password);
        $this->patch('/sales/'.$sales->getKey().'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif',$sales->fresh()->status_user);
        $this->patch('/sales/'.$sales->getKey().'/aktifkan')->assertRedirect();
        $this->assertSame('aktif',$sales->fresh()->status_user);
        $data = ['kode_pelanggan'=>'QA-C2','nama_pelanggan'=>'Baru','nama_toko'=>'Toko Baru','alamat_pelanggan'=>'Alamat','no_wa'=>'0800000000'];
        $this->post('/pelanggan',$data)->assertRedirect(route('pelanggan.index'));
        $customer = Pelanggan::where('kode_pelanggan','QA-C2')->firstOrFail();
        $this->put('/pelanggan/'.$customer->getKey(),array_merge($data,['nama_toko'=>'Toko Diubah']))->assertRedirect();
        $this->assertSame('Toko Diubah',$customer->fresh()->nama_toko);
        $this->patch('/pelanggan/'.$customer->getKey().'/nonaktifkan')->assertRedirect();
        $this->assertSame('nonaktif',$customer->fresh()->status_pelanggan);
        $this->post('/produk',[])->assertSessionHasErrors(['kode_produk','harga_produk','stok_produk']);
        Http::assertNothingSent();
    }

    public function test_order_restores_sparse_old_input_and_matching_field_error(): void
    {
        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors->put('default', new \Illuminate\Support\MessageBag(['produk.3.jumlah'=>'Jumlah wajib lebih dari nol.']));
        $this->actingAs($this->sales)->withSession([
            '_old_input'=>['id_pelanggan'=>$this->customer->getKey(),'produk'=>[3=>['id_produk'=>$this->product->getKey(),'jumlah'=>'0']]],
            'errors'=>$errors,
        ])->get('/order/tambah')->assertOk()
            ->assertSee('name="produk[0][jumlah]"',false)
            ->assertSee('value="0"',false)
            ->assertSee('aria-describedby="quantity-0-error"',false)
            ->assertSee('Jumlah wajib lebih dari nol.');
    }

    public function test_search_status_and_pagination_preserve_query_and_owner_scope(): void
    {
        for ($i = 0; $i < 14; $i++) $this->order();
        $this->order('selesai');
        $this->order('menunggu_validasi','100',$this->user('sales'));
        $this->actingAs($this->admin)->get('/transaksi?cari=QA&status=menunggu_validasi')
            ->assertOk()->assertViewHas('transaksi',fn ($rows) => $rows->total() === 15 && $rows->count() === 10)
            ->assertSee('page=2',false)->assertSee('cari=QA',false)->assertSee('status=menunggu_validasi',false);
        $this->get('/transaksi?cari=QA&status=menunggu_validasi&page=2')
            ->assertOk()->assertViewHas('transaksi',fn ($rows) => $rows->count() === 5);
        $this->actingAs($this->sales)->get('/order?cari=QA')
            ->assertOk()->assertViewHas('order',fn ($rows) => $rows->total() === 15 && $rows->every(fn ($order) => $order->id_user === $this->sales->getKey()));
    }

    public function test_order_and_validation_flow_uses_memory_database_and_fake_whatsapp(): void
    {
        $data = ['id_pelanggan'=>$this->customer->getKey(),'produk'=>[['id_produk'=>$this->product->getKey(),'jumlah'=>2]]];
        $this->actingAs($this->sales)->post('/order',$data)->assertRedirect(route('order.index'));
        $order = Penjualan::firstOrFail();
        $this->assertSame('25000.50',$order->total_penjualan);
        $data['produk'][0]['jumlah'] = 3;
        $this->put('/order/'.$order->getKey(),$data)->assertRedirect(route('order.index'));
        $this->assertSame('37500.75',$order->fresh()->total_penjualan);
        $this->assertSame(3,$order->detailPenjualan()->first()->jumlah_produk);
        $this->actingAs($this->user('sales'))->get('/order/'.$order->getKey())->assertForbidden();
        $this->actingAs($this->admin)->patch('/transaksi/'.$order->getKey().'/validasi')->assertRedirect(route('transaksi.show',$order));
        $this->assertSame('tervalidasi',$order->fresh()->status_penjualan);
        $this->assertSame(17,$this->product->fresh()->stok_produk);
        Http::assertSentCount(1);
        $this->patch('/transaksi/'.$order->getKey().'/validasi')->assertSessionHasErrors('transaksi');
        $this->assertSame(17,$this->product->fresh()->stok_produk);
        Http::assertSentCount(1);
        $this->actingAs($this->sales)->get('/order/'.$order->getKey().'/edit')->assertForbidden();
    }
}
