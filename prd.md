# Product Requirements Document (PRD)

## Digitalisasi Workflow dengan Fitur Integrasi Input Sales dan Admin pada PT. Halus Ciptanadi

**Versi:** 0.3
**Status:** Working Product Baseline  
**Repository:** `lan-zip/simple-sales`  
**Branch Development:** `dev`

---

## 1. Ringkasan Produk

Sistem ini adalah aplikasi berbasis web untuk mendigitalisasi workflow transaksi penjualan PT. Halus Ciptanadi.

Masalah utama pada workflow sebelumnya adalah Sales mencatat pesanan customer secara manual menggunakan nota fisik, kemudian Admin melakukan input ulang ke sistem. Proses tersebut menyebabkan duplikasi input, keterlambatan, risiko kesalahan pencatatan, dan keterbatasan monitoring transaksi secara real time.

Sistem yang dibangun memungkinkan Sales memasukkan order langsung melalui website dari lapangan. Data tersebut kemudian dapat diproses dan divalidasi oleh Admin. Status transaksi diperbarui sepanjang proses pemenuhan order sampai selesai, dilengkapi dokumentasi foto toko dan bukti penyerahan, serta menghasilkan nota digital yang dapat dikirim kepada customer melalui WhatsApp.

Direktur menggunakan sistem untuk memonitor informasi penjualan dan stok.

---

## 2. Product Goal

Membangun workflow transaksi penjualan digital yang menghubungkan proses:

**Sales input order → Admin validasi/proses → Pengiriman → Sales konfirmasi penyelesaian → Nota digital → Monitoring**

Tujuan utamanya:

1. Menghilangkan input ulang order oleh Admin.
2. Mempercepat pencatatan transaksi dari lapangan.
3. Mengurangi kesalahan input data.
4. Menyediakan status transaksi yang jelas.
5. Menyediakan dokumentasi transaksi.
6. Mempermudah Admin dalam memproses transaksi.
7. Mempermudah Direktur dalam memonitor penjualan dan stok.
8. Mendukung pembuatan dan pengiriman nota digital.

---

## 3. Target Users

### 3.1 Primary User — Sales

Sales merupakan pengguna utama karena menggunakan sistem langsung ketika berada di lapangan.

Kebutuhan utama Sales:

- Login.
- Melihat data produk.
- Melihat dan menambah customer.
- Membuat order.
- Mengubah order yang masih menunggu validasi.
- Melihat detail order.
- Mengunggah foto toko.
- Melihat status order.
- Mengunggah foto bukti penyerahan.
- Menandai order selesai setelah bukti penyerahan tersedia.
- Melihat nota digital.

### 3.2 Secondary User — Admin

Admin bertanggung jawab terhadap pengelolaan operasional transaksi.

Kebutuhan utama Admin:

- Login.
- Mengelola produk.
- Mengelola data Sales.
- Mengelola customer.
- Melihat transaksi.
- Memvalidasi transaksi.
- Memproses perubahan status transaksi.
- Melihat detail transaksi dan nota.
- Mengirim atau memicu pengiriman nota melalui WhatsApp.
- Monitoring transaksi Sales.

### 3.3 Secondary User — Direktur

Direktur merupakan pengguna monitoring.

Kebutuhan utama Direktur:

- Login.
- Melihat informasi penjualan.
- Melihat laporan penjualan.
- Melihat laporan stok.
- Melakukan monitoring tanpa terlibat langsung dalam input transaksi.

---

## 4. Product Scope

### 4.1 MVP — Must Have

Fitur berikut wajib tersedia untuk mencapai product completion:

1. Login dan role-based access.
2. Data produk.
3. Data customer.
4. Input order Sales.
5. Edit order selama masih menunggu validasi.
6. Detail order.
7. Upload foto toko.
8. Admin melihat dan memvalidasi transaksi.
9. Workflow status:
   - `menunggu_validasi`
   - `diproses`
   - `dikirim`
   - `selesai`
10. Upload foto bukti penyerahan.
11. Penyelesaian order setelah bukti penyerahan tersedia.
12. Nota digital.
13. Pengiriman nota melalui WhatsApp.
14. Monitoring transaksi inti.
15. Logout.

