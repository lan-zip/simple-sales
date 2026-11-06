# Log Temuan Usability & Rencana Tindak Lanjut

> [!IMPORTANT]
> **Status Dokumen: KERANGKA LOG TEMUAN (MENUNGGU PELAKSANAAN SESI PENGUJIAN NYATA)**
> 
> Dokumen ini mendefinisikan kerangka klasifikasi tingkat keparahan temuan (*severity framework*), skema pencatatan temuan kualitatif, dan tabel pelacakan perbaikan (*remediation tracking*). Sesuai dengan **Kebijakan Nol Fabrikasi (*Zero Fabrication*)**, belum ada temuan yang dicatat sebelum sesi pengujian nyata dilaksanakan.

---

## 1. Kerangka Klasifikasi Keparahan Temuan (*Severity Framework*)

Setiap hambatan, kebingungan antarmuka, atau kesalahan yang ditemui partisipan diklasifikasikan ke dalam 4 tingkatan keparahan:

| Tingkat Keparahan | Definisi Operasional | Kriteria Eskalasi & Tindak Lanjut |
| :--- | :--- | :--- |
| **Critical** | Masalah yang menyebabkan partisipan **gagal menyelesaikan tugas utama** (*task failure*), mengalami kehilangan data, atau terjebak dalam kebingungan kritis tanpa jalan keluar (*no workaround*). | **Wajib diperbaiki segera dan dibuatkan tes regresi otomatis sebelum Issue #18 dapat ditutup (*blocking sign-off*).** |
| **Major** | Masalah yang menyebabkan perlambatan signifikan, percobaan berulang (*multiple retries*), atau frustrasi nyata, namun partisipan pada akhirnya berhasil menyelesaikan tugas dengan susah payah. | Dievaluasi untuk perbaikan prioritas tinggi sebelum rilis, atau didokumentasikan dalam rekomendasi perbaikan. |
| **Minor** | Hambatan kecil, keraguan sesaat, atau gesekan antarmuka yang dapat diatasi sendiri oleh partisipan secara cepat (*quick self-recovery*). | Dicatat sebagai masukan penyempurnaan pengalaman pengguna (*UX polish*). |
| **Cosmetic** | Ketidaksempurnaan visual minor (misalnya jarak spasi antarmuka, konsistensi tipografi, tata letak sekunder) yang sama sekali tidak mengganggu alur tugas. | Dicatat dalam backlog perbaikan visual pascarilis. |

---

## 2. Skema Pencatatan Temuan (*Finding Schema*)

Setiap temuan yang teridentifikasi selama sesi pengujian nyata wajib didokumentasikan dengan struktur standar berikut:

```text
ID Temuan:           USE-[ROLE]-[NOMOR_URUT]  (Contoh: USE-SLS-001)
Peran Partisipan:    [Admin / Sales / Direktur]
Kode Tugas Terkait:  [Misalnya ADM-T08 / SLS-T04 / DIR-T04]
Perilaku Teramati:   [Tindakan nyata yang dilakukan pengguna dan respons sistem di layar]
Ekspektasi Pengguna: [Apa yang diharapkan atau dicari oleh pengguna saat itu]
Dampak Usability:    [Bagaimana masalah mempengaruhi efisiensi, keberhasilan, atau kejelasan]
Tingkat Keparahan:   [Critical / Major / Minor / Cosmetic]
Bukti / Kutipan:     [Kutipan langsung ucapan pengguna, stempel waktu, atau rekaman visual]
Rekomendasi Solusi:  [Tindakan perbaikan kode atau penyesuaian UI yang disarankan]
Status Penyelesaian: [Open / In Progress / Fixed / Verified]
Referensi Tes:       [Nama file/metode PHPUnit regresi jika dilakukan perubahan kode]
```

---

## 3. Log Temuan Usability Nyata (*Live Usability Findings Log*)

Tabel log berikut akan diperbarui secara transparan segera setelah pengujian selesai:

| ID Temuan | Peran | Kode Tugas | Ringkasan Masalah Teramati | Tingkat Keparahan | Status Penyelesaian | Referensi Tiket / PR |
| :---: | :---: | :---: | :--- | :---: | :---: | :---: |
| *(Belum ada data)* | - | - | *Akan dicatat setelah pelaksanaan pengujian bersama pengguna.* | - | - | - |

---

## 4. Protokol Perbaikan Cacat Usability (*Remediation Protocol*)

Apabila dalam pengujian nyata teridentifikasi temuan dengan keparahan **Critical**:
1. Masalah diverifikasi reproduksibilitasnya pada lingkungan pengujian lokal (`http://localhost:8000`).
2. Dibuat perbaikan terarah (*targeted code fix*) pada file Blade atau komponen UI terkait tanpa mengubah arsitektur inti atau aturan bisnis yang sudah valid.
3. Ditambahkan automated feature test baru pada direktori `tests/Feature/` untuk mencegah regresi di masa depan.
4. Dilakukan pengujian ulang terhadap seluruh rangkaian tes otomatis (`php artisan test`) untuk memastikan nol kegagalan (*zero failures*).
5. Bukti perbaikan didokumentasikan secara lengkap pada tabel di atas sebelum diteruskan ke **GitHub Issue #19 (Final Release Audit)**.
