# 📋 CPH Tyre Dashboard — Status Pengerjaan Project

> **Terakhir diperbarui:** 21 Februari 2026  
> **Dibuat oleh:** Developer (auto-generated dari review codebase)  
> **Referensi:** Chat WhatsApp Pak Agus CPH & Notulensi Meeting

---

## Daftar Isi

- [Ringkasan Progress](#ringkasan-progress)
- [6 Feb 2026 — Masukan GSI](#6-feb-2026--masukan-gsi)
- [18 Feb 2026 — Request Fitur Import & Role](#18-feb-2026--request-fitur-import--role)
- [19 Feb 2026 (Kamis) — Meeting](#19-feb-2026-kamis--meeting)
- [20 Feb 2026 (Jumat) — Meeting Online](#20-feb-2026-jumat--meeting-online)
- [Tambahan dari Diskusi 20 Feb](#tambahan-dari-diskusi-20-feb)
- [Prioritas Selanjutnya](#prioritas-selanjutnya)

---

## Ringkasan Progress

| Status     | Jumlah Item | Keterangan                               |
| ---------- | :---------: | ---------------------------------------- |
| ✅ Selesai |   **15**    | Sudah diimplementasikan dan berjalan     |
| ⚠️ Partial |    **5**    | Sebagian dikerjakan, perlu penyempurnaan |
| ❌ Belum   |   **20**    | Belum dikerjakan sama sekali             |

**Estimasi keseluruhan: ~37.5% selesai**

---

## 6 Feb 2026 — Masukan GSI

> Sumber: Chat Pak Agus CPH, 6 Feb 2026 pukul 16:09 & 16:11

### ✅ Sudah Dikerjakan

|  #  | Request Client                                                                                                     | Detail Implementasi                                                                                                                                                                                                                                                      | File Terkait                                                                              |
| :-: | ------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------- |
|  1  | **Potensi perubahan HM/KM baru (bisa menimbulkan hasil minus)** — dari penggantian unit odometer/panel/electricity | Sudah ada fungsi `calculateLifetimeDiff()` yang menangani kalkulasi lifetime dengan kemungkinan odometer/HM ter-reset (nilai minus). Fungsi ini membandingkan reading saat ini vs saat install, dan jika hasilnya minus, tetap memprosesnya.                             | `app/Http/Controllers/TyrePerformance/Movement/TyreMovementController.php` (line 158-175) |
|  3  | **Kondisi pemasangan tyre dibuat lebih simple: New, Spare, Repair** — status dibedakan antara install dan replace  | Form pemasangan sudah memiliki dropdown `install_condition` dengan pilihan: **New (Baru)**, **Spare (Bekas/Cadangan)**, **Repair (Hasil Perbaikan/Vulkanisir)**. Sistem juga membedakan antara install baru dan replace (penggantian ban pada posisi yang sudah terisi). | `resources/views/tyre-performance/movement/pemasangan.blade.php` (line 131-140)           |

### ❌ Belum Dikerjakan

|  #  | Request Client                                                                                                                                    | Catatan                                                                                                                                                                                                                                                              |
| :-: | ------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|  2  | **Penyebutan nama tyre failure/damage disepakati dengan site/user** — sifatnya adjustable/per customer, difasilitasi dengan istilah masing-masing | Saat ini Failure Code masih **global** (berlaku sama untuk semua customer). Belum ada mekanisme untuk setiap customer memiliki set istilah/failure code mereka sendiri. Perlu: tambah relasi failure code ↔ customer, dan filter di dashboard sesuai customer aktif. |
|  4  | **Claim/warranty: share parameter yang diberikan oleh pabrik** — harus discuss dengan pihak principal                                             | Belum ada modul **Claim/Warranty** sama sekali. Ini memerlukan diskusi lebih lanjut dengan principal/pabrik mengenai parameter apa saja yang akan digunakan (syarat klaim, jarak tempuh minimum, dll).                                                               |

### 📝 Catatan Tambahan (Konteks Diskusi)

| Topik                                                                | Status     | Keterangan                                                                                                                                                                       |
| -------------------------------------------------------------------- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Ban or habit (rute/tonase/karakter tonase/muatan/skill driver/brand) | ⚠️ Partial | Sebagian sudah ter-capture melalui data segment, location, dan brand. Namun **data per driver** belum ada (karena jika ambil "by unit number" hasilnya sama — sudah disepakati). |
| Customer based (tonase/volume)                                       | ⚠️ Partial | Data tonase belum sepenuhnya ter-implementasi. Perlu field `curb_weight` di master vehicle (lihat request 20 Feb).                                                               |

---

## 18 Feb 2026 — Request Fitur Import & Role

> Sumber: Chat Pak Agus CPH, 18 Feb 2026 pukul 17:03 - 17:09

### ✅ Sudah Dikerjakan

|  #  | Request Client                                | Detail Implementasi                                                                                                                                                                                                                                                          | File Terkait                                                                                                           |
| :-: | --------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
|  1  | **Buka akses akun dummy**                     | Migration `phase1_upgrade_tables` sudah menambahkan kolom `name` ke tabel `users` untuk mendukung dummy users yang tidak perlu linked ke data karyawan.                                                                                                                      | `database/migrations/2026_02_19_060000_phase1_upgrade_tables.php`                                                      |
|  2  | **Export data**                               | Dashboard sudah memiliki tombol Export dengan dropdown menu: Movements Raw Data, Failure Analysis Data, Tyre Master List. Semua di-export dalam format CSV.                                                                                                                  | `app/Http/Controllers/TyrePerformance/DashboardController.php` → method `export()` (line 957-1165)                     |
|  3  | **Import data by Excel** (request GSI)        | Upload file CSV sudah berjalan. Data di-parse, disimpan ke tabel `import_batches` & `import_items`, lalu menunggu approval admin. Module yang didukung: Failure Codes, Tyre Brand, Tyre Size, Tyre Pattern, Tyre Master, Vehicle Master, Movement History, Tyre Examination. | `app/Http/Controllers/UserManagement/ImportController.php`                                                             |
|  4  | **Fitur approval untuk admin import request** | Halaman approval sudah ada lengkap dengan fitur: view detail data, approve (proses data ke tabel master), reject (dengan alasan). Setiap aksi di-log ke activity log.                                                                                                        | `app/Http/Controllers/UserManagement/ImportApprovalController.php`, `resources/views/user-management/import-approval/` |
|  5  | **Tracking aktivitas (log edit data)**        | Activity Log sudah berjalan dengan pencatatan: user, activity, action_type (login/create/update/delete/import/export), module, data before & after (JSON), IP address. Halaman log sudah ada dengan fitur search & pagination.                                               | `app/Http/Controllers/UserManagement/ActivityLogController.php`, migration `phase1_upgrade_tables`                     |
|  6  | **Download template import**                  | Setiap module import sudah memiliki template CSV yang bisa di-download sebagai panduan format upload.                                                                                                                                                                        | `DashboardController.php` → `downloadTemplate()` (line 1167-1218)                                                      |

### ❌ Belum Dikerjakan

|  #  | Request Client                                                                                                     | Catatan                                                                                                                                                                  |
| :-: | ------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
|  1  | **Level Manajerial: all data, all dashboard, log import/export, log edit data, BA, Invoicing, overdue, lead time** | Pembagian level akses **belum granular** sesuai 3-tier yang diminta. Saat ini role hanya berdasarkan menu permission, belum ada logic khusus per level untuk akses data. |
|  2  | **Supervisor level: edit data, export/import**                                                                     | Belum ada pembatasan spesifik: siapa yang hanya bisa edit, siapa yang bisa full access.                                                                                  |
|  3  | **Admin level: input data, import data request**                                                                   | Sudah ada secara fungsional (import request → approval), tapi belum ada pembatasan role yang ketat.                                                                      |
|  4  | **Lead time tracking: items delivery, BA, Invoicing, document received date**                                      | Belum ada fitur lead time sama sekali. Ini terkait erat dengan modul BA/Invoicing yang juga belum ada.                                                                   |
|  5  | **Level manajerial, BA, Invoicing, overdue**                                                                       | Modul **BA (Berita Acara)** dan **Invoicing** belum dibuat.                                                                                                              |

### Spesifikasi Role yang Diminta (Referensi)

```
┌─────────────────┬──────────────────────────────────────────────────────────────┐
│ Level           │ Akses                                                        │
├─────────────────┼──────────────────────────────────────────────────────────────┤
│ 1. Manajerial   │ All data, all dashboard, log import/export, log edit data,  │
│                 │ BA, Invoicing, overdue, lead time                            │
│                 │ Dibagi: tyre history, lead time, AR, all log history          │
├─────────────────┼──────────────────────────────────────────────────────────────┤
│ 2. Supervisor   │ Edit data, export/import                                     │
├─────────────────┼──────────────────────────────────────────────────────────────┤
│ 3. Admin        │ Input data, import data request                              │
└─────────────────┴──────────────────────────────────────────────────────────────┘
```

---

## 19 Feb 2026 (Kamis) — Meeting

> Sumber: Notulensi meeting Kamis 19 Feb 2026

### ✅ Sudah Dikerjakan

|  #  | Request                                                                              | Detail Implementasi                                                                                  |
| :-: | ------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------- |
|  1  | **Export/import data dashboard, failure dll sebagai data mentah untuk diverifikasi** | Export sudah tersedia: Movements Raw Data, Failure Analysis, Tyre Master. Import via CSV + approval. |

### ❌ Belum Dikerjakan

|  #  | Request                                              | Catatan                                                                                                                        |
| :-: | ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
|  1  | **Form untuk user upload BA (Berita Acara)**         | Belum ada modul BA. Alur yang diminta: **BA → PO → INVOICE** (dari sisi CPH), atau **PO → BA → INVOICE** (dari sisi customer). |
|  2  | **Semua user CPH bisa melakukan export/import data** | Export tersedia, tapi akses import masih perlu dikonfigurasi agar semua user CPH bisa menggunakannya (lewat permission).       |

---

## 20 Feb 2026 (Jumat) — Meeting Online

> Sumber: Notulensi meeting online Jumat 20 Feb 2026

### ✅ Sudah Dikerjakan

|  #  | Request                                                         | Detail Implementasi                                                                                                                         | File Terkait                                                               |
| :-: | --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
|  1  | **Performa Brand tambah filter keterangan size, type, pattern** | Chart "Performa Brand (Avg Lifetime KM)" sudah punya 3 filter dropdown (Size, Type, Pattern) dengan AJAX endpoint `brandPerformanceAjax()`. | `dashboard.blade.php` line 374-410, `DashboardController.php` line 385-400 |
|  2  | **CPK by Brand filter**                                         | Chart "Cost Per KM by Brand" sudah punya filter yang sama.                                                                                  | `dashboard.blade.php` line 424-460, `DashboardController.php` line 402-417 |
|  3  | **Axle Analysis scrap per posisi & filter**                     | Chart "Scrap by Position Analysis" dengan filter Size/Type/Pattern, total scrap badge, dan drill-down detail.                               | `dashboard.blade.php` line 534-584, `DashboardController.php` line 419-472 |
|  4  | **Examination PDF**                                             | Cetak langsung (stream) dan unduh file PDF sudah berjalan.                                                                                  | `TyreExaminationController.php` → `exportPdf()`                            |

### ❌ Belum Dikerjakan

|  #  | Request                                                                                                    | Catatan                                                                                             |   Estimasi Effort    |
| :-: | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- | :------------------: |
|  1  | **Dashboard "total sample" → "total entry/count"**                                                         | Masih tertulis "Total sample" di 2 tempat (line 1180 & 1321 di `dashboard.blade.php`).              |     🟢 Quick fix     |
|  2  | **Tambah 1 kolom RTD lagi (RTD 4) di form Examination**                                                    | Belum ada kolom `rtd_4`. Perlu: tambah kolom di DB, update form create, update view show & PDF.     |      🟡 Medium       |
|  3  | **Dashboard failure code yang tampil sesuai yang customer buat**                                           | Failure code masih global. Perlu relasi customer ↔ failure code.                                    |      🟡 Medium       |
|  4  | **Penamaan semua istilah/bahasa pada sistem diseragamkan**                                                 | Masih campuran bahasa Inggris dan Indonesia di seluruh sistem. Ini dijadwalkan di akhir pengerjaan. |      🟡 Medium       |
|  5  | **Form pemasangan/pelepasan: visual layout diperkecil, automasi pengisian form dicek**                     | Layout masih ukuran default. Otomasi beberapa field masih perlu di-review.                          |      🟡 Medium       |
|  6  | **Tambahan field RTD dari master tyre di form pemasangan**                                                 | Form pemasangan belum menampilkan RTD (OTD/current RTD) dari master tyre saat ban dipilih.          |     🟢 Quick fix     |
|  7  | **Master Vehicle: ubah "Tyre Layout" → "Axle Layout", "Tyre Positions" → "Wheels"**                        | Masih menggunakan istilah lama di seluruh menu dan label.                                           |     🟢 Quick fix     |
|  8  | **Master Vehicle: merk, type, konfig roda, konfig ban, curb weight (berat kendaraan) untuk hitung tonase** | Belum ada field-field ini di master vehicle. Perlu tambah kolom di DB + update form.                |       🔴 Major       |
|  9  | **Examination form dan detail: kolom tanda tangan dihapus**                                                | Masih ada signature boxes di `show.blade.php` (5 box) dan `pdf.blade.php` (5 box).                  |     🟢 Quick fix     |
| 10  | **Invoicing: 1 menu saja (karena mencakup 3 menu lama) + field status paid/unpaid**                        | Belum ada modul Invoicing sama sekali.                                                              |       🔴 Major       |
| 11  | **Dashboard: tambahan fitur speed**                                                                        | Belum jelas spesifikasinya. Perlu klarifikasi lebih lanjut.                                         | ❓ Perlu klarifikasi |
| 12  | **Informasi/Promo sebelum/setelah login**                                                                  | Belum ada. Disimpan di akhir pengerjaan (sesuai kesepakatan).                                       |      🟡 Medium       |

---

## Tambahan dari Diskusi 20 Feb

> Sumber: Catatan tambahan meeting 20 Feb 2026

### ❌ Belum Dikerjakan

|  #  | Request                                                                                                 | Catatan                                                                                                                                       |   Estimasi Effort    |
| :-: | ------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- | :------------------: |
|  1  | **Tambah "type" di table dashboard**                                                                    | Dashboard table belum ada kolom type ban.                                                                                                     |     🟢 Quick fix     |
|  2  | **Performa brand: rincian avg lifetime per size/type/pattern (list view)**                              | Filter sudah ada, tapi belum ada **list/table view** rincian avg lifetime per item.                                                           |      🟡 Medium       |
|  3  | **Tambah status Retreaded (Vulkanisir) untuk R0, RN dst di master ban + chart lingkaran**               | Belum ada status retreaded di master ban. Ada migration lama vulkanisir tapi belum ter-implementasi ke master tyre.                           |       🔴 Major       |
|  4  | **Chart baru: kerusakan (prematur vs case/gundul/worn out)**                                            | Belum ada chart breakdown berdasarkan jenis kerusakan.                                                                                        |      🟡 Medium       |
|  5  | **Pembahasan role akses akun**                                                                          | Sudah ada role/permission dasar, tapi belum granular sesuai 3-tier yang diminta.                                                              |       🔴 Major       |
|  6  | **Examination form / form inspeksi export ke Excel**                                                    | Examination baru bisa di-export ke PDF, belum bisa ke Excel.                                                                                  |      🟡 Medium       |
|  7  | **RTD Distribution & Population di Fleet Health: ubah title**                                           | Masih bertuliskan "Fleet Health (% Tread Remaining)". Perlu disesuaikan titlenya.                                                             |     🟢 Quick fix     |
|  8  | **Hitungan kondisi ban pakai % saja (good/monitor/dll dari rata-rata master ban)**                      | Fleet health sudah pakai %, tapi logikanya belum sepenuhnya dari rata-rata semua ban di master (hanya dari ban terpasang).                    |      🟡 Medium       |
|  9  | **Axle Analysis: total ban scrap per bulan + per posisi**                                               | Scrap by position sudah ada. Belum ada **breakdown per bulan** (tren bulanan scrap).                                                          |      🟡 Medium       |
| 10  | **Chart: x=scrap qty, y=tyre position, filter by size, grafik by jenis kerusakan (prematur, worn out)** | Scrap by position + filter size sudah ada. Belum ada breakdown **by jenis kerusakan**.                                                        |      🟡 Medium       |
| 11  | **Chart human error untuk deteksi data tidak valid**                                                    | Belum ada fitur validasi/deteksi anomali data sama sekali.                                                                                    |       🔴 Major       |
| 12  | **Form pelepasan: tambahkan upload gambar, hide layout, data matching install & remove tyre**           | Form pelepasan belum ada upload gambar. Belum ada view terpisah untuk lihat konfigurasi ban. Data matching antara install & remove belum ada. |       🔴 Major       |
| 13  | **Tahapan update RTD (bagaimana pengisian RTD)**                                                        | Perlu klarifikasi dengan GSI bagaimana tahapan update RTD dilakukan (di examination? di movement? manual?).                                   | ❓ Perlu klarifikasi |

---

## Prioritas Selanjutnya

### 🟢 Quick Wins (Bisa dikerjakan segera, effort kecil)

1. ~~"Total sample"~~ → **"Total Entry"** di dashboard ✅ **(SELESAI 21 Feb 2026)**
2. **Hapus tanda tangan** di Examination `show.blade.php` dan `pdf.blade.php` ✅ **(SELESAI 21 Feb 2026)**
3. **Rename**: "Tyre Layout" → "Axle Layout", "Tyre Positions" → "Wheels" di menu/label ✅ **(SELESAI oleh developer)**
4. **Ubah title** "Fleet Health" → "Kondisi Ban Terpasang (RTD %)" ✅ **(SELESAI 21 Feb 2026)**
5. **Tampilkan RTD** dari master tyre di form pemasangan ✅ **(SELESAI 21 Feb 2026)** — OTD & RTD muncul saat pilih ban di dropdown dan di info badges
6. ~~**Tambah kolom "type"** di table dashboard~~ → **Hapus kolom "Type" (Bias/Radial)** dari semua table drill-down ✅ **(SELESAI 21 Feb 2026)**

### 🟡 Medium Priority (Perlu planning, effort sedang)

1. **RTD 4** — tambah kolom di DB + update examination form
2. **Chart kerusakan** — prematur vs worn out
3. **Axle Analysis** breakdown per bulan
4. **Examination export ke Excel**
5. **Hitungan kondisi ban %** dari seluruh master ban
6. **Failure code per customer** (adjustable)
7. **Seragamkan bahasa/istilah** di seluruh sistem
8. **Informasi/Promo** (di akhir pengerjaan)

### 🔴 Major Features (Butuh design & planning matang)

1. **Modul BA → PO → Invoice** — alur lengkap berita acara
2. **Modul Invoicing** — 1 menu, status paid/unpaid
3. **Role 3-tier** — Manajerial / Supervisor / Admin dengan permission granular
4. **Status Retreaded/Vulkanisir** (R0, R1, RN) + chart
5. **Form pelepasan** — upload gambar, data matching, view konfigurasi
6. **Chart Human Error** — deteksi data tidak valid
7. **Claim/Warranty** — parameter dari pabrik
8. **Lead Time Tracking** — items delivery, BA, invoicing, document received
9. **Master Vehicle** — field baru (merk, type, konfig roda/ban, curb weight)

### ❓ Perlu Klarifikasi

1. **Fitur speed** di dashboard — apa maksudnya? Kecepatan kendaraan? Loading speed?
2. **Tahapan update RTD** — bagaimana alur pengisian RTD yang diinginkan?
3. **Curb weight / tonase** — formula perhitungan yang digunakan?

---

## Catatan Teknis

### Stack Teknologi

- **Framework:** Laravel (PHP)
- **Database:** MySQL (shared dengan CPH lama)
- **Template:** Vuexy Admin + Bootstrap 5
- **Charts:** ApexCharts
- **PDF:** DomPDF

### File-File Utama

```
app/Http/Controllers/
├── TyrePerformance/
│   ├── DashboardController.php          # Dashboard utama + export + import template
│   ├── Examination/
│   │   └── TyreExaminationController.php # Form inspeksi + PDF
│   ├── Master/
│   │   ├── KendaraanController.php       # Master vehicle
│   │   ├── TyreBrandController.php       # Master brand
│   │   ├── TyreFailureCodeController.php # Master failure code
│   │   ├── TyreMasterController.php      # Master tyre
│   │   ├── TyrePositionController.php    # Konfigurasi posisi/layout
│   │   └── ... (Size, Pattern, Location, Segment)
│   └── Movement/
│       └── TyreMovementController.php    # Pemasangan & Pelepasan
└── UserManagement/
    ├── ActivityLogController.php          # Log aktivitas
    ├── ImportApprovalController.php       # Approval import
    ├── ImportController.php              # Upload CSV
    ├── RoleController.php                # Manajemen role
    └── UserController.php                # Manajemen user
```

---

_Dokumen ini di-generate secara otomatis dari review codebase pada 21 Feb 2026._