### 4.2 Nice to Have

Fitur berikut bukan blocker MVP utama:

- Pengelolaan Sales yang lebih lengkap.
- Search dan filtering yang lebih advanced.
- Print report.
- Dashboard dan summary tambahan.
- Penyempurnaan UX operasional.

### 4.3 Out of Scope

Fitur berikut tidak termasuk dalam scope produk ini:

- Akuntansi detail.
- Laporan laba rugi.
- Jurnal dan pembukuan keuangan.
- Integrasi Shopee/Tokopedia atau marketplace lain.
- Integrasi tracking ekspedisi.
- Sistem reward Sales.
- Modul lain yang tidak berkaitan dengan workflow penjualan yang telah ditentukan.

---

## 5. Core Workflow

Workflow utama transaksi:

```text
Sales membuat order
        ↓
menunggu_validasi
        ↓
Admin validasi
        ↓
diproses
        ↓
Admin mengubah status
        ↓
dikirim
        ↓
Sales mengunggah bukti penyerahan
        ↓
Sales menyelesaikan order
        ↓
selesai
        ↓
Nota digital / pengiriman WhatsApp
```

### Aturan workflow

#### `menunggu_validasi`

Status awal ketika Sales menyimpan order baru.

Sales dapat:

- Melihat order.
- Mengubah order selama belum divalidasi.

Admin dapat:

- Melihat order.
- Melihat detail.
- Memvalidasi order.

#### `diproses`

Status ketika transaksi telah divalidasi dan masuk tahap proses.

Admin dapat:

- Melihat transaksi.
- Mengubah status transaksi sesuai workflow.

#### `dikirim`

Menunjukkan bahwa transaksi telah memasuki tahap pengiriman/penyerahan kepada customer.

Sales dapat:

- Melihat status.
- Mengunggah bukti penyerahan.

#### `selesai`

Order hanya dapat menjadi selesai apabila bukti penyerahan telah tersedia.

Sales melakukan:

1. Upload foto bukti penyerahan.
2. Menyelesaikan order.

Sistem harus menolak penyelesaian order apabila bukti penyerahan belum tersedia.

---

## 6. Functional Requirements

### FR-01 — Authentication

Sistem harus menyediakan login dengan tiga role:

- Sales
- Admin
- Direktur

Setiap pengguna hanya dapat mengakses fitur sesuai role.

Sistem harus menyediakan logout.

### FR-02 — Product Management

Admin dapat:

- Menambah produk.
- Mencari produk.
- Mengubah produk.
- Menonaktifkan produk.

Produk minimal mempunyai informasi:

- Kode produk.
- Nama produk.
- Kategori.
- Harga.
- Stok.
- Satuan.
- Status aktif/nonaktif.

### FR-03 — Sales Management

Admin dapat:

- Menambah Sales.
- Mencari Sales.
- Mengubah Sales.
- Menonaktifkan Sales.

### FR-04 — Customer Management

Sistem menyediakan pengelolaan customer.

Fungsi minimal:

- Tambah customer.
- Cari customer.
- Ubah customer.
- Nonaktifkan customer.

Hak akses final untuk masing-masing role harus mengikuti aturan permission yang telah disepakati dan tidak boleh memberikan akses lebih luas dari kebutuhan role.

### FR-05 — Create Sales Order

Sales dapat membuat order dengan minimal:

- Customer.
- Produk.
- Jumlah produk.
- Harga.
- Detail transaksi.
- Foto toko.

Ketika order berhasil dibuat:

```text
status = menunggu_validasi
```

Sistem harus melakukan validasi terhadap data wajib dan jumlah produk.

### FR-06 — Edit Sales Order

Sales hanya dapat mengubah order yang masih:

```text
menunggu_validasi
```

Order yang telah diproses tidak boleh diedit seperti order baru.

### FR-07 — Order Detail

Sistem harus menampilkan detail:

- Nomor order.
- Tanggal.
- Customer.
- Sales.
- Produk.
- Jumlah.
- Harga satuan.
- Subtotal.
- Total transaksi.
- Foto toko.
- Status transaksi.
- Bukti penyerahan jika tersedia.
- Nota digital.

### FR-08 — Store Photo

Sales harus dapat mengunggah foto toko ketika membuat order.

Foto harus tersimpan sebagai bagian dari data transaksi/order.

Foto toko dapat dilihat kembali oleh user yang mempunyai akses terhadap transaksi tersebut.

### FR-09 — Transaction Validation

Admin dapat:

- Mencari transaksi.
- Melihat detail.
- Melihat nota.
- Melakukan validasi transaksi.

Validasi harus dilakukan terhadap order yang masih:

```text
menunggu_validasi
```

### FR-10 — Transaction Status

Status utama sistem:

```text
menunggu_validasi
diproses
dikirim
selesai
```

Sistem harus menerapkan state transition yang valid dan menolak perubahan status yang tidak sesuai workflow.

Status harus konsisten antara:

- Database.
- Backend.
- Dashboard.
- List transaksi.
- Detail transaksi.
- Badge/status UI.

### FR-11 — Delivery Proof

Sales dapat mengunggah foto bukti penyerahan ketika transaksi sudah berada pada tahap:

```text
dikirim
```

### FR-12 — Complete Order

Sales dapat menyelesaikan order setelah foto bukti penyerahan tersimpan.

Tanpa bukti penyerahan:

```text
status → selesai
```

harus ditolak.

Setelah selesai:

- Status berubah menjadi `selesai`.
- Tanggal selesai tersimpan.
- Bukti penyerahan tetap tersedia dalam detail transaksi.

### FR-13 — Digital Note

Sistem dapat menghasilkan nota digital berdasarkan data transaksi yang tersimpan.

Untuk MVP, nota digital menggunakan **browser-print HTML** sebagai output customer-facing. PDF atau file download khusus belum menjadi requirement MVP.

Nota minimal menampilkan:

- Nomor transaksi.
- Tanggal.
- Customer.
- Sales.
- Detail produk.
- Jumlah.
- Harga.
- Subtotal.
- Total transaksi.
- Status transaksi.

Ketentuan print:

- Tombol **Cetak Nota** tersedia untuk transaksi berstatus `diproses`, `dikirim`, atau `selesai`.
- Tombol **Cetak Nota** tidak tersedia ketika transaksi masih `menunggu_validasi`.
- Layout print harus customer-facing dan tidak menampilkan navigation, form/action internal, foto toko, atau foto bukti penyerahan.
- Setelah transaksi mencapai `selesai`, nota customer-facing dianggap final dan order tidak dapat diedit lagi.

### FR-14 — WhatsApp Note

Nota digital dapat dikirim kepada customer melalui WhatsApp menggunakan layanan pihak ketiga.

Untuk MVP, WhatsApp tetap menggunakan **pesan teks**. Pengiriman file/PDF sebagai attachment bukan requirement Issue #4.

Nomor WhatsApp customer harus mempunyai satu sumber data yang konsisten.

Sistem tidak boleh mengandalkan field nomor WhatsApp yang berbeda-beda tanpa aturan source of truth yang jelas.

Trigger pengiriman WhatsApp masih merupakan Open Question sampai keputusan produk ditetapkan.

### FR-15 — Sales Report

Sistem menyediakan laporan penjualan untuk Direktur.

Laporan minimal berdasarkan data transaksi yang telah tersimpan.

### FR-16 — Stock Report

Sistem menyediakan laporan stok untuk Direktur.

### FR-17 — Print Report

Sistem menyediakan kemampuan mencetak laporan sesuai kebutuhan sistem.

---

## 7. Role & Permission Matrix

