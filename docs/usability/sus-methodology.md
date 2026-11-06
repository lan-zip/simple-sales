# Metodologi System Usability Scale (SUS) — Simple Sales

## 1. Konteks & Tujuan Evaluasi

Dokumen ini mendefinisikan metodologi pengujian **System Usability Scale (SUS)** dan *usability acceptance* untuk sistem informasi **Simple Sales**. Evaluasi ini merupakan tahapan kendali mutu usability (Quality Gate) sebelum pelaksanaan audit rilis final (Issue #19) dan promosi status PRD menuju versi 1.0.

Tujuan utama pengujian:
1. Mengukur tingkat kebergunaan (*usability*), efisiensi, dan kepuasan subjektif pengguna terhadap sistem berdasarkan interaksi nyata.
2. Memverifikasi pemenuhan kriteria penerimaan non-fungsional pada PRD: **NFR-05 (Usability: SUS $\ge$ 70)**.
3. Mengidentifikasi hambatan usability nyata (*friction points*, kebingungan antarmuka, keraguan operasional) lintas tiga peran pengguna utama: **Admin**, **Sales**, dan **Direktur**.

---

## 2. Pemisahan Fakta Sumber vs Rekomendasi Metodologis

Untuk menjaga integritas ilmiah dan transparansi audit, dokumentasi ini secara eksplisit membedakan antara fakta sumber dan rekomendasi metodologis:

| Kategori | Butir / Ketetapan | Sumber / Rujukan | Status |
| :--- | :--- | :--- | :--- |
| **Fakta Sumber** | Judul penelitian: *"Digitalisasi Workflow dengan Fitur Integrasi Input Sales dan Admin pada PT. Halus Ciptanadi"*. | `prd.md` Bagian 17 (Source Reference) | Otoritatif |
| **Fakta Sumber** | Tiga peran pengguna utama: Admin, Sales, dan Direktur. | `prd.md` Bagian 5 (User Roles & Personas) | Otoritatif |
| **Fakta Sumber** | Target minimum usability: **SUS $\ge$ 70**. | `prd.md` NFR-05, Seksi 12, Seksi 13 | Otoritatif |
| **Fakta Sumber** | Alur operasional Sales di lapangan harus dapat digunakan secara nyata pada perangkat mobile. | `prd.md` NFR-05, Seksi 13 (DoD Item 11) | Otoritatif |
| **Rekomendasi Metodologis** | Penggunaan instrumen 10-item standar SUS dari John Brooke (1996). | Brooke (1996) | Rekomendasi Standar Industri |
| **Rekomendasi Metodologis** | Adaptasi kuesioner Bahasa Indonesia baku menggunakan terjemahan tervalidasi Sharfina & Santoso (2016). | Sharfina & Santoso (2016) *(Dokumen proposal mentah tidak tersedia dalam repo)* | Rekomendasi Akademik |
| **Rekomendasi Metodologis** | Penentuan ukuran sampel $N=7$ (Opsi A: 1 Direktur, 2 Admin, 4 Sales). | Keputusan User & Prinsip Nielsen (1993, 2000) | Disetujui Pengguna |
| **Rekomendasi Metodologis** | Klasifikasi keparahan temuan 4 tingkat (*Critical*, *Major*, *Minor*, *Cosmetic*). | Praktik Evaluasi Usability Standar | Rekomendasi Pengujian |

> [!NOTE]
> Berkas proposal penelitian mentah dalam format `.docx`/`.pdf` tidak tersimpan langsung di dalam repositori git ini. Oleh karena itu, rumusan kuesioner Bahasa Indonesia mengadopsi instrumen baku terjemahan Sharfina & Santoso (2016) yang lazim digunakan pada riset sistem informasi di Indonesia, bukan mengklaim kutipan kata-demi-kata dari berkas fisik proposal.

---

## 3. Kohort & Sasaran Partisipan (Opsi A — $N=7$)

Berdasarkan persetujuan pengguna, target partisipan pengujian ditetapkan menggunakan **Opsi A ($N=7$)**:

- **1 orang Direktur**: Mengevaluasi ringkasan metrik eksekutif, filter laporan penjualan, laporan stok, dan keterbacaan cetak dokumen resmi (*browser print*).
- **2 orang Admin**: Mengevaluasi manajemen master data (produk, pelanggan, sales), alur validasi order masuk, pembaruan status pengiriman, dan pencetakan nota transaksi.
- **4 orang Sales**: Mengevaluasi pencatatan pelanggan baru di lapangan, pembuatan order, pengunggahan foto fisik toko, pengunggahan foto bukti serah terima barang, dan pemantauan status pesanan melalui smartphone.

> [!IMPORTANT]
> **Kebijakan Nol Fabrikasi (*Zero Fabrication*)**:
> Angka $N=7$ adalah **target kuota pengujian**. Skor, pilihan jawaban Likert, waktu pengerjaan, catatan observasi, dan temuan kualitatif sama sekali tidak boleh disimulasikan atau dikarang sebelum sesi pengujian nyata bersama pengguna dilakukan.

---

## 4. Modalitas Perangkat Pengujian

1. **Peran Sales (Prioritas Utama — Fisik Smartphone)**:
   - Pengujian wajib diprioritaskan menggunakan **smartphone fisik** dengan rentang lebar layar **360px – 412px** (Android / iOS) yang terhubung ke jaringan server lokal (`http://<ip-lokal>:8000`).
   - *Fallback*: Jika koneksi jaringan lokal ke smartphone fisik mengalami kendala teknis tak terduga, penguji diperbolehkan menggunakan emulasi perangkat responsif Chrome DevTools (profil *iPhone 12/14* atau *Samsung Galaxy S20*).
2. **Peran Admin**:
   - Komputer desktop atau laptop dengan resolusi layar standar kantor ($\ge 1366 \times 768$ px).
3. **Peran Direktur**:
   - Komputer desktop, laptop, atau tablet dengan resolusi layar $\ge 1280 \times 800$ px.

---

## 5. Model Perhitungan Skor SUS

System Usability Scale terdiri dari 10 pernyataan dengan skala Likert 1 sampai 5:
- **1** = Sangat Tidak Setuju
- **2** = Tidak Setuju
- **3** = Ragu-ragu / Netral
- **4** = Setuju
- **5** = Sangat Setuju

### Rumus Kontribusi Skor per Butir

Untuk setiap responden $j$:
- **Butir Ganjil** ($Q_1, Q_3, Q_5, Q_7, Q_9$ — Pernyataan Positif):
  $$\text{Kontribusi } X_{i,j} = R_{i,j} - 1 \quad (\text{rentang: } 0 \le X_{i,j} \le 4)$$
- **Butir Genap** ($Q_2, Q_4, Q_6, Q_8, Q_{10}$ — Pernyataan Negatif):
  $$\text{Kontribusi } Y_{i,j} = 5 - R_{i,j} \quad (\text{rentang: } 0 \le Y_{i,j} \le 4)$$

### Skor Individual Partisipan ($S_j$)
$$S_j = \left( \sum_{i \in \{1,3,5,7,9\}} X_{i,j} + \sum_{i \in \{2,4,6,8,10\}} Y_{i,j} \right) \times 2.5$$
Rentang skor individual: $0 \le S_j \le 100$.

### Skor Rata-rata Studi ($\bar{S}$)
$$\bar{S} = \frac{1}{N} \sum_{j=1}^{N} S_j$$

---

## 6. Standar Interpretasi & Kriteria Penerimaan

Berdasarkan literatur evaluasi SUS (Bangor, Kortum, & Miller, 2008; Sauro, 2011):

| Rentang Skor SUS | Tingkat Penerimaan (*Acceptability*) | Kategori Penilaian (*Grade*) | Predikat (*Adjective Rating*) |
| :---: | :---: | :---: | :---: |
| **$\ge$ 85.0** | Dapat Diterima (*Acceptable*) | Grade A | *Excellent* (Sangat Baik) |
| **70.0 – 84.9** | **Dapat Diterima (*Acceptable*)** | **Grade B / C** | **Good (Baik) — [TARGET PRD]** |
| **50.0 – 69.9** | Marjinal (*Marginal*) | Grade D | *OK / Fair* (Cukup / Kurang) |
| **< 50.0** | Tidak Dapat Diterima (*Not Acceptable*) | Grade F | *Poor* (Buruk) |

### Keputusan Penerimaan (*Acceptance Gate Decision*)
1. **LULUS (PASS)**:
   - Skor rata-rata $\bar{S} \ge 70.0$, DAN
   - Tidak ada temuan usability berstatus **Critical** yang belum tertangani.
2. **BELUM LULUS (FAIL / REMEDIATION REQUIRED)**:
   - Skor rata-rata $\bar{S} < 70.0$, ATAU
   - Ditemukan minimal 1 masalah usability berstatus **Critical** yang menghalangi pengguna menyelesaikan alur tugas operasional utama.
