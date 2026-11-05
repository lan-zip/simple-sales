<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\DetailPenjualan;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    private User $admin;

    private User $salesA;

    private User $salesB;

    private User $direktur;

    private Produk $product;

    private Pelanggan $customer;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'wa_testing',
            'database.connections' => [
                'wa_testing' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ],
            ],
            'session.driver' => 'array',
            'cache.default' => 'array',
            'services.fonnte.token' => 'secret-fonnte-test-token',
            'services.fonnte.url' => 'https://api.fonnte.com/send',
        ]);

        DB::purge();
        Http::preventStrayRequests();
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

        $this->admin = $this->createUser('admin', 'Admin WA');
        $this->salesA = $this->createUser('sales', 'Sales Satu');
        $this->salesB = $this->createUser('sales', 'Sales Dua');
        $this->direktur = $this->createUser('direktur', 'Direktur Utama');

        $this->product = Produk::create([
            'kode_produk' => 'PRD-WA-01',
            'nama_produk' => 'Cat Tembok 5kg',
            'kategori_produk' => 'Cat',
            'harga_produk' => 75000,
            'stok_produk' => 50,
            'satuan_produk' => 'kaleng',
            'status_produk' => 'aktif',
        ]);

        $this->customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-WA-01',
            'nama_pelanggan' => 'I Ketut Wirawan',
            'nama_toko' => 'UD Sinar Jaya',
            'alamat_pelanggan' => 'Jl. Gatot Subroto No. 10',
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

    private function createOrder(
        string|OrderStatus $status = OrderStatus::MENUNGGU_VALIDASI,
        ?User $owner = null,
        ?Pelanggan $customer = null,
        ?string $orderNoWa = null
    ): Penjualan {
        $rawStatus = $status instanceof OrderStatus ? $status->value : $status;

        $order = Penjualan::create([
            'id_pelanggan' => ($customer ?? $this->customer)->getKey(),
            'id_user' => ($owner ?? $this->salesA)->getKey(),
            'nomor_penjualan' => 'PJ-WA-'.uniqid(),
            'no_wa' => $orderNoWa,
            'tanggal_penjualan' => now()->toDateString(),
            'total_penjualan' => 150000,
            'status_penjualan' => $rawStatus,
            'tanggal_validasi' => in_array($rawStatus, ['diproses', 'dikirim', 'selesai'], true) ? now()->toDateString() : null,
            'tanggal_selesai' => $rawStatus === 'selesai' ? now()->toDateString() : null,
            'foto_toko' => 'foto_toko/store.jpg',
            'foto_bukti_penyerahan' => $rawStatus === 'selesai' ? 'bukti_penyerahan/proof.jpg' : null,
        ]);

        DetailPenjualan::create([
            'id_penjualan' => $order->getKey(),
            'id_produk' => $this->product->getKey(),
            'jumlah_produk' => 2,
            'harga_satuan' => 75000,
            'subtotal' => 150000,
        ]);

        return $order;
    }

    public function test_admin_validation_dispatches_whatsapp_notification_using_customer_phone_number(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        // Even if penjualan.no_wa contains a different or null value, pelanggan.no_wa is the source of truth
        $order = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI, $this->salesA, $this->customer, '089999999999');

        $response = $this->actingAs($this->admin)->patch(route('transaksi.validasi', $order));

        $response->assertRedirect(route('transaksi.show', $order))
            ->assertSessionHas('success');

        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan);
        $this->assertSame(48, $this->product->fresh()->stok_produk);

        Http::assertSentCount(1);
        Http::assertSent(function (HttpClientRequest $request) use ($order) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'secret-fonnte-test-token')
                && $request['target'] === '6281234567890'
                && $request['countryCode'] === '62'
                && str_contains($request['message'], 'I Ketut Wirawan')
                && str_contains($request['message'], $order->nomor_penjualan)
                && str_contains($request['message'], 'Rp 150.000');
        });
    }

    public function test_validation_commits_transaction_and_shows_warning_when_fonnte_returns_failure(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => false, 'reason' => 'device disconnected'], 500),
        ]);

        Log::spy();

        $order = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);

        $response = $this->actingAs($this->admin)->patch(route('transaksi.validasi', $order));

        $response->assertRedirect(route('transaksi.show', $order))
            ->assertSessionHas('warning');

        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan);
        $this->assertSame(48, $this->product->fresh()->stok_produk);
        Http::assertSentCount(1);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context) use ($order) {
                $serialized = json_encode($context);

                return $message === 'Fonnte WhatsApp notification failed'
                    && $context === [
                        'transaksi_id' => $order->id_penjualan,
                        'status_code' => 500,
                    ]
                    && ! str_contains((string) $serialized, 'secret-fonnte-test-token')
                    && ! str_contains((string) $serialized, '081234567890')
                    && ! str_contains((string) $serialized, '6281234567890')
                    && ! str_contains((string) $serialized, 'device disconnected');
            });
    }

    public function test_validation_handles_connection_timeout_without_retries_or_sensitive_logs(): void
    {
        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;
            throw new ConnectionException('Connection timed out containing 081234567890 and secret-fonnte-test-token');
        });

        Log::spy();

        $order = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI);

        $response = $this->actingAs($this->admin)->patch(route('transaksi.validasi', $order));

        $response->assertRedirect(route('transaksi.show', $order))
            ->assertSessionHas('warning');

        // Transaction must still be diproses and only 1 attempt made (no automatic retries)
        $this->assertSame(OrderStatus::DIPROSES, $order->fresh()->status_penjualan);
        $this->assertSame(1, $attempts);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context) use ($order) {
                $serialized = json_encode($context);

                return $message === 'Fonnte WhatsApp notification connection error'
                    && $context === [
                        'transaksi_id' => $order->id_penjualan,
                    ]
                    && ! str_contains((string) $serialized, 'secret-fonnte-test-token')
                    && ! str_contains((string) $serialized, '081234567890')
                    && ! str_contains((string) $serialized, 'Connection timed out');
            });
    }

    public function test_phone_number_normalization_accepts_valid_indonesian_formats_within_11_to_15_digits(): void
    {
        $service = app(WhatsAppService::class);

        $validCases = [
            '0811123456' => '62811123456',         // 10 local -> 11 normalized digits (min)
            '081234567890' => '6281234567890',     // 12 local -> 13 normalized digits
            '+6281234567890' => '6281234567890',   // +628... -> 628...
            '6281234567890' => '6281234567890',    // 628... -> unchanged
            '0812-3456-7890' => '6281234567890',   // hyphens
            '+62 812 3456 7890' => '6281234567890', // spaces
            '+62 (812) 3456-7890' => '6281234567890', // balanced parentheses with +62
            '(0812) 3456-7890' => '6281234567890', // balanced parentheses with local prefix
            '08123456789012' => '628123456789012', // 14 local -> 15 normalized digits (max)
        ];

        foreach ($validCases as $input => $expected) {
            $this->assertSame(
                $expected,
                $service->normalizePhoneNumber($input),
                "Failed asserting that '{$input}' normalizes to '{$expected}'."
            );
        }
    }

    public function test_phone_number_normalization_rejects_malformed_parentheses_bare_8_and_invalid_inputs_with_zero_http_calls(): void
    {
        Http::fake();
        $service = app(WhatsAppService::class);

        $invalidCases = [
            null,
            '',
            '   ',
            '81234567890',         // bare 8... number
            '0812(34567890',       // unbalanced opening parenthesis
            '0812)34567890',       // unbalanced closing parenthesis
            '0812()34567890',      // empty parentheses
            ')0812(34567890',      // inverted parentheses
            '(08)(12)3456789',     // multiple pairs of parentheses
            '0812(abc)34567',      // alphabetic inside parentheses
            '0812abc34567',        // alphabetic characters
            '0812+3456789',        // misplaced plus sign
            '++628123456789',      // multiple plus signs
            '+62 (0812) 3456-7890', // invalid combined +6208... prefix
            '02112345678',         // landline prefix (not 08...)
            '6280123456789',       // mobile prefix starting with 6280...
            '081234567',           // too short (10 normalized digits < 11)
            '081234567890123',     // too long (16 normalized digits > 15)
        ];

        foreach ($invalidCases as $invalidInput) {
            $this->assertNull(
                $service->normalizePhoneNumber($invalidInput),
                'Failed asserting that invalid input ['.var_export($invalidInput, true).'] is rejected.'
            );

            $customer = Pelanggan::create([
                'kode_pelanggan' => 'CUST-INV-'.uniqid(),
                'nama_pelanggan' => 'Pelanggan Invalid',
                'nama_toko' => 'Toko Invalid',
                'alamat_pelanggan' => 'Denpasar',
                'no_wa' => (string) $invalidInput,
                'status_pelanggan' => 'aktif',
            ]);

            $order = $this->createOrder(OrderStatus::DIPROSES, $this->salesA, $customer);

            $this->assertFalse($service->sendOrderNotification($order));
        }

        Http::assertNothingSent();
    }

    public function test_phone_number_validation_rejects_numbers_starting_with_6280_and_makes_no_http_call(): void
    {
        Http::fake();
        $service = app(WhatsAppService::class);

        $this->assertSame('6281234567890', $service->normalizePhoneNumber('6281234567890'));
        $this->assertNull($service->normalizePhoneNumber('6280123456789'));

        $customer = Pelanggan::create([
            'kode_pelanggan' => 'CUST-INV-6280',
            'nama_pelanggan' => 'Customer 6280',
            'nama_toko' => 'Toko 6280',
            'alamat_pelanggan' => 'Denpasar',
            'no_wa' => '6280123456789',
            'status_pelanggan' => 'aktif',
        ]);

        $order = $this->createOrder(OrderStatus::DIPROSES, $this->salesA, $customer);

        $this->assertFalse($service->sendOrderNotification($order));
        Http::assertNothingSent();
    }

    public function test_missing_fonnte_token_fails_gracefully_with_zero_http_requests(): void
    {
        Http::fake();
        Log::spy();

        config(['services.fonnte.token' => '']);

        $order = $this->createOrder(OrderStatus::DIPROSES);
        $service = app(WhatsAppService::class);

        $this->assertFalse($service->sendOrderNotification($order));
        Http::assertNothingSent();

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Fonnte WhatsApp token is not configured', [
                'transaksi_id' => $order->id_penjualan,
            ]);
    }

    public function test_manual_kirim_nota_authorization_matrix(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $orderSalesA = $this->createOrder(OrderStatus::DIPROSES, $this->salesA);

        // 1. Guest is redirected to login
        $this->post(route('transaksi.kirimNota', $orderSalesA))
            ->assertRedirect(route('login'));
        Http::assertNothingSent();

        // 2. Direktur is forbidden (403)
        $this->actingAs($this->direktur)
            ->post(route('transaksi.kirimNota', $orderSalesA))
            ->assertForbidden();
        Http::assertNothingSent();

        // 3. Non-owning Sales (Sales B) is forbidden (403)
        $this->actingAs($this->salesB)
            ->post(route('transaksi.kirimNota', $orderSalesA))
            ->assertForbidden();
        Http::assertNothingSent();

        // 4. Owning Sales (Sales A) is allowed
        $this->actingAs($this->salesA)
            ->from(route('order.show', $orderSalesA))
            ->post(route('transaksi.kirimNota', $orderSalesA))
            ->assertRedirect(route('order.show', $orderSalesA))
            ->assertSessionHas('success');
        Http::assertSentCount(1);

        // 5. Admin is allowed for any transaction
        $this->actingAs($this->admin)
            ->from(route('transaksi.show', $orderSalesA))
            ->post(route('transaksi.kirimNota', $orderSalesA))
            ->assertRedirect(route('transaksi.show', $orderSalesA))
            ->assertSessionHas('success');
        Http::assertSentCount(2);
    }

    public function test_manual_kirim_nota_status_boundaries(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        // Rejected statuses: menunggu_validasi and any non-canonical status
        foreach (['menunggu_validasi', 'tervalidasi', 'dibatalkan'] as $rejectedStatus) {
            $order = $this->createOrder($rejectedStatus, $this->salesA);

            $this->actingAs($this->admin)
                ->from(route('transaksi.show', $order))
                ->post(route('transaksi.kirimNota', $order))
                ->assertRedirect(route('transaksi.show', $order))
                ->assertSessionHas('error');
        }

        Http::assertNothingSent();

        // Allowed statuses: diproses, dikirim, selesai
        foreach ([OrderStatus::DIPROSES, OrderStatus::DIKIRIM, OrderStatus::SELESAI] as $index => $allowedStatus) {
            $order = $this->createOrder($allowedStatus, $this->salesA);

            $this->actingAs($this->admin)
                ->from(route('transaksi.show', $order))
                ->post(route('transaksi.kirimNota', $order))
                ->assertRedirect(route('transaksi.show', $order))
                ->assertSessionHas('success');

            Http::assertSentCount($index + 1);
        }
    }

    public function test_kirim_ulang_whatsapp_button_visibility_confirmation_and_no_print_by_role_and_status(): void
    {
        $pendingOrder = $this->createOrder(OrderStatus::MENUNGGU_VALIDASI, $this->salesA);

        // Hidden for menunggu_validasi on both Sales and Admin views
        $this->actingAs($this->salesA)
            ->get(route('order.show', $pendingOrder))
            ->assertOk()
            ->assertDontSee('Kirim Ulang WhatsApp');

        $this->actingAs($this->admin)
            ->get(route('transaksi.show', $pendingOrder))
            ->assertOk()
            ->assertDontSee('Kirim Ulang WhatsApp');

        // Visible for diproses, dikirim, and selesai with confirmation, CSRF, and no-print
        foreach ([OrderStatus::DIPROSES, OrderStatus::DIKIRIM, OrderStatus::SELESAI] as $status) {
            $order = $this->createOrder($status, $this->salesA);
            $expectedActionUrl = route('transaksi.kirimNota', $order);

            // Owning Sales view
            $salesContent = $this->actingAs($this->salesA)
                ->get(route('order.show', $order))
                ->assertOk()
                ->assertSee('Kirim Ulang WhatsApp')
                ->getContent();

            $this->assertMatchesRegularExpression(
                '/<form[^>]*action="'.preg_quote($expectedActionUrl, '/').'"[^>]*method="POST"[^>]*class="[^"]*no-print[^"]*"[^>]*data-confirm-message="[^"]+"[^>]*>[\s\S]*?name="_token"[\s\S]*?Kirim Ulang WhatsApp[\s\S]*?<\/form>/i',
                $salesContent
            );

            // Admin view
            $adminContent = $this->actingAs($this->admin)
                ->get(route('transaksi.show', $order))
                ->assertOk()
                ->assertSee('Kirim Ulang WhatsApp')
                ->getContent();

            $this->assertMatchesRegularExpression(
                '/<form[^>]*action="'.preg_quote($expectedActionUrl, '/').'"[^>]*method="POST"[^>]*class="[^"]*no-print[^"]*"[^>]*data-confirm-message="[^"]+"[^>]*>[\s\S]*?name="_token"[\s\S]*?Kirim Ulang WhatsApp[\s\S]*?<\/form>/i',
                $adminContent
            );
        }
    }

    public function test_public_test_wa_route_is_removed(): void
    {
        Http::fake();

        $this->get('/test-wa')->assertNotFound();

        Http::assertNothingSent();
    }
}
