# Laporan Phase 2 — Dashboard dan Halaman Bisnis

Tanggal: 11 Oktober 2026. Branch: `dev`.

Cakupan mengikuti persetujuan terbaru pengguna: dashboard **dan** halaman bisnis, termasuk master data dan transaksi yang sebelumnya dijadwalkan pada fase terpisah. Tidak melanjutkan Phase 3.

## Pemeriksaan awal dan batas perubahan

- Membaca `docs/design.md`, perubahan Phase 1, seluruh kontrak controller/model/route terkait, serta struktur tabel aktual melalui transaksi MySQL `READ ONLY`.
- Mempertahankan layout global, sidebar/topbar maroon, Inter lokal, tokens, komponen dan mekanisme interaksi Phase 1. Login dan layout tidak dikerjakan ulang pada Phase 2.
- Snapshot hash sebelum Phase 2 digunakan untuk membedakan pekerjaan baru dari perubahan sebelumnya. Perubahan lama pada `package-lock.json`, font, login, layout, dan dokumen desain tetap dipertahankan.
- Tidak mengubah route, model, middleware, authentication/authorization, konfigurasi database, migration, atau data MySQL. Tidak memasang package, menjalankan migration/seeder, mengirim WhatsApp sungguhan, commit, push, atau mengganti branch.
- Satu-satunya controller yang berubah: `DashboardController`, pada cabang Admin sesuai izin.

## Halaman yang diselesaikan

| Area | Halaman | Hasil |
|---|---|---|
| Dashboard | Admin, Sales, Direktur | Statistik aktual, deskripsi cakupan metrik, navigasi sesuai role, empat kartu responsif |
| Dashboard Admin | Aktivitas terbaru | Lima order aktual beserta pelanggan, sales, tanggal, status, total, dan tautan detail |
| Data Sales | Daftar, tambah, edit | Pencarian, pagination, form bersama, validasi, konfirmasi nonaktif/aktif kembali |
| Data Produk | Daftar, tambah, edit | Harga/stok/satuan terbaca; form edit yang sebelumnya kosong sudah berfungsi dengan endpoint lama |
| Data Pelanggan | Daftar, tambah, edit | Identitas, toko, kontak dan alamat konsisten; konfirmasi nonaktif |
| Order Sales | Daftar, tambah, edit, detail | Baris produk dinamis, stok/harga, estimasi subtotal/total, pemulihan input/error, detail dan cetak |
| Transaksi Admin | Daftar, detail/validasi | Filter status lama, informasi pelanggan/sales, harga historis, konfirmasi konsekuensi stok dan WhatsApp |

Total: **18 halaman bisnis**. Halaman detail master data baru tidak dibuat karena tidak tersedia dalam route saat ini.

Komponen bersama tambahan: format rupiah, ringkasan validasi, aksi formulir, toolbar pencarian, aksi record dengan konfirmasi, serta empty state tabel. Form tambah/edit memakai partial bersama. Daftar dan detail order/transaksi menggunakan partial yang sama dengan aksi sesuai role.

Tabel dapat digeser secara horizontal dan diakses keyboard. Form memakai label, indikator wajib, keterkaitan error, focus state, serta loading/pencegahan submit ganda dari Phase 1. Format tanggal menggunakan locale Indonesia; angka rupiah mempertahankan pecahan bila ada. Nomor telepon ditampilkan sebagai teks untuk mempertahankan nol awal dan nilai yang tersimpan.

Statistik memakai singkatan `rb`, `jt`, atau `miliar` bila panjang, dengan nilai penuh pada tooltip hover/fokus dan label aksesibel, sesuai kelonggaran Bagian 4.4 `design.md`. Tampilan ringkas dibulatkan; query pendapatan dan nilai penuh tetap tidak dibulatkan. Ketentuan inti `design.md` tidak diubah.

## Perubahan DashboardController

Struktur aktual mendukung kebutuhan tanpa perubahan schema:

- Model `Penjualan` menggunakan tabel `penjualan`, primary key `id_penjualan`, kolom nominal `total_penjualan` bertipe `decimal(12,2)`, dan `status_penjualan`.
- Relasi `pelanggan` menggunakan `id_pelanggan`; relasi `user` menggunakan `id_user`. Tidak tersedia `created_at`.
- Pendapatan Admin: `SUM(total_penjualan)` hanya untuk status `selesai`, seluruh periode. Tidak menyebut metrik sebagai pendapatan hari ini.
- Lima order terbaru: `ORDER BY id_penjualan DESC LIMIT 5`, konsisten dengan urutan pada daftar transaksi. Ini berarti order yang terakhir dicatat, bukan pengurutan tanggal bisnis.
- Relasi pelanggan dan user dimuat melalui eager loading dengan kolom yang diperlukan, untuk menghindari query per baris. Maksimal tiga query untuk daftar terbaru beserta relasinya, ditambah satu agregasi pendapatan.
- Query lama dan cakupan Sales/Direktur tetap dipertahankan. Tes memverifikasi Sales hanya memperoleh ringkasan order miliknya.
- Status lama `menunggu_konfirmasi` ditemukan dalam data aktual. Ditampilkan sebagai badge netral dengan teks; tidak ditambahkan sebagai transisi baru atau aksi baru.

## Daftar file Phase 2

Diubah:

