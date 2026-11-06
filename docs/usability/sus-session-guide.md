# Panduan Pelaksanaan Sesi Usability Testing (Session Guide)

## 1. Peran & Protokol Fasilitator

Fasilitator bertindak sebagai pengamat netral selama pengujian. Fasilitator bertanggung jawab memastikan lingkungan pengujian siap, memandu pengantar, mengamati interaksi tanpa mengintervensi alur berpikir alami pengguna, dan mencatat catatan kualitatif.

### Pedoman Netralitas Fasilitator
1. **Hindari Kalimat Mengarahkan (*No Leading Prompts*)**: Jangan pernah menanyakan *"Bukankah tombol ini sangat mudah ditemukan?"* atau *"Apakah alur ini terasa simpel?"*.
2. **Prinsip *Think-Aloud***: Dorong partisipan untuk menyuarakan apa yang mereka lihat, cari, atau pikirkan saat bernavigasi (*"Silakan suarakan apa yang sedang Anda perhatikan di layar"*).
3. **Bantuan saat Mengalami Kebuntuan (*Stuck Protocol*)**:
   - Jika partisipan tampak bingung, amati terlebih dahulu tanpa menyela selama 1–2 menit.
   - Hanya berikan petunjuk minimal jika partisipan telah terhenti total (*blocked*) selama lebih dari 3 menit atau secara eksplisit meminta bantuan.
   - Catat kejadian tersebut sebagai **Catatan Bantuan Fasilitator** pada lembar observasi.
4. **Kemandirian Kuesioner**: Saat partisipan mengisi kuesioner SUS digital, fasilitator tidak boleh berada dalam posisi mendikte, mengomentari nilai, atau mengklarifikasi arti kata secara subyektif.

---

## 2. Naskah Pengantar untuk Partisipan (*Briefing Script*)

Bacakan teks pengantar berikut sebelum sesi dimulai:

> *"Selamat datang dan terima kasih atas kesediaan Anda meluangkan waktu untuk berpartisipasi dalam pengujian sistem informasi Simple Sales. Tujuan dari sesi ini adalah untuk mengevaluasi kebergunaan dan kemudahan antarmuka aplikasi Simple Sales yang sedang kami kembangkan untuk PT. Halus Ciptanadi.*
>
> *Perlu kami tegaskan bahwa pengujian ini sama sekali BUKAN untuk menguji kemampuan, kepintaran, atau performa Anda. Sistem inilah yang sedang diuji. Tidak ada tindakan yang salah dalam pengujian ini. Jika Anda menemukan kesulitan, kebingungan, atau hal yang tidak sesuai ekspektasi Anda, hal tersebut merupakan masukan yang sangat berharga bagi penyempurnaan sistem.*
>
> *Kami akan meminta Anda menjalankan serangkaian skenario tugas operasional yang biasa dilakukan sesuai peran Anda. Selama menjalankan tugas, kami sangat menghargai jika Anda bersedia menyuarakan apa yang sedang Anda pikirkan, cari, atau harapkan di layar (think-aloud).*
>
> *Setelah seluruh tugas selesai, Anda akan diminta mengisi kuesioner 10 pertanyaan secara mandiri pada formulir digital yang kami sediakan, diikuti dengan tanya jawab singkat terkait pengalaman Anda.*
>
> *Apakah ada pertanyaan sebelum kita mulai?"*

---

## 3. Kesiapan Lingkungan Pengujian & Data Uji

### 3.1 Parameter Lingkungan
- **Aplikasi URL**: `http://localhost:8000` (atau IP jaringan lokal untuk perangkat smartphone, misalnya `http://192.168.1.x:8000`).
- **Database**: MySQL `simple_sales`.
- **Aset Frontend**: Kompilasi produksi Vite (`npm run build`).
- **Gateway WhatsApp**: Keamanan terjamin (`FONNTE_TOKEN` bernilai null / tidak diset pada file `.env`). Sistem mencatat payload notifikasi secara lokal tanpa mengirim pesan nyata ke pelanggan luar.

### 3.2 Kredensial Akun Pengujian

| Peran | Username | Password | Keterangan Akun |
| :---: | :---: | :---: | :--- |
| **Admin** | `admin` | `admin123` | Administrator operasional master data & transaksi |
| **Sales** | `sales` | `sales123` | Sales lapangan utama PT. Halus Ciptanadi |
| **Sales (Cadangan)** | `sales2` | `sales123` | Akun sales kedua jika pengujian dilakukan paralel |
| **Direktur** | `direktur` | `direktur123` | Akun pimpinan / manajemen eksekutif |

---

## 4. Skenario Tugas Operasional Aktual per Peran

Setiap skenario disusun berdasarkan fungsionalitas nyata yang telah lolos verifikasi Black Box pada cabang `dev`. Seluruh rute menggunakan endpoint aktif aplikasi.

