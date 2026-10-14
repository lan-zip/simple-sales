# DESIGN.md — PT. Halus Ciptanadi · Pencatatan Penjualan

> Dokumen acuan desain UI untuk aplikasi **Pencatatan Penjualan**.
> Sumber utama: mockup **Dashboard Admin – Halaman Operasional** (frame ±1440 × 1024 px).
> Dokumen ini bersifat **hidup**: fitur baru akan ditambahkan nanti, jadi semua aturan di sini ditulis supaya bisa diperluas tanpa merusak konsistensi (lihat [Bagian 10](#10-panduan-menambah-fitur-baru)).

> **Catatan:** nilai warna dan ukuran di bawah diestimasi dari mockup. Jika ada file Figma/brand guideline resmi, nilai di sini sebaiknya disesuaikan dengan sumber tersebut.

---

## 1. Ringkasan Produk

| Item | Keterangan |
|---|---|
| Nama perusahaan | PT. Halus Ciptanadi |
| Nama aplikasi | Pencatatan Penjualan |
| Platform | Web app (desktop-first) |
| Bahasa UI | Bahasa Indonesia |
| Peran yang terlihat | Admin (user: "Lanicaaa") |
| Karakter visual | Tegas, profesional, bersih; dominan merah marun dengan area konten putih |

### Prinsip Desain
1. **Jelas dulu, hiasan belakangan.** Angka dan data adalah fokus; dekorasi seminimal mungkin.
2. **Satu warna brand, banyak warna fungsi.** Merah marun untuk identitas/navigasi, warna lain (hitam, biru, hijau, oranye) khusus untuk menandai makna data.
3. **Konsisten & berulang.** Komponen yang sama (card, tabel, badge) dipakai ulang di semua halaman.
4. **Scan cepat.** Judul besar, label kecil, angka sangat besar.
5. **Siap berkembang.** Navigasi, kartu ringkasan, dan tabel dibuat modular agar mudah ditambah.

---

## 2. Design Tokens

### 2.1 Warna

#### Brand
| Token | Hex (estimasi) | Penggunaan |
|---|---|---|
| `--color-brand-900` | `#6F0D0D` | Sidebar, top bar (warna utama) |
| `--color-brand-700` | `#8A1414` | Hover item sidebar (turunan) |
| `--color-brand-divider` | `rgba(255,255,255,0.25)` | Garis pemisah di sidebar |

#### Netral
| Token | Hex (estimasi) | Penggunaan |
|---|---|---|
| `--color-bg` | `#FFFFFF` | Latar area konten |
| `--color-surface` | `#FFFFFF` | Card, panel tabel |
| `--color-surface-muted` | `#F3F4F6` | Input pencarian, header tabel |
| `--color-border` | `#E5E7EB` | Border card & panel |
| `--color-text-primary` | `#0A0A0A` | Judul, angka, teks utama |
| `--color-text-secondary` | `#374151` | Subjudul, label |
| `--color-text-placeholder` | `#9CA3AF` | Placeholder "Cari di sini…" |
| `--color-skeleton` | `#E5E7EB` | Placeholder loading di tabel |
| `--color-on-brand` | `#FFFFFF` | Teks & ikon di atas merah marun |

#### Semantik (aksen kartu statistik)
| Makna | Garis bawah card | Latar ikon | Ikon |
|---|---|---|---|
| Total Sales | `#000000` | `#E5E7EB` | `#374151` |
| Total Pelanggan | `#0B3A9E` | `#DBEAFE` | `#0B2A6B` |
| Total Pendapatan | `#16A34A` | `#DCFCE7` | `#15803D` |
| Transaksi Pending | `#C2500A` | `#FFEDD5` | `#C2500A` |

#### Interaksi
| Token | Hex | Penggunaan |
|---|---|---|
| `--color-link` | `#0B3A9E` | Tautan "Lihat Semua →" |
| `--color-nav-active-bg` | `#FFFFFF` | Latar menu sidebar aktif |
| `--color-nav-active-text` | `#0A0A0A` | Teks menu aktif |

> **Aturan warna:** Jangan gunakan merah marun untuk status error/bahaya karena sudah menjadi warna brand. Status error memakai merah terang terpisah (mis. `#DC2626`) dan selalu disertai ikon + teks.

### 2.2 Tipografi

Font: **Inter** (sans-serif geometris/neo-grotesque), fallback `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif`.

| Peran | Ukuran | Weight | Contoh di UI |
|---|---|---|---|
| Page title (H1) | 32px | 700 | "Halaman Operasional" |
| Section title (H2) | 20px | 700 | "Aktivitas Terbaru" |
| Panel title (H3) | 20px | 500 | "Order Sales Terbaru" |
| Subtitle / body | 16px | 400 | "Rekap penjualan dan transaksi pending hari ini." |
| Stat number | 36–40px | 700 | "000" |
| Stat label | 14px | 500 | "Total Sales" |
| Nav item | 14px | 500 | "Data Produk" |
| Brand name | 20px | 600 | "PT. Halus Ciptanadi" |
| Brand tagline | 14px | 400 | "Pencatatan Penjualan" |
| Link kecil | 12px | 500–600 | "Lihat Semua" |
| User name | 13px | 600 | "Lanicaaa" |
| User role | 9–10px | 400 | "Admin" |

### 2.3 Spacing
Skala dasar **4px**. Nilai yang sering dipakai: `4, 8, 12, 16, 24, 32, 40, 48`.

| Konteks | Nilai |
|---|---|
| Padding horizontal area konten | ±40px |
| Jarak antar kartu statistik | ±31px (gunakan 32px) |
| Jarak judul halaman → kartu | ±32px |
| Jarak kartu → judul section | ±32px |
| Padding dalam card statistik | 20px |
| Padding horizontal panel tabel | 26px (gunakan 24px) |
| Tinggi baris tabel | ±65px |

### 2.4 Radius
| Token | Nilai | Dipakai pada |
|---|---|---|
| `--radius-sm` | 6px | Kotak ikon di card |
| `--radius-md` | 8px | Card, panel, input pencarian |
| `--radius-pill` | 999px | Badge status, avatar |

### 2.5 Shadow & Border
| Elemen | Gaya |
|---|---|
| Card statistik | Border 1px `--color-border`, shadow sangat halus, **garis bawah tebal 4–5px** berwarna aksen |
| Panel tabel | Border 1px `--color-border`, shadow sangat halus (`0 1px 2px rgba(0,0,0,.06)`) |
| Input pencarian | Tanpa border mencolok, latar `--color-surface-muted` |

### 2.6 Snippet CSS Variables

```css
:root {
  /* Brand */
  --color-brand-900: #6F0D0D;
  --color-brand-700: #8A1414;
  --color-on-brand: #FFFFFF;
  --color-brand-divider: rgba(255, 255, 255, 0.25);

  /* Netral */
  --color-bg: #FFFFFF;
  --color-surface: #FFFFFF;
  --color-surface-muted: #F3F4F6;
  --color-border: #E5E7EB;
  --color-text-primary: #0A0A0A;
  --color-text-secondary: #374151;
  --color-text-placeholder: #9CA3AF;
  --color-skeleton: #E5E7EB;

  /* Semantik */
  --accent-neutral: #000000;
  --accent-info: #0B3A9E;
  --accent-success: #16A34A;
  --accent-warning: #C2500A;
  --accent-danger: #DC2626;

  --tint-neutral: #E5E7EB;
  --tint-info: #DBEAFE;
  --tint-success: #DCFCE7;
  --tint-warning: #FFEDD5;
  --tint-danger: #FEE2E2;

  /* Interaksi */
  --color-link: #0B3A9E;

  /* Tipografi */
  --font-sans: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;

  /* Radius */
  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-pill: 999px;

  /* Layout */
  --sidebar-width: 346px;
  --topbar-height: 96px;
  --content-padding-x: 40px;
}
```

---

## 3. Layout Global

```
┌───────────────┬──────────────────────────────────────────────┐
│               │  Top Bar (96px)  [ Search ]     🔔 | Avatar  │
│   Sidebar     ├──────────────────────────────────────────────┤
│   (346px)     │  Page Title + Subtitle                       │
│               │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐         │
│   Logo        │  │ Stat │ │ Stat │ │ Stat │ │ Stat │         │
│   ───────     │  └──────┘ └──────┘ └──────┘ └──────┘         │
│   Menu        │  Section Title                               │
│               │  ┌────────────────────────────────────────┐  │
│   ───────     │  │ Panel Tabel                            │  │
│   Keluar      │  └────────────────────────────────────────┘  │
└───────────────┴──────────────────────────────────────────────┘
```

| Area | Aturan |
|---|---|
| Sidebar | Lebar tetap 346px, tinggi penuh viewport, latar `--color-brand-900` |
| Top bar | Tinggi 96px, latar `--color-brand-900`, menyatu dengan sidebar (satu blok merah berbentuk "L") |
| Konten | Latar putih, padding horizontal 40px, scroll vertikal independen |
| Grid statistik | 4 kolom sama lebar, gap 32px |
| Grid tabel | Lebar penuh area konten |

> Top bar dan sidebar memakai warna yang sama sehingga terbaca sebagai satu frame navigasi. Area konten putih "masuk" di dalam frame tersebut.

---

## 4. Komponen

### 4.1 Sidebar

**Struktur (atas → bawah)**
1. **Brand block** — logo (siluet putih) + nama "PT. Halus Ciptanadi" + tagline "Pencatatan Penjualan". Dipisah dari menu oleh garis tipis.
2. **Menu navigasi** — daftar vertikal, tinggi tiap item ±52px.
3. **Footer** — garis pemisah tipis, lalu tombol **Keluar** (ikon logout + teks).

**Item menu**
| State | Latar | Teks/ikon |
|---|---|---|
| Default | transparan | putih |
| Hover | `--color-brand-700` | putih |
| Aktif | putih penuh lebar sidebar | hitam |
| Focus | outline 2px putih, offset −2px | — |

- Ikon: outline/line icon 24px, stroke ±1.5px, jarak ikon ke teks ±16px.
- Teks: 14px, weight 500.
- Padding kiri ±44px (ikon sejajar vertikal di semua item).
- Item aktif **tidak** memakai radius (full-bleed horizontal).

**Menu saat ini**
| Label | Ikon (gambaran) | Fungsi |
|---|---|---|
| Halaman Utama | grid/dashboard | Dashboard operasional (aktif di mockup) |
| Data Sales | grafik tren naik | Kelola data tenaga penjualan |
| Data Produk | kotak dengan panah turun | Kelola katalog produk |
| Transaksi Penjualan | dua orang | Catat & lihat transaksi |
| Data Pelanggan | dokumen | Kelola data pelanggan |
| Keluar | pintu + panah | Logout (di footer sidebar) |

> Pemetaan ikon di atas mengikuti mockup; ikon bisa diganti selama gayanya tetap line icon yang konsisten.

### 4.2 Top Bar

| Elemen | Spesifikasi |
|---|---|
| Search | Lebar ±420px, tinggi ±43px, radius 8px, latar `--color-surface-muted`, ikon kaca pembesar di kiri, placeholder "Cari di sini…" |
| Notifikasi | Ikon lonceng putih, area klik min. 40×40px; siapkan badge untuk notifikasi belum dibaca |
| Divider | Garis vertikal tipis putih |
| Profil | Avatar bulat putih (ikon user), nama (13px/600) di atas, peran "Admin" (10px) di bawah |

Posisi: search di kiri (sejajar mulai area konten), notifikasi + profil rata kanan.

### 4.3 Page Header

- **H1**: "Halaman Operasional" (32px / 700).
- **Subtitle**: satu baris deskripsi singkat (16px / 400, `--color-text-secondary`).
- Setiap halaman wajib punya H1 + subtitle satu kalimat.
- Jika halaman punya aksi utama (mis. "Tambah Produk"), tempatkan tombol di **kanan sejajar H1**.

### 4.4 Stat Card (Kartu Ringkasan)

**Anatomi (atas → bawah)**
1. Kotak ikon 40×40px, radius 6px, latar warna tint.
2. Label (14px / 500).
3. Angka besar (36–40px / 700).
4. Garis aksen bawah 4–5px, mengikuti lengkung bawah card.

**Spesifikasi**
| Properti | Nilai |
|---|---|
| Ukuran | ±229 × 188px (flex, 4 kolom) |
| Latar | putih |
| Border | 1px `--color-border` |
| Radius | 8px |
| Aksen bawah | warna sesuai makna (lihat 2.1) |

**Instance saat ini**
| Label | Aksen | Ikon | Catatan data |
|---|---|---|---|
| Total Sales | hitam | grup orang | Jumlah sales (tenaga penjualan) |
| Total Pelanggan | biru | satu orang | Jumlah pelanggan |
| Total Pendapatan | hijau | uang tunai | Nominal rupiah (format `Rp 0`) |
| Transaksi Pending | oranye | clipboard + jam | Jumlah transaksi menunggu |

**Aturan angka**
- Pendapatan memakai format rupiah dengan pemisah titik: `Rp 12.500.000`.
- Angka panjang boleh disingkat (`Rp 1,2 jt`) dengan tooltip menampilkan nilai penuh.
- Saat loading: tampilkan skeleton pada area angka.

### 4.5 Panel Tabel ("Order Sales Terbaru")

**Container**
- Latar putih, border 1px, radius 8px, shadow halus.

**Header panel**
- Judul kiri (20px / 500).
- Tautan kanan "Lihat Semua →" (12px, `--color-link`, ikon panah 14px).
- Dipisah dari tabel oleh garis tipis.

**Header tabel**
- Tinggi ±52px, latar `--color-surface-muted`, teks kolom kecil (12–13px, weight 600, `--color-text-secondary`).

**Baris**
- Tinggi ±65px, tanpa garis antar baris pada mockup (pemisah dengan whitespace). Untuk tabel padat, boleh tambah garis 1px `--color-border`.
- Padding horizontal 24px, kolom selaras kiri kecuali kolom terakhir (rata kanan).

**Kolom (5 kolom pada mockup, isi masih placeholder)**
| # | Posisi | Isi yang disarankan | Tipe |
|---|---|---|---|
| 1 | Kiri (lebar) | ID / No. Order | teks |
| 2 | Kiri | Nama Pelanggan | teks |
| 3 | Tengah | Nama Sales / Tanggal | teks |
| 4 | Tengah-kanan | **Status** | badge pill |
| 5 | Kanan | Total / Aksi | angka / ikon |

> Kolom pada tabel di atas adalah **usulan** karena mockup hanya menampilkan skeleton. Finalisasi setelah struktur data ditetapkan.

**State Loading (skeleton)** — sudah dirancang di mockup:
- Bar abu-abu `--color-skeleton`, tinggi ±12px, radius pill, lebar bervariasi.
- Kolom status: pill skeleton 74×31px dengan bulatan kecil di kiri.

### 4.6 Badge Status

Pill, tinggi ±28–31px, padding horizontal 12px, titik/ikon kecil di kiri, teks 12px / 500.

| Status | Latar | Teks/titik |
|---|---|---|
| Selesai / Lunas | `--tint-success` | `--accent-success` |
| Pending | `--tint-warning` | `--accent-warning` |
| Diproses | `--tint-info` | `--accent-info` |
| Dibatalkan / Gagal | `--tint-danger` | `--accent-danger` |

> Warna badge **harus konsisten** dengan warna aksen di Stat Card (mis. pending = oranye).

---

## 5. Ikonografi

- Gaya: **line icon**, stroke konsisten (±1.5px), sudut membulat ringan.
- Ukuran: 24px (navigasi), 20px (top bar), 20–24px (kotak ikon card), 14–16px (inline di tautan).
- Warna: putih di atas marun; warna aksen di atas tint; abu gelap di atas putih.
- Disarankan satu library saja (mis. Lucide / Heroicons Outline / Tabler) agar konsisten.

---

## 6. Halaman yang Sudah Terdefinisi

### 6.1 Halaman Utama — "Halaman Operasional"
| Zona | Isi |
|---|---|
| Header | Judul + "Rekap penjualan dan transaksi pending hari ini." |
| Ringkasan | 4 Stat Card: Total Sales, Total Pelanggan, Total Pendapatan, Transaksi Pending |
| Aktivitas Terbaru | Panel tabel "Order Sales Terbaru" (5 baris) + tautan "Lihat Semua" |

### 6.2 Halaman lain di navigasi (belum ada mockup)
Data Sales · Data Produk · Transaksi Penjualan · Data Pelanggan.
Gunakan **template halaman daftar** pada Bagian 7 sebagai titik awal.

---

## 7. Template Halaman (untuk fitur mendatang)

### 7.1 Halaman Daftar (List/Table)
```
[H1 + subtitle]                         [Tombol utama: + Tambah ...]
[Search/filter bar]                     [Filter status] [Ekspor]
┌ Panel Tabel ─────────────────────────────────────────────────┐
│ Header kolom                                                 │
│ Baris data ...                                    [aksi ⋮]   │
│ Pagination                                                   │
└──────────────────────────────────────────────────────────────┘
```

### 7.2 Halaman Form (Tambah/Ubah)
- Panel putih dengan border & radius 8px, lebar maks ±720px.
- Label di atas input, input tinggi 44px, radius 8px, border 1px `--color-border`, fokus: border `--color-brand-900` + ring halus.
- Tombol di kanan bawah: **Batal** (sekunder) dan **Simpan** (primer).

### 7.3 Halaman Detail
- H1 + badge status di samping judul.
- Konten dalam panel/kartu; aksi di kanan atas.

---

## 8. Komponen Pendukung (disiapkan untuk pengembangan)

### Tombol
| Varian | Latar | Teks | Border |
|---|---|---|---|
| Primer | `--color-brand-900` | putih | — |
| Sekunder | putih | `--color-brand-900` | 1px `--color-brand-900` |
| Tersier / Ghost | transparan | `--color-text-primary` | — |
| Bahaya | `--accent-danger` | putih | — |

Tinggi 40px (default), 32px (kecil), radius 8px, teks 14px / 600. Disabled: opasitas 50%, kursor `not-allowed`.

### Input
Tinggi 44px, radius 8px, latar putih, border 1px `--color-border`, placeholder `--color-text-placeholder`. Error: border `--accent-danger` + pesan 12px di bawah.

### Modal / Dialog
Lebar 480–560px, radius 12px, overlay `rgba(0,0,0,.5)`, judul 20px/600, aksi di kanan bawah.

### Toast / Notifikasi
Pojok kanan atas, radius 8px, strip aksen kiri 4px sesuai tipe (sukses/info/peringatan/error).

### Empty State
Ikon besar abu-abu, judul singkat, satu kalimat penjelasan, satu tombol aksi.
Contoh: "Belum ada order hari ini" + "Catat Transaksi".

### Pagination
Angka halaman bulat/persegi 36px, halaman aktif memakai `--color-brand-900` + teks putih.

---

## 9. Perilaku & Aksesibilitas

### State umum
| State | Perilaku |
|---|---|
| Loading | Skeleton abu-abu (seperti pada tabel mockup) |
| Kosong | Empty state (lihat Bagian 8) |
| Error | Pesan jelas + tombol "Coba lagi" |
| Hover | Perubahan latar halus (≤150ms) |
| Focus | Outline 2px terlihat jelas (wajib untuk keyboard) |

### Aksesibilitas
- Kontras teks putih di atas `#6F0D0D` sangat tinggi (≥ 10:1) — aman untuk teks kecil.
- Teks abu placeholder (`#9CA3AF`) hanya untuk placeholder, bukan informasi penting.
- Status tidak boleh hanya mengandalkan warna: sertakan teks/ikon.
- Area klik minimal 40×40px.
- Semua elemen interaktif bisa diakses keyboard; gunakan `aria-current="page"` pada menu aktif.
- Ikon tanpa teks wajib punya `aria-label` (mis. lonceng: "Notifikasi").

### Responsif (usulan, mockup baru versi desktop)
| Breakpoint | Perilaku |
|---|---|
| ≥ 1280px | Layout seperti mockup |
| 1024–1279px | Sidebar menyempit (±260px), kartu statistik tetap 4 kolom |
| 768–1023px | Sidebar menjadi ikon saja / drawer, kartu 2 × 2 |
| < 768px | Sidebar jadi drawer (hamburger), kartu 1 kolom, tabel scroll horizontal atau berubah jadi list kartu |

### Format data (lokal Indonesia)
- Mata uang: `Rp 1.250.000`
- Tanggal: `11 Okt 2026` (panjang: `11 Oktober 2026`)
- Jam: 24 jam, `14.30` atau `14:30`
- Bahasa: Indonesia formal-netral, kalimat singkat, kapitalisasi **Title Case** untuk label menu & judul kartu.

---

## 10. Panduan Menambah Fitur Baru

Fitur akan bertambah seiring waktu. Ikuti aturan berikut agar UI tetap konsisten:

1. **Menu baru** → tambah item di sidebar dengan pola ikon line + teks 14px. Urutkan berdasarkan alur kerja (data master → transaksi → laporan). Jika menu lebih dari ±8 item, kelompokkan dengan label grup kecil atau submenu collapsible.
2. **Kartu ringkasan baru** → gunakan Stat Card yang sama. Pilih aksen berdasarkan **makna**, bukan selera:
   - Netral/hitam → jumlah umum
   - Biru → informasi/pelanggan/proses
   - Hijau → uang/sukses/lunas
   - Oranye → menunggu/perlu tindakan
   - Merah (`--accent-danger`) → gagal/bermasalah/terlambat
3. **Tabel baru** → pakai Panel Tabel (4.5): header muted, baris ±65px (atau 48px untuk mode padat), status selalu berupa badge.
4. **Halaman baru** → mulai dari template pada Bagian 7; wajib punya H1 + subtitle.
5. **Peran pengguna baru** (mis. Sales, Manajer) → pertahankan layout, tetapi sembunyikan menu yang tidak relevan; tampilkan nama peran di profil (top bar).
6. **Fitur kandidat** (belum ada di mockup, hanya untuk antisipasi):
   - Laporan & ekspor (PDF/Excel)
   - Filter periode (hari ini / minggu / bulan) pada dashboard
   - Grafik penjualan (garis/batang) — gunakan palet semantik dan marun sebagai warna utama seri
   - Manajemen stok produk
   - Notifikasi (panel dropdown dari ikon lonceng)
   - Pengaturan akun & profil
   - Log aktivitas
7. **Jangan** menambah warna brand baru, font baru, atau radius baru tanpa memperbarui dokumen ini.

---

## 11. Struktur Komponen (saran untuk implementasi)

```
components/
├─ layout/
│  ├─ AppShell          # sidebar + topbar + content
│  ├─ Sidebar
│  ├─ SidebarItem
│  └─ Topbar
├─ ui/
│  ├─ Button
│  ├─ Input
│  ├─ SearchInput
│  ├─ Badge
│  ├─ Card
│  ├─ StatCard
│  ├─ DataTable
│  ├─ Skeleton
│  ├─ Modal
│  ├─ Toast
│  └─ EmptyState
└─ pages/
   └─ dashboard/ ...
```

---

## 12. Checklist Review UI

- [ ] Memakai token warna/tipografi/spacing dari dokumen ini
- [ ] Judul halaman + subtitle tersedia
- [ ] Status memakai badge dengan warna semantik yang benar
- [ ] Ada state loading, kosong, dan error
- [ ] Format rupiah & tanggal sesuai standar Indonesia
- [ ] Elemen interaktif punya focus state & `aria-label`
- [ ] Ikon sesuai gaya line icon
- [ ] Diuji di breakpoint desktop & tablet

---

## 13. Asumsi & Pertanyaan Terbuka

- "Sales" diasumsikan = **tenaga penjualan** (karena ada menu *Data Sales*), bukan jumlah penjualan. Mohon dikonfirmasi.
- Isi kolom tabel "Order Sales Terbaru" belum ditentukan (mockup masih skeleton).
- Font, kode warna, dan logo diestimasi dari gambar; perlu dicocokkan dengan aset resmi.
- Belum ada mockup untuk mode gelap, mobile, dan halaman selain dashboard.
- Peran selain **Admin** belum didefinisikan.

---

## 14. Riwayat Perubahan

| Versi | Tanggal | Catatan |
|---|---|---|
| 0.1 | 11 Okt 2026 | Draf awal dari mockup Dashboard Admin |
| 0.2 | 11 Okt 2026 | Catatan implementasi fondasi Phase 1, mengikuti proposal audit |

## 15. Catatan Implementasi Phase 1

- Tokens terpusat di `resources/css/app.css`; font Inter disimpan lokal di `resources/fonts/` beserta lisensi OFL. Tidak diperlukan package font atau ikon tambahan.
- Radius dialog memakai token `--radius-dialog: 12px`, sesuai ukuran modal pada Bagian 8.
- Aksen sukses tetap `#16A34A`. Teks kecil pada latar sukses memakai `--text-success: #15803D` (warna hijau yang sudah digunakan pada ikon statistik) untuk keterbacaan; tint tetap `#DCFCE7`.
- Tombol dan angka pagination memiliki area interaksi minimal 40px, serta tombol minimal 44px pada mobile.
- Drawer dipakai pada lebar di bawah 1024px, dengan dialog native, Escape, dan pengembalian fokus. Tanpa JavaScript, menu tetap tersedia dalam alur halaman.
- Topbar menyediakan slot pencarian kontekstual untuk pencarian yang telah didukung backend. Search global, lonceng interaktif, dan menu laporan ditampilkan setelah fungsinya tersedia dan disetujui. Identitas pengguna selalu berasal dari sesi autentikasi.
- Brand block sementara menggunakan nama resmi perusahaan dan tagline. Logo tidak direka ulang; aset resmi dapat dipasang ketika tersedia.
- Pemetaan badge hanya mencakup nilai yang dibaca aplikasi: `menunggu_validasi`, `tervalidasi`, `diproses`, `dikirim`, `selesai`, serta `aktif`/`nonaktif` untuk master data. Nilai lain ditampilkan netral. Komponen tidak menambah aksi transisi status.
- Pesan session success/warning/error/info dirender sekali melalui layout. Validasi field tetap menggunakan error bag backend. Loading mengikuti submit nyata dan tidak menggantikan data backend.
- Integrasi komponen ke dashboard, master data, dan transaksi dilakukan pada fase masing-masing. Definisi metrik, route, controller, model, dan database tidak berubah pada Phase 1.
