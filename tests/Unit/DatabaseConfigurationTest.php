<?php

namespace Tests\Unit;

use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Blueprint;
use PDO;
use Tests\TestCase;

class DatabaseConfigurationTest extends TestCase
{
    public function test_mysql_connection_configuration_matches_proposal_baseline(): void
    {
        $mysql = config('database.connections.mysql');

        $this->assertIsArray($mysql);
        $this->assertSame('mysql', $mysql['driver']);
        $this->assertSame('utf8mb4', $mysql['charset']);
        $this->assertSame('utf8mb4_unicode_ci', $mysql['collation']);
        $this->assertTrue($mysql['strict']);

        // PHPUnit isolation: phpunit.xml sets DB_DATABASE=:memory:
        $this->assertSame(':memory:', $mysql['database']);

        // Production/default configuration source
        $rawConfig = file_get_contents(config_path('database.php'));
        $this->assertStringContainsString(
            "'default' => env('DB_CONNECTION', 'mysql')",
            $rawConfig
        );
        $this->assertStringContainsString(
            "'database' => env('DB_DATABASE', 'simple_sales')",
            $rawConfig
        );
    }

    public function test_core_table_blueprints_compile_to_valid_mysql_ddl(): void
    {
        $connection = new MySqlConnection(
            new PDO('sqlite::memory:'),
            'simple_sales',
            '',
            ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci']
        );
        $connection->useDefaultSchemaGrammar();

        $tableBlueprints = [
            'user' => function (Blueprint $t): void {
                $t->id('id_user');
                $t->string('nama_user', 100);
                $t->string('username', 50)->unique();
                $t->string('password', 255);
                $t->string('role', 20);
                $t->string('no_telp', 20)->nullable();
                $t->text('alamat')->nullable();
                $t->string('status_user', 20)->default('aktif');
            },
            'pelanggan' => function (Blueprint $t): void {
                $t->id('id_pelanggan');
                $t->string('kode_pelanggan', 30)->unique();
                $t->string('nama_pelanggan', 100);
                $t->string('nama_toko', 100);
                $t->text('alamat_pelanggan');
                $t->string('no_wa', 20);
                $t->string('status_pelanggan', 20)->default('aktif');
            },
            'produk' => function (Blueprint $t): void {
                $t->id('id_produk');
                $t->string('kode_produk', 30)->unique();
                $t->string('nama_produk', 100);
                $t->string('kategori_produk', 100);
                $t->decimal('harga_produk', 12, 2);
                $t->integer('stok_produk');
                $t->string('satuan_produk', 50);
                $t->string('status_produk', 20)->default('aktif');
            },
            'penjualan' => function (Blueprint $t): void {
                $t->id('id_penjualan');
                $t->unsignedBigInteger('id_pelanggan');
                $t->unsignedBigInteger('id_user');
                $t->string('nomor_penjualan', 30)->unique();
                $t->date('tanggal_penjualan');
                $t->decimal('total_penjualan', 12, 2)->default(0);
                $t->string('status_penjualan', 30);
                $t->date('tanggal_validasi')->nullable();
                $t->string('foto_toko', 255)->nullable();
                $t->string('foto_bukti_penyerahan', 255)->nullable();
                $t->date('tanggal_selesai')->nullable();
                $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
                $t->foreign('id_user')->references('id_user')->on('user');
            },
            'detail_penjualan' => function (Blueprint $t): void {
                $t->id('id_detail_penjualan');
                $t->unsignedBigInteger('id_produk');
                $t->unsignedBigInteger('id_penjualan');
                $t->integer('jumlah_produk');
                $t->decimal('harga_satuan', 12, 2);
                $t->decimal('subtotal', 12, 2);
                $t->foreign('id_produk')->references('id_produk')->on('produk');
                $t->foreign('id_penjualan')->references('id_penjualan')->on('penjualan');
            },
        ];

        foreach ($tableBlueprints as $tableName => $callback) {
            $blueprint = new Blueprint($connection, $tableName);
            $blueprint->create();
            $callback($blueprint);

            $statements = $blueprint->toSql();
            $this->assertNotEmpty($statements, "Generated SQL statements for {$tableName} should not be empty.");
            $this->assertStringContainsString("create table `{$tableName}`", $statements[0]);
            $this->assertStringContainsString('default character set utf8mb4', $statements[0]);
        }
    }
}