---

### 4.1 Skenario Tugas Admin (10 Tugas)
**Perangkat**: Desktop / Laptop ($\ge 1366 \times 768$ px)

| Kode | Nama Skenario | Titik Mulai | Instruksi Aksi bagi Partisipan | Kriteria Keberhasilan Tugas |
| :---: | :--- | :--- | :--- | :--- |
| **ADM-T01** | Login Admin | `/login` | Masukkan username `admin` dan password `admin123`. | Berhasil masuk ke halaman `/dashboard` Admin. |
| **ADM-T02** | Cari & Filter Produk | `/produk` | Cari produk dengan nama/kode tertentu dan terapkan filter status `aktif`. | Daftar produk terfilter sesuai kriteria pencarian tanpa galat. |
| **ADM-T03** | Tambah Produk Baru | `/produk/tambah` | Masukkan data produk baru (kode unik, nama, kategori, satuan, harga, stok awal) lalu simpan. | Pesan sukses muncul dan data produk baru tampil pada tabel. |
| **ADM-T04** | Cari & Tinjau Pelanggan | `/pelanggan` | Cari data toko pelanggan dan periksa nomor kontak yang tersimpan. | Informasi kontak dan alamat toko pelanggan terlihat jelas. |
| **ADM-T05** | Tambah Akun Sales | `/sales/tambah` | Daftarkan satu sales baru dengan nama, username, nomor HP, alamat, dan password. | Akun sales baru tersimpan dan berstatus aktif pada tabel sales. |
| **ADM-T06** | Filter Transaksi Masuk | `/transaksi` | Buka menu Transaksi dan filter data dengan status `menunggu_validasi`. | Menampilkan hanya transaksi pesanan yang menunggu persetujuan admin. |
| **ADM-T07** | Periksa Detail & Foto Toko | Detail Transaksi | Buka salah satu transaksi berstatus menunggu validasi, lalu periksa foto toko dari sales. | Modal/thumbnail foto fisik toko terbuka dengan jelas dan dapat ditutup. |
| **ADM-T08** | Validasi Transaksi | Detail Transaksi | Klik tombol `Validasi Transaksi` dan setujui konfirmasi modal. | Status transaksi berhasil berubah menjadi `diproses`. |
| **ADM-T09** | Kirim Pesanan | Detail Transaksi | Klik tombol `Kirim Pesanan` untuk pesanan yang telah diproses. | Status transaksi berganti menjadi `dikirim`. |
| **ADM-T10** | Cetak Nota Transaksi | Detail Transaksi | Klik tombol `Cetak Nota` pada transaksi berstatus `dikirim`. | Jendela browser print preview terbuka dengan layout nota digital rapi. |

---

### 4.2 Skenario Tugas Sales (11 Tugas)
**Perangkat Prioritas**: Smartphone Fisik (lebar 360px – 412px) | *Fallback: DevTools Mobile*

| Kode | Nama Skenario | Titik Mulai | Instruksi Aksi bagi Partisipan | Kriteria Keberhasilan Tugas |
| :---: | :--- | :--- | :--- | :--- |
| **SLS-T01** | Login Sales | `/login` | Masukkan username `sales` dan password `sales123` pada ponsel. | Berhasil masuk ke dashboard Sales dengan tampilan responsif. |
| **SLS-T02** | Tambah Pelanggan Baru | `/pelanggan/tambah` | Daftarkan pelanggan toko baru saat kunjungan lapangan (nama toko, kontak, alamat). | Toko baru berhasil disimpan dan langsung tersedia untuk dipesan. |
| **SLS-T03** | Tambah Order Baru | `/order/tambah` | Pilih toko pelanggan yang telah dibuat, lalu pilih minimal 2 produk dengan jumlah kuantiti valid. | Kuantiti dan hitungan subtotal/total muncul otomatis secara dinamis. |
| **SLS-T04** | Unggah Foto Toko | Form `/order/tambah` | Ambil atau pilih file foto toko fisik pelanggan (format gambar JPG/PNG $\le$ 5MB). | Pratinjau nama file atau gambar foto toko muncul pada form. |
| **SLS-T05** | Simpan Order | Form `/order/tambah` | Tekan tombol `Simpan Order`. | Pesanan tersimpan dengan status `menunggu_validasi` dan pesan sukses muncul. |
| **SLS-T06** | Tinjau Daftar Order | Menu `/order` | Buka daftar pesanan dan gunakan filter status `menunggu_validasi`. | Hanya menampilkan order milik sales yang bersangkutan. |
| **SLS-T07** | Ubah Pesanan Sebelum Validasi | `/order/{id}/edit` | Ubah kuantiti produk pada pesanan yang masih berstatus `menunggu_validasi`. | Data pesanan berhasil diperbarui dengan total baru yang sesuai. |
| **SLS-T08** | Pantau Status Order | Detail Order | Buka rincian order yang telah dikirim oleh admin (`dikirim`). | Lencana status menampilkan informasi bahwa barang sedang dikirim. |
| **SLS-T09** | Unggah Bukti Penyerahan | Detail Order | Klik `Upload Bukti Pengiriman` lalu unggah foto bukti penyerahan barang di toko. | Foto bukti penyerahan tersimpan dan thumbnail bukti tampil di halaman detail. |
| **SLS-T10** | Selesaikan Pesanan | Detail Order | Klik tombol `Selesaikan Order` setelah foto bukti terunggah. | Status pesanan berganti menjadi `selesai` dan data terkunci. |
| **SLS-T11** | Tinjau Nota Transaksi | Detail Order | Buka tampilan nota digital pesanan yang sudah selesai. | Dokumen nota digital tampil bersih dan mudah dibaca pada layar ponsel. |

