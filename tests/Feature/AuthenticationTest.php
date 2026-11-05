<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'auth_testing',
            'database.connections' => [
                'auth_testing' => [
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
        });
    }

    public function test_login_page_renders_successfully_for_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('name="username"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_authenticated_user_accessing_login_page_is_redirected_to_dashboard(): void
    {
        $user = $this->createActiveUser();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_authenticate_with_valid_active_credentials(): void
    {
        $user = $this->createActiveUser([
            'username' => 'sales_demo',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('login.process'), [
            'username' => 'sales_demo',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHasNoErrors();
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $this->createActiveUser([
            'username' => 'sales_demo',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->from(route('login'))->post(route('login.process'), [
            'username' => 'sales_demo',
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
        $this->assertSame('sales_demo', session('_old_input.username'));
    }

    public function test_user_cannot_authenticate_with_non_existent_username(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'username' => 'non_existent_user',
            'password' => 'any_password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_login_requires_username_and_password(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), []);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'username' => 'Username wajib diisi.',
            'password' => 'Password wajib diisi.',
        ]);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate_even_with_correct_password(): void
    {
        $this->createActiveUser([
            'username' => 'inactive_sales',
            'password' => Hash::make('secret123'),
            'status_user' => 'nonaktif',
        ]);

        $response = $this->from(route('login'))->post(route('login.process'), [
            'username' => 'inactive_sales',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login' => 'Username atau password salah.']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createActiveUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_unauthenticated_guest_accessing_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_unauthenticated_guest_accessing_logout_is_redirected_to_login(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function createActiveUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'nama_user' => 'Test User',
            'username' => 'test_user',
            'password' => Hash::make('password123'),
            'role' => 'sales',
            'no_telp' => '081234567890',
            'alamat' => 'Denpasar',
            'status_user' => 'aktif',
        ], $overrides));
    }
}