- `app/Http/Controllers/DashboardController.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `resources/js/ui/interactions.js`
- `resources/views/components/ui/stat-card.blade.php`
- `resources/views/dashboard/admin.blade.php`
- `resources/views/dashboard/sales.blade.php`
- `resources/views/dashboard/direktur.blade.php`
- `resources/views/sales/index.blade.php`
- `resources/views/sales/create.blade.php`
- `resources/views/sales/edit.blade.php`
- `resources/views/produk/index.blade.php`
- `resources/views/produk/create.blade.php`
- `resources/views/produk/edit.blade.php`
- `resources/views/pelanggan/index.blade.php`
- `resources/views/pelanggan/create.blade.php`
- `resources/views/pelanggan/edit.blade.php`
- `resources/views/order/index.blade.php`
- `resources/views/order/create.blade.php`
- `resources/views/order/edit.blade.php`
- `resources/views/order/show.blade.php`
- `resources/views/transaksi/index.blade.php`
- `resources/views/transaksi/show.blade.php`

Dibuat:

- `resources/js/pages/order-form.js`
- `resources/views/components/ui/money.blade.php`
- `resources/views/components/ui/validation-summary.blade.php`
- `resources/views/components/ui/form-actions.blade.php`
- `resources/views/components/ui/search-toolbar.blade.php`
- `resources/views/components/ui/record-action.blade.php`
- `resources/views/components/ui/list-empty.blade.php`
- `resources/views/sales/_form.blade.php`
- `resources/views/produk/_form.blade.php`
- `resources/views/pelanggan/_form.blade.php`
- `resources/views/order/_form.blade.php`
- `resources/views/order/_product-row.blade.php`
- `resources/views/shared/sales-list.blade.php`
- `resources/views/shared/sale-detail.blade.php`
- `tests/Feature/RedesignUiTest.php`
- `docs/phase-2.md`

Vite juga menghasilkan manifest/aset build pada `public/build/` sesuai konfigurasi proyek. File hasil build tidak di-commit.

## Validasi dan batas pengujian

| Pemeriksaan | Hasil / batas |
|---|---|
| `npm run build` | Lulus; Vite membangun CSS, JavaScript dan font lokal |
| Kompilasi Blade | 58 view dikompilasi; direktori kompilasi QA berada di `/tmp` |
| Sintaks PHP | 79 file PHP aplikasi, tes, dan hasil kompilasi diperiksa; tanpa error sintaks |
| Tes regresi Phase 2 | 7 tes, 111 assertion lulus; menggunakan schema khusus SQLite `:memory:` dan HTTP fake |
| CRUD dan status | Create/update/nonaktif master data, aktif kembali Sales, create/update order, validasi transaksi, stok, pencegahan validasi ulang diuji melalui request Laravel pada SQLite |
| Authorization | Menu per role, penolakan akses role, kepemilikan order, dan larangan edit order terproses diuji pada SQLite |
| Pencarian/pagination | Kombinasi pencarian/status, halaman kedua, pelestarian query, dan cakupan Sales lulus |
| Data nyata | Controller dirender menggunakan model aktual dalam transaksi MySQL `READ ONLY`, kemudian `ROLLBACK`; pendapatan cocok dengan SQL pembanding dan lima ID terbaru cocok dengan query terpisah |
| Browser | Lulus 144 kombinasi (24 tampilan × 6 ukuran) tanpa overflow halaman/konten; CSS/JS terverifikasi termuat dan tidak ada error JavaScript. Preview HTML hasil render, bukan server Laravel live; aset build dilayani langsung oleh harness dan request submit dicegat sebagai mock. Chromium pada lebar 320, 390, 768, 1024, 1280, 1440px |
| Interaksi browser | Tambah/hapus baris, indeks unik, harga desimal, nama field POST, loading/double-submit, error/old input, dialog batal/setuju, dan media cetak diperiksa |
| Tes bawaan | Unit Example lulus. Feature Example gagal: mengharapkan `/` = 200, sedangkan route lama melakukan redirect login = 302 |
| Peringatan PHP | Dua deprecation PHP 8.5 pada konstanta `PDO::MYSQL_ATTR_SSL_CA` di `config/database.php:61` dan `:81`; tidak diubah dalam scope ini |

Perintah tes khusus yang dapat diulang (SQLite tersedia pada lingkungan ini):

```sh
APP_CONFIG_CACHE=/tmp/halus-no-config-cache.php DB_CONNECTION=sqlite DB_DATABASE=:memory: vendor/bin/phpunit tests/Feature/RedesignUiTest.php
```

Test class mengganti koneksi default ke `ui_testing`, hanya mengizinkan SQLite `:memory:`, memeriksa koneksi tersebut sebelum membuat tabel, dan memalsukan request Fonnte. Tabel dan record sintetis hanya hidup selama tes. Tidak memakai `RefreshDatabase`, migration produksi atau seeder.

Preview QA terdiri dari 18 halaman, tiga kondisi pencarian kosong, satu kondisi error berdasarkan record aktual, dan dua fixture order sintetis. Fixture tidak masuk aplikasi atau MySQL. Screenshot dan harness sementara berada di `/tmp/halus-phase2-preview/` serta `/tmp/halus-phase2-*.php` / `.mjs`.

**Batasan:** tidak melakukan submit CRUD pada MySQL nyata; tidak menguji pengiriman WhatsApp sebenarnya; SQLite tidak membuktikan perilaku lock/concurrency MySQL; browser hanya Chromium desktop emulation, belum perangkat fisik/Safari/Firefox atau audit screen reader menyeluruh. Form login/layout Phase 1 dipertahankan, bukan diuji ulang sebagai alur login live pada fase ini.

## Kendala dan pekerjaan berikutnya

- Data aktual tidak memiliki produk aktif dengan stok lebih dari nol. Form order menampilkan peringatan dan penyimpanan nonaktif ketika katalog tidak tersedia. Perubahan stok hanya dapat dilakukan pengguna berwenang melalui proses operasional; data tidak diubah untuk membuat QA lulus.
- Status `menunggu_konfirmasi` memerlukan klarifikasi bisnis jika kelak ingin dinormalisasi. Phase 2 hanya menampilkannya.
- Tidak ada modul/detail bisnis baru, grafik, ekspor, notifikasi interaktif, atau transisi status tambahan.
- Error server/404/403 belum dibuat sebagai halaman kustom baru; respons authorization lama tetap berlaku.

Rekomendasi Phase 3 (menunggu persetujuan):

| Pekerjaan | Manfaat / area | Backend atau database | Prioritas |
|---|---|---|---|
| UAT pada staging terisolasi dengan data representatif | Menguji alur nyata dari login hingga validasi dan cetak tanpa data utama; seluruh modul | Database staging tersendiri, tanpa perubahan schema utama; provider WhatsApp wajib tetap mock/nonaktif | Tinggi |
| Audit aksesibilitas/perangkat lanjutan | Memastikan keyboard, screen reader, Safari/Firefox, dan layar sentuh pada seluruh halaman | Tidak | Tinggi |
| Penyelarasan tes bawaan dan kompatibilitas PHP | Memperbaiki ekspektasi redirect pada ExampleTest dan deprecation konfigurasi database | Tes/config; perubahan config memerlukan persetujuan terpisah | Sedang |
| Halaman error yang konsisten | Respons 403/404/500 memakai bahasa/desain bersama | Dapat berupa Blade error views, tanpa mengubah keputusan authorization | Sedang |
| Review endpoint WhatsApp lama | Meninjau `/test-wa` dan cakupan akses `kirim-nota` yang sudah ada; tidak dipanggil saat QA | Memerlukan persetujuan backend/route tersendiri | Tinggi |

Fase berikutnya sebaiknya difokuskan pada UAT dan penyempurnaan di atas karena master data dan transaksi sudah termasuk persetujuan Phase 2 terbaru. Tidak ada pekerjaan Phase 3 yang otomatis dijalankan.