---

### 4.3 Skenario Tugas Direktur (7 Tugas)
**Perangkat**: Desktop / Laptop / Tablet ($\ge 1280 \times 800$ px)

| Kode | Nama Skenario | Titik Mulai | Instruksi Aksi bagi Partisipan | Kriteria Keberhasilan Tugas |
| :---: | :--- | :--- | :--- | :--- |
| **DIR-T01** | Login Direktur | `/login` | Masukkan username `direktur` dan password `direktur123`. | Masuk ke dashboard eksekutif Direktur. |
| **DIR-T02** | Pantau Ringkasan Dashboard | `/dashboard` | Periksa 4 kartu metrik utama: *Total Pendapatan*, *Transaksi Selesai*, *Produk Aktif*, dan *Stok Rendah* (stok $\le$ 10). | Nilai metrik dan deskripsi ringkasan terbaca jelas tanpa tombol manipulasi data. |
| **DIR-T03** | Buka Laporan Penjualan | `/laporan-penjualan` | Masuk ke menu Laporan Penjualan dan tinjau rekap transaksi selesai beserta total nilai rupiah. | Tabel transaksi selesai dan kartu ringkasan total pendapatan tampil lengkap. |
| **DIR-T04** | Terapkan Filter & Atur Ulang | `/laporan-penjualan` | Terapkan filter berdasarkan tanggal, pelanggan, atau sales, lalu klik `Atur Ulang`. | Filter memperbarui data sesuai kriteria dan tombol atur ulang mengembalikan ke kondisi semula. |
| **DIR-T05** | Periksa Detail Transaksi | Tabel Laporan | Klik tombol `Detail` pada salah satu baris transaksi selesai (`/laporan-penjualan/{id}`). | Rincian produk yang dipesan, harga satuan, total, dan foto bukti penyerahan tampil rapi. |
| **DIR-T06** | Cetak Laporan Penjualan | `/laporan-penjualan` | Klik tombol `Cetak Laporan`. | Browser print preview memunculkan header PT. Halus Ciptanadi dan filter aktif secara bersih (kontrol UI tersembunyi). |
| **DIR-T07** | Buka & Cetak Laporan Stok | `/laporan-stok` | Buka menu Laporan Stok, periksa kolom (*No, Kode Produk, Nama Produk, Kategori, Stok, Satuan, Status*), gunakan pagination, lalu klik `Cetak Laporan`. | Tabel stok lengkap, navigasi halaman bekerja, dan jendela cetak menampilkan format dokumen resmi. |

---

## 5. Lembar Observasi Fasilitator & Tanya Jawab Kualitatif

### 5.1 Catatan Observasi Selama Pengerjaan Tugas
Untuk setiap tugas yang dijalankan partisipan, catat:
1. **Status Penyelesaian**: Selesai Mandiri / Selesai dengan Bantuan Minimal / Gagal / Dilewati.
2. **Titik Keraguan (*Hesitation Points*)**: Momen di mana partisipan berhenti mencari tombol, ragu memilih opsi, atau mengklik elemen yang tidak dapat diklik.
3. **Komentar Spontan**: Ungkapan verbal yang keluar saat menggunakan antarmuka (misal: *"Saya kira tombolnya di sebelah kanan"* atau *"Font ini agak kecil di HP"*).

### 5.2 Panduan Tanya Jawab Kualitatif Pascates (5–10 Menit)
Setelah partisipan selesai mengisi kuesioner SUS digital, ajukan pertanyaan terbuka berikut:
1. *"Bagian atau alur mana dari aplikasi ini yang menurut Anda paling lancar dan mudah digunakan?"*
2. *"Apakah ada bagian yang membuat Anda bingung, ragu-ragu, atau memperlambat pekerjaan Anda?"*
3. *"Apakah ada label, istilah, atau tampilan yang menurut Anda kurang jelas maknanya?"*
4. *"Jika ada satu hal yang paling ingin Anda tingkatkan dari antarmuka ini, hal apakah itu?"*