| Fitur | Sales | Admin | Direktur |
|---|---:|---:|---:|
| Login | ✓ | ✓ | ✓ |
| Dashboard | ✓ | ✓ | ✓ |
| Lihat Produk | ✓ | ✓ | ✓* |
| Kelola Produk | — | ✓ | — |
| Kelola Sales | — | ✓ | — |
| Lihat Customer | ✓ | ✓ | — |
| Tambah Customer | ✓ | ✓ | — |
| Edit Customer | — | ✓ | — |
| Nonaktifkan Customer | — | ✓ | — |
| Buat Order | ✓ | — | — |
| Edit Order Menunggu Validasi | ✓ | — | — |
| Lihat Detail Order | ✓ | ✓ | ✓* |
| Upload Foto Toko | ✓ | — | — |
| Validasi Transaksi | — | ✓ | — |
| Ubah Status Proses | — | ✓ | — |
| Upload Bukti Penyerahan | ✓ | — | — |
| Selesaikan Order | ✓ | — | — |
| Lihat Nota | ✓ | ✓ | ✓* |
| Kirim Nota WhatsApp | sesuai workflow | ✓ | — |
| Laporan Penjualan | — | sesuai kebutuhan | ✓ |
| Laporan Stok | — | sesuai kebutuhan | ✓ |
| Cetak Laporan | — | sesuai kebutuhan | ✓ |
| Logout | ✓ | ✓ | ✓ |

`*` akses monitoring mengikuti implementasi dashboard/report yang telah ditentukan.

---

## 8. Business Rules

### BR-01

Order baru selalu dimulai dari:

```text
menunggu_validasi
```

### BR-02

Order hanya dapat diedit sebelum divalidasi.

### BR-03

Validasi transaksi dilakukan oleh Admin.

### BR-04

Status tidak boleh berpindah secara sembarang.

Contoh:

```text
menunggu_validasi → selesai
```

harus ditolak.

### BR-05

Status `selesai` membutuhkan foto bukti penyerahan.

### BR-06

Foto toko merupakan dokumentasi saat Sales melakukan input order.

### BR-07

Foto bukti penyerahan merupakan dokumentasi saat order dikirim/diterima customer.

### BR-08

Data transaksi yang digunakan untuk nota harus berasal dari data transaksi tersimpan, bukan data manual terpisah.

### BR-09

Pengiriman WhatsApp harus menggunakan nomor customer yang berasal dari sumber data customer yang konsisten.

### BR-10

Transaksi yang selesai menjadi data yang dapat digunakan untuk kebutuhan laporan.

### BR-11

Nota customer-facing hanya dapat dicetak untuk transaksi berstatus `diproses`, `dikirim`, atau `selesai`.

### BR-12

Foto toko dan foto bukti penyerahan merupakan dokumentasi operasional dan tidak ditampilkan pada output nota customer-facing.

### BR-13

Untuk MVP, output nota digital adalah browser-print HTML. PDF atau file download khusus tidak diwajibkan.

### BR-14

WhatsApp tetap menggunakan pesan teks pada MVP. Attachment file/PDF bukan requirement Issue #4.

### BR-15

Setelah transaksi mencapai `selesai`, order dan nota customer-facing dianggap final dan order tidak dapat diedit.

---

## 9. Non-Functional Requirements

### NFR-01 — Platform

Sistem berbasis website.

### NFR-02 — Responsive

Sistem harus nyaman digunakan pada smartphone karena Sales menggunakan sistem ketika berada di lapangan.

Minimal workflow mobile yang harus usable:

- Login.
- Lihat customer.
- Buat order.
- Pilih produk.
- Upload foto.
- Lihat status.
- Upload bukti penyerahan.
- Selesaikan order.

### NFR-03 — Security

- Semua fitur harus berada di balik authentication kecuali route publik yang memang diperlukan.
- Role harus divalidasi di backend.
- User tidak boleh mendapatkan akses hanya dengan memanipulasi URL.
- Endpoint perubahan status harus memverifikasi state saat ini dan hak akses actor.
- Upload file harus divalidasi.

### NFR-04 — Data Integrity

Perubahan status dan transaksi harus konsisten antara database, backend, dan UI.

### NFR-05 — Usability

Sistem harus cukup mudah digunakan oleh Sales di lapangan.

Target usability:

```text
SUS ≥ 70
```

---

## 10. Technical Constraints

Stack mengikuti proposal dan keputusan produk:

- **Backend:** Laravel / PHP
- **Frontend:** Blade / HTML / JavaScript
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Development environment:** Laragon
- **Platform:** Responsive Web Application
- **WhatsApp:** Third-party WhatsApp service

Jangan mengganti stack utama tanpa keputusan eksplisit.

---

## 11. Current Implementation Audit Baseline

Hasil audit terhadap repository `lan-zip/simple-sales` menunjukkan:

### Sudah tersedia

- Authentication.
- Role middleware.
- Dashboard Admin.
- Dashboard Sales.
- Product management.
- Sales management.
- Customer management.
- Sales order creation.
- Sales order editing ketika waiting validation.
- Admin transaction listing.
- Admin transaction detail.
- Admin transaction validation.
- Basic WhatsApp integration.
- Responsive layout foundation.

### Gap / belum sesuai

#### P0

1. State machine transaksi belum lengkap.
2. Implementasi saat ini masih menggunakan status `tervalidasi` yang belum menjadi status utama dalam requirement proposal.
3. Transaksi belum mempunyai workflow lengkap sampai `selesai`.
4. Upload foto toko belum selesai di order flow.
5. Upload bukti penyerahan belum tersedia.
6. Digital note belum sepenuhnya sesuai kebutuhan final.
7. WhatsApp flow masih mempunyai inconsistency.
8. Terdapat route testing WhatsApp publik yang harus diamankan/dihapus.
9. Source nomor WhatsApp belum konsisten.
10. Permission customer perlu diperketat.
11. Environment database perlu disejajarkan dengan requirement MySQL.
12. Automated test untuk workflow kritis perlu ditambahkan.
13. Workflow Sales mobile belum melalui runtime QA.

#### P1

1. Laporan penjualan Direktur.
2. Laporan stok Direktur.
3. Print report.
4. Search/filter enhancement.
5. UX/accessibility review.
6. Black Box test execution.
7. SUS evaluation.

---

## 12. Acceptance Criteria

Fitur dianggap selesai apabila:

### Workflow

```text
Sales create order
→ Admin validate
→ Admin process
→ Admin ship
→ Sales upload proof
→ Sales complete
```

berhasil dilakukan end-to-end.

### Security

- User tidak dapat melewati state.
- User tidak dapat menjalankan action yang bukan haknya.
- URL/direct request tidak dapat digunakan untuk bypass permission.

### Data

- Status tersimpan dengan benar.
- Bukti foto tersimpan dan dapat ditampilkan.
- Nota menggunakan data transaksi yang benar.
- Data selesai tersedia untuk laporan.

### Quality

- Black Box test untuk seluruh scope berjalan sesuai expected result.
- Tidak terdapat P0/critical blocker.
- SUS ≥70.
- Workflow mobile Sales dapat digunakan secara nyata.

---

## 13. Definition of Done

Project dianggap **Done** apabila seluruh kondisi berikut terpenuhi:

1. Semua fitur MVP selesai.
2. Core workflow Sales → Admin → proses → kirim → selesai berjalan end-to-end.
3. Foto toko dan bukti penyerahan dapat digunakan.
4. Nota digital berfungsi.
5. WhatsApp flow berfungsi.
6. Permission tiap role benar.
7. Tidak terdapat P0/critical blocker.
8. Automated tests untuk workflow kritis tersedia dan lulus.
9. Black Box testing untuk scope selesai dan lulus.
10. SUS mencapai minimal 70.
11. Workflow Sales pada smartphone telah diuji secara nyata.
12. PRD dan implementasi tidak memiliki konflik requirement yang belum diketahui.

---

## 14. Resolved Product Decisions

Keputusan berikut sudah ditetapkan dan menjadi requirement produk yang dapat digunakan developer/AI agent.

### OQ-03 — Nota Digital

**Resolved:** Untuk MVP, nota digital cukup berupa **browser-print HTML**. PDF atau file download khusus belum diwajibkan.

### OQ-05 — Ketersediaan Cetak Nota

**Resolved:** Tombol **Cetak Nota** tersedia ketika transaksi berstatus:

- `diproses`
- `dikirim`
- `selesai`

Tidak tersedia pada `menunggu_validasi`.

### OQ-06 — Immutability Nota

**Resolved:** Setelah order mencapai `selesai`, nota customer-facing dianggap final dan order tidak dapat diedit.

### OQ-07 — WhatsApp Attachment

**Resolved:** MVP tetap menggunakan pesan WhatsApp berbasis teks. PDF/file attachment tidak termasuk scope Issue #4.

---

## 15. Open Questions

Requirement berikut masih membutuhkan keputusan produk dan tidak boleh ditebak oleh developer atau AI agent:

### OQ-01 — Permission Customer

**Decision: Sales tidak memiliki permission untuk mengedit atau menonaktifkan customer.**

Permission customer ditetapkan sebagai berikut:

| Action | Admin | Sales | Direktur |
|---|---:|---:|---:|
| Melihat / list customer | ✓ | ✓ | — |
| Menambah customer | ✓ | ✓ | — |
| Mengedit / update customer | ✓ | — | — |
| Menonaktifkan customer | ✓ | — | — |

Sales hanya diperbolehkan **melihat dan menambah customer**.

Pengeditan, perubahan data master customer, dan penonaktifan customer dibatasi kepada Admin untuk menjaga integritas data customer, termasuk nomor WhatsApp yang digunakan sebagai sumber data utama untuk notifikasi WhatsApp.

**Status:** Decided

### OQ-02 — WhatsApp Trigger

Apakah WhatsApp note:

- otomatis setelah transaksi divalidasi,
- otomatis setelah transaksi selesai,
- manual melalui tombol,
- atau kombinasi otomatis + manual?

### OQ-04 — Report Filter

Filter minimal laporan Direktur belum ditetapkan secara rinci.

---

## 15. Product Priorities

Urutan pengerjaan:

### P0 — Critical

1. Order lifecycle state machine.
2. Foto toko.
3. Delivery proof + completion.
4. Digital note.
5. WhatsApp stabilization.
6. Security WhatsApp.
7. WhatsApp number source-of-truth.
8. Customer permissions.
9. MySQL environment alignment.
10. Automated tests.
11. Mobile Sales QA.

### P1 — Important

1. Sales report.
2. Stock report.
3. Print report.
4. Search/filter enhancement.
5. UX/accessibility review.
6. Black Box testing.
7. SUS.

### P2 — Finalization

1. Final product audit.
2. PRD v1.0 sign-off.
3. Release readiness.

---

## 16. Working Rule for Development

`prd.md` adalah source of truth untuk development.

AI agent/developer wajib:

1. Membaca `prd.md` sebelum mengubah code.
2. Membandingkan requirement dengan implementasi existing.
3. Tidak membuat business rule baru tanpa dasar requirement.
4. Menandai requirement ambigu sebagai Open Question.
5. Mengerjakan issue sesuai scope.
6. Tidak mengubah fitur di luar issue tanpa alasan yang jelas.
7. Menjalankan test setelah perubahan.
8. Tidak commit/push sebelum hasil perubahan direview.

---

## 17. Source Reference

PRD ini disusun berdasarkan:

1. Seminar Proposal:
   **“Digitalisasi Workflow dengan Fitur Integrasi Input Sales dan Admin pada PT. Halus Ciptanadi”**
2. Requirement analysis dan functional requirements dalam proposal.
3. Event list dan DFD sistem yang diusulkan.
4. Skenario Black Box testing dalam proposal.
5. Audit terhadap implementasi repository:
   `lan-zip/simple-sales`
6. Keputusan scope produk yang telah disepakati selama proses audit.

---

## 18. PRD Status

**Current status:** `Working Baseline — v0.1`

Dokumen belum dianggap final apabila masih terdapat Open Questions atau acceptance criteria yang belum teruji.

PRD dapat dinaikkan menjadi **v1.0** setelah:

- Open Questions diselesaikan.
- P0 selesai.
- P1 yang diperlukan selesai.
- Black Box lulus.
- SUS ≥70.
- Final audit dilakukan.
